<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ProductVariant;
class ProductVariantPrice extends Model
{
    protected $fillable = ['product_variant_one','product_variant_two','product_variant_three','price','stock','product_id'];
    /**
     * Get the user associated with the ProductVariantPrice
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function ProductVariant($column_name)
    {
        return ProductVariant::where('id', $this->$column_name)->first();
    }
}
