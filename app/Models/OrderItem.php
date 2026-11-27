<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = ['product_id', 'product_name', 'unit_price', 'quantity', 'total', 'variant_id', 'variant_name'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    
        public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}