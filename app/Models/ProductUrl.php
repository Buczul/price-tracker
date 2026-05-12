<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductUrl extends Model
{
    protected $fillable = ['product_id', 'url', 'store_name'];

    public function priceHistories()
    {
        return $this->hasMany(PriceHistory::class, 'product_url_id');
    }
}
