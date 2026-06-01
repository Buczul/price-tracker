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
        $kluczApi = env('SCRAPER_API_KEY');

        if (!$kluczApi) {
            $this->error('Brak klucza API! Dodaj SCRAPER_API_KEY do pliku .env');
            return;
        }

        // Pobieramy aktywne linki wraz z produktem i użytkownikiem (żeby zoptymalizować wysyłkę e-maili)
        $linki = ProductUrl::whereNull('end_of_tracking_at')
            ->whereHas('product', function($zapytanie) {
                $zapytanie->whereNull('end_of_tracking_at');
            })
            ->with('product.user')
            ->get();

        // KROK 1: Grupowanie po identycznym adresie URL
        $pogrupowaneLinki = $linki->groupBy('url');

        $this->info("Znaleziono " . $linki->count() . " linków w bazie, co daje " . $pogrupowaneLinki->count() . " unikalnych zapytań do sklepu.");

        // KROK 2: Główna pętla wykonująca zapytania HTTP
        foreach ($pogrupowaneLinki as $unikalnyAdres => $modeleLinkow) {
            $this->line("Scrapuję: {$unikalnyAdres} (Przypiętych użytkowników: " . $modeleLinkow->count() . ")");

            try {
                // Konstruowanie zapytania do ScraperAPI
                $adresApi = "http://api.scraperapi.com?api_key={$kluczApi}&url=" . urlencode($unikalnyAdres);

                $odpowiedz = Http::timeout(60)->get($adresApi);

                if ($odpowiedz->successful()) {
                    $przeszukiwacz = new Crawler($odpowiedz->body());
                    $znalezionaCena = null;
                    $skrypty = $przeszukiwacz->filter('script[type="application/ld+json"]')->extract(['_text']);

                    foreach ($skrypty as $skrypt) {
                        $dane = json_decode($skrypt, true);

                        if (!$dane) continue;

                        $elementyDoPrzeszukania = isset($dane['@graph']) ? $dane['@graph'] : (isset($dane['@type']) ? [$dane] : $dane);

                        if (is_array($elementyDoPrzeszukania)) {
                            foreach ($elementyDoPrzeszukania as $elementJson) {
                                $czyToProdukt = isset($elementJson['@type']) && (
                                    $elementJson['@type'] === 'Product' ||
                                    (is_array($elementJson['@type']) && in_array('Product', $elementJson['@type']))
                                );

                                if ($czyToProdukt) {
                                    if (isset($elementJson['offers']['price'])) {
                                        $znalezionaCena = $elementJson['offers']['price'];
                                        break 2;
                                    } elseif (isset($elementJson['offers'][0]['price'])) {
                                        $znalezionaCena = $elementJson['offers'][0]['price'];
                                        break 2;
                                    }
                                }
                            }
                        }
                    }

                    if (!$znalezionaCena) {
                        if (preg_match('/"price"\s*:\s*([\d\.]+)/', $odpowiedz->body(), $dopasowania)) {
                            $znalezionaCena = $dopasowania[1];
                            $this->info("Użyto koła ratunkowego (Regex) dla ceny!");
                        }
                    }

                    if ($znalezionaCena) {
                        $this->info("SUKCES! Znaleziona cena: " . $znalezionaCena . " PLN");

                        // KROK 3: Zapis ceny i powiadomienia dla wszystkich śledzących ten konkretny link
                        foreach ($modeleLinkow as $element) {
                            $ostatniaZapisanaCena = PriceHistory::where('product_url_id', $element->id)
                                ->latest()
                                ->first();

                            $cenaDocelowa = $element->product->target_price;
                            $czyWyslacPowiadomienie = false;

                            if ($ostatniaZapisanaCena) {
                                // Sprawdzamy czy cena SPADŁA w stosunku do ostatniego pomiaru.
                                // Jeśli jest taka sama lub wzrosła, nic nie robimy z powiadomieniem.
                                if ((float)$znalezionaCena < (float)$ostatniaZapisanaCena->price) {

                                    if ($cenaDocelowa !== null) {
                                        // Przypadek 1: Cena przebiła próg z góry na dół
                                        if ($znalezionaCena <= $cenaDocelowa && $ostatniaZapisanaCena->price > $cenaDocelowa) {
                                            $czyWyslacPowiadomienie = true;
                                        }
                                        // Przypadek 2: Cena znów spadła, utrzymując się nadal pod progiem
                                        elseif ($znalezionaCena <= $cenaDocelowa) {
                                            $czyWyslacPowiadomienie = true;
                                        }
                                    } else {
                                        // Brak ceny docelowej - powiadamiamy o każdym spadku
                                        $czyWyslacPowiadomienie = true;
                                    }
                                }
                            }

                            // ZAWSZE zapisujemy cenę, niezależnie czy się zmieniła, czy nie.
                            PriceHistory::create([
                                'product_url_id' => $element->id,
                                'price' => $znalezionaCena
                            ]);

                            if ($ostatniaZapisanaCena && (float)$znalezionaCena === (float)$ostatniaZapisanaCena->price) {
                                $this->line("Cena bez zmian. Zapisano dla utrzymania ciągłości historii.");
                            } else {
                                $this->info("Nowa cena dla: " . $element->product->name . " została zapisana do bazy.");
                            }

                            if ($czyWyslacPowiadomienie) {
                                $uzytkownik = $element->product->user;
                                $uzytkownik->notify(new \App\Notifications\PriceDropped(
                                    $element->product->name,
                                    $ostatniaZapisanaCena->price,
                                    $znalezionaCena,
                                    $element->url,
                                    $cenaDocelowa
                                ));
                                $this->info("Wysłano e-mail ze spadkiem ceny do: " . $uzytkownik->email);
                            }
                        }
                    } else {
                        $this->error("Strona się załadowała, ale nie mogłem znaleźć ceny w danych JSON-LD.");
                    }

                } else {
                    $this->error("Błąd API: {$odpowiedz->status()}");
                }

            } catch (\Exception $wyjatek) {
                $this->error("Błąd systemu: " . $wyjatek->getMessage());
            }
        }

        $this->info('Zakończono sprawdzanie.');
    }
}