<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = ['product_id','variant_id','variant'];

     public function ProductVariantPrices ()
    {
        return ProductVariantPrice::where('product_variant_one', $this->id)->orWhere('product_variant_two', $this->id)->orWhere('product_variant_three', $this->id)->get();
    }
}
