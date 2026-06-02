<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use App\Models\ProductUrl;
use App\Models\PriceHistory;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;
use App\Notifications\PriceDropped;

class CheckPrices extends Command
{
    protected $signature = 'prices:check';

    protected $description = 'Sprawdza aktualne ceny wszystkich śledzonych produktów';

    public function handle(): void
    {
        $this->info('Rozpoczynam sprawdzanie cen przez API...');

        // Pobranie klucza autoryzacyjnego do usługi ScraperAPI
        $apiKey = env('SCRAPER_API_KEY');

        // Przerwanie działania zadania, jeśli konfiguracja jest niekompletna
        if (!$apiKey) {
            $this->error('Brak klucza API! Dodaj SCRAPER_API_KEY do pliku .env');
            return;
        }

           /*
            * Pobranie wszystkich aktywnie śledzonych linków.
            * Pomijane są produkty oraz adresy URL oznaczone jako zakończone.
            * Dodatkowo wykonywany jest eager loading użytkownika,
            * aby ograniczyć liczbę zapytań do bazy danych.
            */
        $links = ProductUrl::whereNull('end_of_tracking_at')
            ->whereHas('product', function ($query) {
                $query->whereNull('end_of_tracking_at');
            })
            ->with('product.user')
            ->get();

        /*
         * Grupowanie identycznych adresów URL.
         * Dzięki temu jeden sklep jest scrapowany tylko raz,
         * nawet jeśli ten sam produkt obserwuje wielu użytkowników.
         */
        $groupedLinks = $links->groupBy('url');

        $this->info("Znaleziono {$links->count()} linków w bazie, co daje {$groupedLinks->count()} unikalnych zapytań do sklepu.");

        foreach ($groupedLinks as $uniqueUrl => $urlModels) {
            $this->line("Scrapuję: {$uniqueUrl} (Przypiętych użytkowników: {$urlModels->count()})");

            try {
                // Budowa adresu zapytania do zewnętrznego API
                $apiUrl = "http://api.scraperapi.com?api_key={$apiKey}&url=" . urlencode($uniqueUrl);

                // Pobranie kodu HTML strony produktu
                $response = Http::timeout(60)->get($apiUrl);

                if ($response->successful()) {
                    $crawler = new Crawler($response->body());

                    // Zmienna przechowująca odnalezioną cenę
                    $foundPrice = null;

                    /*
                     * Większość sklepów publikuje dane produktu
                     * w formacie JSON-LD zgodnym ze schema.org.
                     * Jest to najbardziej niezawodne źródło ceny.
                     */
                    $scripts = $crawler->filter('script[type="application/ld+json"]')->extract(['_text']);

                    foreach ($scripts as $script) {
                        $data = json_decode($script, true);

                        // Pominięcie niepoprawnego JSON-a
                        if (!$data) {
                            continue;
                        }

                        /*
                         * Niektóre sklepy przechowują dane produktu
                         * bezpośrednio w obiekcie, a inne w strukturze @graph.
                         */
                        $elementsToSearch = isset($data['@graph']) ? $data['@graph'] : (isset($data['@type']) ? [$data] : $data);

                        if (is_array($elementsToSearch)) {
                            foreach ($elementsToSearch as $jsonElement) {
                                // Weryfikacja czy analizowany element opisuje produkt
                                $isProduct = isset($jsonElement['@type']) && (
                                    $jsonElement['@type'] === 'Product' ||
                                    (is_array($jsonElement['@type']) && in_array('Product', $jsonElement['@type']))
                                );

                                if ($isProduct) {
                                    // Najczęściej spotykana struktura ceny
                                    if (isset($jsonElement['offers']['price'])) {
                                        $foundPrice = $jsonElement['offers']['price'];
                                        break 2;
                                    // Alternatywna struktura tablicowa
                                    } elseif (isset($jsonElement['offers'][0]['price'])) {
                                        $foundPrice = $jsonElement['offers'][0]['price'];
                                        break 2;
                                    }
                                }
                            }
                        }
                    }

                    /*
                     * Mechanizm awaryjny.
                     * Jeśli sklep nie posiada JSON-LD,
                     * wykonywane jest wyszukiwanie ceny przy użyciu regex.
                     */
                    if (!$foundPrice) {
                        if (preg_match('/"price"\s*:\s*([\d\.]+)/', $response->body(), $matches)) {
                            $foundPrice = $matches[1];
                            $this->info("Użyto koła ratunkowego (Regex) dla ceny!");
                        }
                    }

                    if ($foundPrice) {
                        $this->info("SUKCES! Znaleziona cena: {$foundPrice} PLN");

                        /*
                         * Aktualizacja wszystkich rekordów korzystających
                         * z tego samego adresu URL.
                         */
                        foreach ($urlModels as $urlModel) {
                            // Ostatnia zapisana cena produktu
                            $lastSavedPrice = PriceHistory::where('product_url_id', $urlModel->id)
                                ->latest()
                                ->first();

                            // Cena docelowa ustawiona przez użytkownika
                            $targetPrice = $urlModel->product->target_price;
                            $shouldSendNotification = false;

                            /*
                             * Logika wykrywania obniżek.
                             * Powiadomienie wysyłane jest wyłącznie,
                             * gdy cena rzeczywiście spadła.
                             */
                            if ($lastSavedPrice) {
                                if ((float)$foundPrice < (float)$lastSavedPrice->price) {
                                    if ($targetPrice !== null) {
                                        if ($foundPrice <= $targetPrice && $lastSavedPrice->price > $targetPrice) {
                                            $shouldSendNotification = true;
                                        }
                                        /*
                                         * Powiadomienie przy osiągnięciu
                                         * lub przekroczeniu ceny docelowej.
                                         */
                                        elseif ($foundPrice <= $targetPrice) {
                                            $shouldSendNotification = true;
                                        }
                                    } else {
                                        // Użytkownik nie ustawił ceny docelowej
                                        $shouldSendNotification = true;
                                    }
                                }
                            }

                            /*
                             * Zapis każdej obserwacji do historii cen.
                             * Pozwala budować wykres zmian w czasie.
                             */
                            PriceHistory::create([
                                'product_url_id' => $urlModel->id,
                                'price'          => $foundPrice
                            ]);

                            if ($lastSavedPrice && (float)$foundPrice === (float)$lastSavedPrice->price) {
                                $this->line("Cena bez zmian. Zapisano dla utrzymania ciągłości historii.");
                            } else {
                                $this->info("Nowa cena dla: {$urlModel->product->name} została zapisana do bazy.");
                            }

                            /*
                             * Wysłanie powiadomienia e-mail o wykrytej obniżce.
                             */
                            if ($shouldSendNotification) {
                                $user = $urlModel->product->user;
                                $user->notify(new PriceDropped(
                                    $urlModel->product->name,
                                    $lastSavedPrice->price,
                                    $foundPrice,
                                    $urlModel->url,
                                    $targetPrice
                                ));
                                $this->info("Wysłano e-mail ze spadkiem ceny do: {$user->email}");
                            }
                        }
                    } else {
                        $this->error("Strona się załadowała, ale nie mogłem znaleźć ceny w danych JSON-LD.");
                    }

                } else {
                    // Niepoprawna odpowiedź zwrócona przez ScraperAPI
                    $this->error("Błąd API: {$response->status()}");
                }

            } catch (Exception $exception) {
                // Obsługa nieoczekiwanych błędów aplikacji
                $this->error("Błąd systemu: " . $exception->getMessage());
            }
        }

        $this->info('Zakończono sprawdzanie.');
    }
}