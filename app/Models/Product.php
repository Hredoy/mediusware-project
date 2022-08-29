<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'title', 'sku', 'description'
    ];
    public function product_variant_prices()
    {

            return $this->hasMany(ProductVariantPrice::class, 'product_id', 'id');
    }
    public function ProductVariant()
    {

            return $this->hasMany(ProductVariant::class, 'product_id', 'id');
    }
}
