<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'sort_order', 'is_featured', 'image', 'seo_title', 'seo_description',];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
        ];
    }

    /** Tile picture: the uploaded category image, otherwise the first product photo. */
    public function coverUrl(): ?string
    {
        $prefix = 'stores/' . $this->store_id . '/';

        if ($this->image && str_starts_with($this->image, $prefix) && ! str_contains($this->image, '..')) {
            return asset('storage/' . $this->image);
        }

        return $this->products()->where('is_active', true)->latest()->limit(5)->get()
            ->map(fn ($product) => $product->mainImageUrl())
            ->filter()
            ->first();
    }
}
