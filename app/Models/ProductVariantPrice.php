<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ProductVariant;
class ProductVariantPrice extends Model
{
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
