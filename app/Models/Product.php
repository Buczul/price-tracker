<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['user_id', 'name'];

    // relacja: Produkt ma wiele linków
    public function urls() {
        return $this->hasMany(ProductUrl::class);
    }
}
