<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Variant extends Model
{
    protected $fillable = [
        'title', 'description'
    ];
    /**
     * Get all of the variants for the Variant
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ProductVariant()
    {
        return $this->hasMany(ProductVariant::class, 'variant_id', 'id')->groupBy('variant');
    }

}
