<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    protected $table = 'media';

    protected $fillable = ['user_id', 'disk', 'path', 'name', 'mime', 'size', 'width', 'height'];

    protected static function booted(): void
    {
        // The file goes when the library entry goes
        static::deleted(function (Media $media) {
            Storage::disk($media->disk)->delete($media->path);
        });
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function url(): string
    {
        return asset('storage/' . $this->path);
    }

    /** Where this image is used, as short labels. Empty means safe to delete. */
    public function usages(): array
    {
        $uses = [];

        Product::where('store_id', $this->store_id)
            ->whereJsonContains('images', $this->path)
            ->pluck('name')
            ->each(function ($name) use (&$uses) {
                $uses[] = 'Product: ' . $name;
            });

        Category::where('store_id', $this->store_id)
            ->where('image', $this->path)
            ->pluck('name')
            ->each(function ($name) use (&$uses) {
                $uses[] = 'Category: ' . $name;
            });

        $settings = (array) ($this->store->theme_settings ?? []);

        if (($settings['logo'] ?? null) === $this->path) {
            $uses[] = 'Store logo';
        }

        if (($settings['hero_image'] ?? null) === $this->path) {
            $uses[] = 'Home page banner';
        }

        if (($settings['hero_image_mobile'] ?? null) === $this->path) {
            $uses[] = 'Home page banner (phones)';
        }

        foreach ((array) ($settings['promos'] ?? []) as $promo) {
            if (is_array($promo) && ($promo['image'] ?? null) === $this->path) {
                $uses[] = 'Promotion: ' . mb_substr((string) ($promo['title'] ?? 'untitled'), 0, 30);
            }
        }

        foreach ((array) ($settings['hero_slides'] ?? []) as $slide) {
            if (is_array($slide) && (($slide['image'] ?? null) === $this->path || ($slide['mobile_image'] ?? null) === $this->path)) {
                $uses[] = 'Home page slider';
                break;
            }
        }

        if ((((array) $this->store->seo)['image'] ?? null) === $this->path) {
            $uses[] = 'Share image';
        }

        return $uses;
    }
}