<?php

use App\Http\Controllers\StorefrontController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\SteadfastWebhookController;
use App\Http\Controllers\SeoFilesController;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::domain('{storeSlug}.' . config('app.central_domain'))
    ->middleware(['resolve.store', 'throttle:storefront'])
    ->group(function () {
        Route::get('/', [StorefrontController::class, 'home'])->name('storefront.index');
        Route::get('/products', [StorefrontController::class, 'index'])->name('storefront.products');
        Route::get('/products/{product}', [StorefrontController::class, 'show'])
            ->where('product', '[0-9]+(-[A-Za-z0-9-]*)?')
            ->name('storefront.show');
        Route::view('/cart', 'storefront.cart')->name('storefront.cart');
        Route::view('/checkout', 'storefront.checkout')->name('storefront.checkout');
        Route::get('/order/{publicId}', [StorefrontController::class, 'thanks'])->name('storefront.order.thanks');
        Route::get('/category/{slug}', [StorefrontController::class, 'category'])->name('storefront.category');
        Route::get('/robots.txt', [SeoFilesController::class, 'robots'])->name('storefront.robots');
        Route::get('/sitemap.xml', [SeoFilesController::class, 'sitemap'])->name('storefront.sitemap');
    });
    
Route::get('/', LandingController::class)->name('landing');

Route::get('/robots.txt', fn () => response(
    "User-agent: *\nDisallow: /admin\nDisallow: /platform\nDisallow: /webhooks\nAllow: /\n",
    200,
    ['Content-Type' => 'text/plain; charset=UTF-8']
));

Route::post('/webhooks/steadfast/{webhookKey}', SteadfastWebhookController::class)
    ->middleware('throttle:120,1')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class])
    ->name('webhooks.steadfast');