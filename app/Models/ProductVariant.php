<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = ['option1', 'option2', 'sku', 'price', 'stock', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /** For example "M / Red". */
    public function label(): string
    {
        return collect([$this->option1, $this->option2])
            ->filter(fn ($value) => filled($value))
            ->implode(' / ');
    }
}