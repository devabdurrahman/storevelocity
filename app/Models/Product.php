<?php

namespace App\Models;

// use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class Product extends Model
{
    protected $fillable = ['store_id', 'category_id', 'name', 'price', 'stock', 'images', 'is_featured', 'short_description', 'description', 'compare_price', 'sku', 'unit', 'is_active', 'has_variants', 'option1_name', 'option2_name', 'seo_title', 'seo_description',];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'has_variants' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        // A product may only use a category from its own store.
        // Products made in the admin get their store assigned a moment after this event
        // (Filament does it while creating), so this first check runs once the store is known...
        static::saving(function (Product $product) {
            if ($product->category_id && $product->store_id && ! self::categoryBelongsToStore($product)) {
                throw new \InvalidArgumentException('Category does not belong to this store.');
            }
        });

        // ...and this one confirms it for brand-new products right after they are created.
        static::created(function (Product $product) {
            if ($product->category_id && ! self::categoryBelongsToStore($product)) {
                $product->deleteQuietly();

                throw new \InvalidArgumentException('Category does not belong to this store.');
            }
        });

        // Delete the image files when a product is deleted
        // static::deleting(function (Product $product) {
        //     if (! empty($product->images)) {
        //         Storage::disk('public')->delete($product->images);
        //     }
        // });
    }

    private static function categoryBelongsToStore(Product $product): bool
    {
        return Category::where('id', $product->category_id)
            ->where('store_id', $product->store_id)
            ->exists();
    }

    public function store()
    {
        return $this->belongsTo(\App\Models\Store::class);
    }

    public function category()
    {
        return $this->belongsTo(\App\Models\Category::class);
    }

    public function imageUrls(): array
    {
        return collect($this->images ?? [])
            ->map(fn ($path) => asset('storage/' . $path))
            ->all();
    }

    public function mainImageUrl(): ?string
    {
        return $this->imageUrls()[0] ?? null;
    }

    /** Products customers are allowed to see. */
    public function scopeVisible($query)
    {
        return $query->where('is_active', true);
    }

    public function onSale(): bool
    {
        return $this->compare_price !== null && (float) $this->compare_price > (float) $this->price;
    }

    public function discountPercent(): ?int
    {
        if (! $this->onSale()) {
            return null;
        }

        return (int) round((1 - (float) $this->price / (float) $this->compare_price) * 100);
    }

    /** The description as safe HTML. Raw HTML is removed and unsafe links are blocked. */
    public function descriptionHtml(): ?string
    {
        $text = trim((string) $this->description);

        if ($text === '') {
            return null;
        }

        return Str::markdown($text, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }
    
    public function variants()
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order')->orderBy('id');
    }

    public function activeVariants(): Collection
    {
        return $this->variants->where('is_active', true)->values();
    }

    public function totalStock(): int
    {
        return $this->has_variants
            ? (int) $this->activeVariants()->sum('stock')
            : (int) $this->stock;
    }

    public function isAvailable(): bool
    {
        return $this->totalStock() > 0;
    }

    public function lowStock(): bool
    {
        $stock = $this->totalStock();

        return $stock > 0 && $stock <= 5;
    }

    /** The lowest price a customer can pay (for "From ৳X"). */
    public function priceFrom(): float
    {
        if (! $this->has_variants || $this->activeVariants()->isEmpty()) {
            return (float) $this->price;
        }

        return (float) $this->activeVariants()
            ->map(fn ($variant) => (float) ($variant->price ?? $this->price))
            ->min();
    }

    public function hasPriceRange(): bool
    {
        if (! $this->has_variants || $this->activeVariants()->count() < 2) {
            return false;
        }

        $prices = $this->activeVariants()->map(fn ($variant) => (float) ($variant->price ?? $this->price));

        return $prices->min() !== $prices->max();
    }

    /** The words in the product's web address, for example "cotton-panjabi". */
    public function slugPart(): string
    {
        $slug = Str::slug(Str::limit((string) $this->name, 60, ''), '-', 'bn');

        return $slug !== '' ? $slug : 'product';
    }

    /** "12-cotton-panjabi". The number finds the product, the words are for people and search engines. */
    public function urlKey(): string
    {
        return $this->id . '-' . $this->slugPart();
    }

    public function storefrontUrl(): string
    {
        return route('storefront.show', ['product' => $this->urlKey()]);
    }
}
