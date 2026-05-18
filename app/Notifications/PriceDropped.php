<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PriceDropped extends Notification
{
    use Queueable;

    protected $productName;
    protected $oldPrice;
    protected $newPrice;
    protected $url;
    protected $targetPrice;

    // Dodaliśmy $targetPrice do konstruktora
    public function __construct($productName, $oldPrice, $newPrice, $url, $targetPrice)
    {
        $this->productName = $productName;
        $this->oldPrice = $oldPrice;
        $this->newPrice = $newPrice;
        $this->url = $url;
        $this->targetPrice = $targetPrice;
    }

    // TUTAJ DZIEJE SIĘ MAGIA DECYZYJNA
    public function via(object $notifiable): array
    {
        $channels = ['database']; // Zawsze wysyłamy do bazy (dzwoneczek)

        // Jeśli użytkownik ustawił cenę docelową I nowa cena jest od niej mniejsza lub równa -> wyślij też Maila
        if ($this->targetPrice !== null && $this->newPrice <= $this->targetPrice) {
            $channels[] = 'mail';
        }

        return $channels;
    }

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

    // Ta metoda formatuje dane, które pojawią się w rozwijanym menu dzwoneczka
    public function toArray(object $notifiable): array
    {
        return [
            'message' => "Cena {$this->productName} spadła do {$this->newPrice} PLN!",
            'url' => $this->url
        ];
    }
}