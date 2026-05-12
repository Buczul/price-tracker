<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceHistory extends Model
{
    protected $fillable = ['product_url_id', 'price'];

    // Relacja zwrotna: Historia ceny należy do konkretnego linku
    public function url()
    {
        return $this->belongsTo(ProductUrl::class, 'product_url_id');
    }
}