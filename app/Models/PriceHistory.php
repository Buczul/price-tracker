<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model reprezentujący pojedynczy wpis z historią ceny dla danego sklepu (linku).
 */
class PriceHistory extends Model
{
    /**
     * Atrybuty, które mogą być masowo przypisywane (Mass Assignment).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_url_id',
        'price',
    ];

    /**
     * Relacja zwrotna: Historia ceny należy do konkretnego linku w systemie.
     *
     * @return BelongsTo
     */
    public function url(): BelongsTo
    {
        return $this->belongsTo(ProductUrl::class, 'product_url_id');
    }
}