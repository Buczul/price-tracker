<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model reprezentujący konkretny adres URL sklepu dla śledzonego produktu.
 */
class ProductUrl extends Model
{
    /**
     * Atrybuty, które można przypisać masowo.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'url',
        'store_name',
    ];

    /**
     * Relacja: Adres URL produktu zawiera wiele rekordów historii cen.
     *
     * @return HasMany
     */
    public function priceHistories(): HasMany
    {
        return $this->hasMany(PriceHistory::class, 'product_url_id');
    }

    /**
     * Relacja: Adres URL produktu należy do pojedynczego produktu.
     *
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}