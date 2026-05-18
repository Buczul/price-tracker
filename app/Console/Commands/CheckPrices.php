<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProductUrl;
use App\Models\PriceHistory;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class CheckPrices extends Command
{
    // Sygnatura komendy
    protected $signature = 'prices:check';

    protected $description = 'Sprawdza aktualne ceny wszystkich śledzonych produktów';

    public function handle()
    {
        $this->info('Rozpoczynam sprawdzanie cen przez API...');

        // Pobranie klucza z pliku .env
        $apiKey = env('SCRAPER_API_KEY');

        if (!$apiKey) {
            $this->error('Brak klucza API! Dodaj SCRAPER_API_KEY do pliku .env');
            return;
        }

        $urls = ProductUrl::all();

        foreach ($urls as $item) {
            $this->line("Scrapuję: {$item->url}");

            try {
                // Konstruowanie zapytania do ScraperAPI
                $apiUrl = "http://api.scraperapi.com?api_key={$apiKey}&url=" . urlencode($item->url);

                $response = Http::timeout(60)->get($apiUrl);

                if ($response->successful()) {
                    $crawler = new Crawler($response->body());
                    $foundPrice = null;
                    $scripts = $crawler->filter('script[type="application/ld+json"]')->extract(['_text']);

                    foreach ($scripts as $script) {
                        $data = json_decode($script, true);

                        if (!$data) continue;

                        // Rozwiązanie dla sklepów używających @graph
                        $itemsToSearch = isset($data['@graph']) ? $data['@graph'] : (isset($data['@type']) ? [$data] : $data);

                        if (is_array($itemsToSearch)) {
                            foreach ($itemsToSearch as $jsonItem) {
                                // Sprawdzenie, czy to jest Produkt
                                $isProduct = isset($jsonItem['@type']) && (
                                    $jsonItem['@type'] === 'Product' ||
                                    (is_array($jsonItem['@type']) && in_array('Product', $jsonItem['@type']))
                                );

                                if ($isProduct) {
                                    // Różne warianty zapisu ceny w JSON-LD
                                    if (isset($jsonItem['offers']['price'])) {
                                        $foundPrice = $jsonItem['offers']['price'];
                                        break 2;
                                    } elseif (isset($jsonItem['offers'][0]['price'])) {
                                        $foundPrice = $jsonItem['offers'][0]['price'];
                                        break 2;
                                    }
                                }
                            }
                        }
                    }

                    // Jeśli JSON-LD nie zawiera informacji o cenie
                    // użycie Wyrażeń Regularnych (Regex) do wyszukania ceny w html z JavaScriptu
                    if (!$foundPrice) {
                        if (preg_match('/"price"\s*:\s*([\d\.]+)/', $response->body(), $matches)) {
                            $foundPrice = $matches[1];
                            $this->info("Użyto koła ratunkowego (Regex) dla ceny!");
                        }
                    }

                    if ($foundPrice) {
                        $this->info("SUKCES! Znaleziona cena: " . $foundPrice . " PLN");

                        // Przed zapisaniem nowej ceny, sprawdzenie jaka była poprzednia w bazie
                        $lastPriceRecord = PriceHistory::where('product_url_id', $item->id)
                                            ->latest()
                                            ->first();

                        // Zapis nowej ceny
                        PriceHistory::create([
                            'product_url_id' => $item->id,
                            'price' => $foundPrice
                        ]);
                        $this->info("Cena została zapisana w historii!");

                        // Sprawdzenie czy nowa cena jest niższa niż stara
                        if ($lastPriceRecord && $foundPrice < $lastPriceRecord->price) {

                            // Przejście po relacjach żeby znaleźć użytkownika
                            $user = $item->product->user;

                            // Wysłanie powiadomienia
                            $user->notify(new \App\Notifications\PriceDropped(
                                $item->product->name,
                                $lastPriceRecord->price,
                                $foundPrice,
                                $item->url,
                                $item->product->target_price // Dodany parametr
                            ));

                            $this->info("Wysłano e-mail ze spadkiem ceny do: " . $user->email);
                        }

                    } else {
                        $this->error("Strona się załadowała, ale nie mogłem znaleźć ceny w danych JSON-LD.");
                    }

                } else {
                    $this->error("Błąd API: {$response->status()}");
                }

            } catch (\Exception $e) {
                $this->error("Błąd systemu: " . $e->getMessage());
            }
        }

        $this->info('Zakończono sprawdzanie.');
    }
}