<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model reprezentujący produkt śledzony przez użytkownika.
 */
class Product extends Model
{
    /**
     * Atrybuty, które można przypisać masowo.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'user_id',
        'target_price',
    ];

    /**
     * Relacja: Produkt ma wiele śledzonych adresów URL (linków do sklepu).
     *
     * @return HasMany
     */
    public function urls(): HasMany
    {
        return $this->hasMany(ProductUrl::class);
    }

    /**
     * Relacja: Produkt należy do konkretnego użytkownika.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}