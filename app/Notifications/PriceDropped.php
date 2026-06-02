<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Powiadomienie wysyłane, gdy cena śledzonego produktu spadnie poniżej ceny docelowej.
 */
class PriceDropped extends Notification
{
    use Queueable;

    protected string $productName;
    protected ?float $oldPrice;
    protected float $newPrice;
    protected string $url;
    protected ?float $targetPrice;

    /**
     * Utwórz nową instancję powiadomienia.
     *
     * @param string $productName
     * @param float|null $oldPrice
     * @param float $newPrice
     * @param string $url
     * @param float|null $targetPrice
     */
    public function __construct(string $productName, ?float $oldPrice, float $newPrice, string $url, ?float $targetPrice)
    {
        $this->productName = $productName;
        $this->oldPrice    = $oldPrice;
        $this->newPrice    = $newPrice;
        $this->url         = $url;
        $this->targetPrice = $targetPrice;
    }

    /**
     * Pobierz kanały dostarczania powiadomień.
     *
     * @param object $notifiable
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        // Sprawdź, czy cena docelowa jest ustawiona, nowa cena jest niższa od ceny docelowej i czy użytkownik ma włączone powiadomienia e-mail
        if ($this->targetPrice !== null &&
            $this->newPrice <= $this->targetPrice &&
            $notifiable->notify_via_email) {

            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Pobierz reprezentację pocztową powiadomienia.
     *
     * @param object $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('OKAZJA! Cena w dół: ' . $this->productName)
            ->greeting('Cześć!')
            ->line('Cena produktu spadła poniżej Twojej kwoty docelowej (' . $this->targetPrice . ' PLN)!')
            ->line('**Produkt:** ' . $this->productName)
            ->line('**Nowa cena:** ' . $this->newPrice . ' PLN')
            ->action('Przejdź do sklepu', $this->url);
    }

    /**
     * Pobierz reprezentację tablicową powiadomienia.
     * Dane sformatowane w tym miejscu będą wyświetlane w rozwijanym menu powiadomień użytkownika.
     *
     * @param object $notifiable
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => "Cena {$this->productName} spadła do {$this->newPrice} PLN!",
            'url'     => $this->url
        ];
    }
}