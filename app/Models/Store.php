<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Store extends Model
{
    public const ROLE_OWNER = 'owner';
    public const ROLE_MANAGER = 'manager';
    public const ROLE_STAFF = 'staff';

    protected $fillable = ['name', 'slug', 'custom_domain', 'is_active', 'shipping_fee', 'facebook_pixel_id', 'ga_measurement_id', 'theme', 'theme_settings', 'steadfast_api_key', 'steadfast_secret_key', 'seo', 'delivery_inside_enabled', 'delivery_inside_free_over',
'delivery_outside_enabled', 'delivery_outside_fee', 'delivery_outside_free_over',];
    protected $hidden = ['steadfast_api_key', 'steadfast_secret_key', 'steadfast_webhook_token'];

    public const RESERVED_SLUGS = [
        'www', 'admin', 'api', 'app', 'mail', 'smtp', 'ftp', 'dashboard', 'support',
        'help', 'blog', 'shop', 'store', 'cdn', 'static', 'assets', 'status',
        'billing', 'login', 'register', 'webhooks', 'localhost', 'test', 'demo',
    ];

    protected function casts(): array
    {
        return [
            'theme_settings' => 'array',
            'steadfast_api_key' => 'encrypted',
            'steadfast_secret_key' => 'encrypted',
            'steadfast_webhook_token' => 'encrypted',
            'seo' => 'array',
            'delivery_inside_enabled' => 'boolean',
            'delivery_outside_enabled' => 'boolean',
        ];
    }

    public function users()
    {
        return $this->belongsToMany(\App\Models\User::class)->withPivot('role');
    }

    public function products()
    {
        return $this->hasMany(\App\Models\Product::class);
    }
    
    public function orders()
    {
        return $this->hasMany(\App\Models\Order::class);
    }
    
    public function categories()
    {
        return $this->hasMany(\App\Models\Category::class);
    }
    
    public function blockedPhones()
    {
        return $this->hasMany(\App\Models\BlockedPhone::class);
    }

    public function storefrontUrl(): string
    {
        $request = request();
        $port = $request->getPort();
        $portPart = in_array($port, [80, 443], true) ? '' : ':' . $port;

        return $request->getScheme() . '://' . $this->slug . '.' . config('app.central_domain') . $portPart;
    }

    public function media()
    {
        return $this->hasMany(\App\Models\Media::class);
    }

    /** Only the products customers may see. The admin uses products(). */
    public function visibleProducts()
    {
        return $this->products()->where('is_active', true);
    }

    protected static function booted(): void
    {
        static::creating(function (Store $store) {
            $store->webhook_key = $store->webhook_key ?: (string) Str::ulid();
            $store->steadfast_webhook_token = $store->steadfast_webhook_token ?: Str::random(40);
        });

        static::updated(function (Store $store) {
            if ($store->wasChanged(['steadfast_api_key', 'steadfast_secret_key'])) {
                Cache::forget("steadfast:balance:{$store->id}");
            }
        });
    }

    public function hasSteadfast(): bool
    {
        return filled($this->steadfast_api_key) && filled($this->steadfast_secret_key);
    }

    public function ensureWebhookCredentials(): void
    {
        if (! $this->webhook_key) {
            $this->webhook_key = (string) Str::ulid();
        }

        if (blank($this->steadfast_webhook_token)) {
            $this->steadfast_webhook_token = Str::random(40);
        }

        if ($this->isDirty()) {
            $this->save();
        }
    }

    public function courierChecks()
    {
        return $this->hasMany(\App\Models\CourierCheck::class);
    }
}
