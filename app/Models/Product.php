<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name', 'user_id', 'target_price'];

    // relacja: Produkt ma wiele linków
    public function urls() {
        return $this->hasMany(ProductUrl::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
