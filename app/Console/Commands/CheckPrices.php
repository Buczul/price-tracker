<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProductUrl;
use App\Models\PriceHistory;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class CheckPrices extends Command
{
    // 1. To jest nazwa, którą będziesz wpisywać w terminalu
    protected $signature = 'prices:check';

    // 2. Opis komendy
    protected $description = 'Sprawdza aktualne ceny wszystkich śledzonych produktów';

    public function handle()
    {
        $this->info('Rozpoczynam sprawdzanie cen przez API...');

        // Pobieramy klucz z pliku .env
        $apiKey = env('SCRAPER_API_KEY');

        if (!$apiKey) {
            $this->error('Brak klucza API! Dodaj SCRAPER_API_KEY do pliku .env');
            return;
        }

        $urls = ProductUrl::all();

        foreach ($urls as $item) {
            $this->line("Scrapuję: {$item->url}");

            try {
                // Konstruujemy zapytanie do ScraperAPI.
                // Podajemy im nasz klucz i url sklepu, który chcemy odwiedzić
                $apiUrl = "http://api.scraperapi.com?api_key={$apiKey}&url=" . urlencode($item->url);

                // Wysyłamy proste zapytanie - ScraperAPI zajmie się udawaniem przeglądarki
                $response = Http::timeout(60)->get($apiUrl);

                if ($response->successful()) {
                    $crawler = new Crawler($response->body());
                    $foundPrice = null;

                    // Szukamy tagów <script>, w których sklepy trzymają dane dla wyszukiwarek
                    $scripts = $crawler->filter('script[type="application/ld+json"]')->extract(['_text']);

                    foreach ($scripts as $script) {
                        if (str_contains($script, '"@type":"Product"')) {
                            $data = json_decode($script, true);

                            // Sprawdzamy czy w tych danych jest cena (format różni się minimalnie w zależności od sklepu)
                            if (isset($data['offers']['price'])) {
                                $foundPrice = $data['offers']['price'];
                                break;
                            } elseif (isset($data['offers'][0]['price'])) {
                                $foundPrice = $data['offers'][0]['price'];
                                break;
                            }
                        }
                    }

                    if ($foundPrice) {
                        $this->info("SUKCES! Znaleziona cena: " . $foundPrice . " PLN");

                        // Zapisujemy cenę do bazy danych
                        PriceHistory::create([
                            'product_url_id' => $item->id,
                            'price' => $foundPrice
                        ]);
                        $this->info("Cena została zapisana w historii!");

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