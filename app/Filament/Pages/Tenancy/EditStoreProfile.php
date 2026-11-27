<?php

namespace App\Filament\Pages\Tenancy;

use App\Exceptions\CourierException;
use App\Filament\Support\MediaField;
use App\Models\Store;
use App\Services\SteadfastClient;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Tenancy\EditTenantProfile;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\ConnectionException;
use Filament\Forms\Components\Repeater;
use App\Support\SeoCheck;

class EditStoreProfile extends EditTenantProfile
{
    public static function getLabel(): string
    {
        return 'Store settings';
    }

    public static function canView(Model $tenant): bool
    {
        return (bool) auth()->user()?->hasRoleIn($tenant, [Store::ROLE_OWNER]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Settings')
                ->columnSpanFull()
                ->persistTabInQueryString('tab')
                ->tabs([
                    $this->storeTab(),
                    $this->lookTab(),
                    $this->homeTab(),
                    $this->sectionsTab(),
                    $this->deliveryTab(),
                    $this->seoTab(),
                    $this->trackingTab(),
                ]),
        ]);
    }

    private function storeTab(): Tab
    {
        return Tab::make('Store')
            ->icon('heroicon-o-building-storefront')
            ->columns(2)
            ->schema([
                TextInput::make('name')
                    ->label('Store name')
                    ->required()
                    ->maxLength(100),

                TextInput::make('storefront_address')
                    ->label('Your store address')
                    ->readOnly()
                    ->dehydrated(false)
                    ->copyable()
                    ->afterStateHydrated(fn (TextInput $component) => $component->state(Filament::getTenant()->storefrontUrl()))
                    ->helperText('Share this link with your customers.'),

                TextInput::make('theme_settings.whatsapp')
                    ->label('WhatsApp number')
                    ->tel()
                    ->trim()
                    ->regex('/^(?:\+?88)?01[3-9]\d{8}$/')
                    ->helperText('Optional. Adds a chat button to your store. Example: 01712345678.'),

                TextInput::make('theme_settings.facebook_url')
                    ->label('Facebook page link')
                    ->url()
                    ->trim()
                    ->regex('#^https://(www\.|m\.)?facebook\.com/[A-Za-z0-9._\-/]+$#')
                    ->helperText('Optional. Example: https://www.facebook.com/yourpage'),
                    
                Textarea::make('theme_settings.footer_about_text')
                    ->label('Footer About us')
                    ->maxLength(220)
                    ->helperText('About us text for footer, displayed after the logo on footer'),
            ]);
    }

    private function lookTab(): Tab
    {
        return Tab::make('Look & feel')
            ->icon('heroicon-o-paint-brush')
            ->columns(2)
            ->schema([
                Radio::make('theme')
                    ->label('Theme')
                    ->options(collect(config('themes.catalog'))->map(fn ($theme) => $theme['name'])->all())
                    ->descriptions(collect(config('themes.catalog'))->map(fn ($theme) => $theme['best_for'])->all())
                    ->required()
                    ->columnSpanFull(),

                ColorPicker::make('theme_settings.brand_color')
                    ->label('Brand colour')
                    ->regex('/^#[0-9a-fA-F]{6}$/')
                    ->helperText('Used for buttons and highlights across your store.'),

                Select::make('theme_settings.brand_text')
                    ->label('Text on buttons')
                    ->options([
                        'auto' => 'Automatic (recommended)',
                        'light' => 'Always white',
                        'dark' => 'Always dark',
                    ])
                    ->default('auto')
                    ->native(false)
                    ->helperText('Automatic keeps the text easy to read: bright colours like lime green or orange are made a little deeper so white text works. Choose white or dark to use your exact colour instead.'),

                TextInput::make('theme_settings.announcement')
                    ->label('Announcement bar')
                    ->maxLength(120)
                    ->helperText('Optional short message at the top of every page, for example free delivery over ৳2,000.'),

                MediaField::upload('theme_settings.logo')
                    ->label('Logo')
                    ->maxSize(1024)
                    ->imageResizeMode('contain')
                    ->imageResizeTargetWidth('600')
                    ->imageResizeTargetHeight('300')
                    ->helperText('Optional. Your store name shows if you leave this empty.')
                    ->columnSpanFull(),

                Actions::make([
                    MediaField::picker('theme_settings.logo'),
                ])->columnSpanFull(),

                Select::make('theme_settings.logo_size')
				    ->label('Logo size')
				    ->options(['small' => 'Small', 'medium' => 'Medium', 'large' => 'Large'])
				    ->default('medium')
				    ->native(false)
				    ->helperText('How big your logo appears at the top of your store.'),

            ]);
    }

    // private function homeTab(): Tab
	// {
	//     return Tab::make('Home page')
	//         ->icon('heroicon-o-home')
	//         ->columns(2)
	//         ->schema([
	//             Toggle::make('theme_settings.hero_enabled')
	//                 ->label('Show banner')
	//                 ->default(true),

	//             Toggle::make('theme_settings.show_new_arrivals')
	//                 ->label('Show the "New arrivals" section')
	//                 ->default(true),

	//             MediaField::upload('theme_settings.hero_image')
	//                 ->label('Banner photo')
	//                 ->imageResizeMode('contain')
	//                 ->imageResizeTargetWidth('1920')
	//                 ->imageResizeTargetHeight('1080')
	//                 ->helperText('A wide picture, about 1600 x 600. It is never cropped.')
	//                 ->columnSpanFull(),

	//             Actions::make([
	//                 MediaField::picker('theme_settings.hero_image'),
	//             ])->columnSpanFull(),

	//             Radio::make('theme_settings.hero_style')
	//                 ->label('Banner style')
	//                 ->live()
	//                 ->default('picture')
	//                 ->options([
	//                     'picture' => 'Picture only',
	//                     'overlay' => 'Text on the picture',
	//                     'split' => 'Picture beside text',
	//                 ])
	//                 ->descriptions([
	//                     'picture' => 'Best when your picture already has words and a button on it. It is shown whole, and clicking it opens the page you choose below.',
	//                     'overlay' => 'Your headline and button on top of the picture, with a dark shade so they stay readable. Good for plain photos.',
	//                     'split' => 'Picture on one side, your text on the other. Good for product photos.',
	//                 ])
	//                 ->columnSpanFull(),

	//             MediaField::upload('theme_settings.hero_image_mobile')
	//                 ->label('Banner photo for phones (optional)')
	//                 ->imageResizeMode('contain')
	//                 ->imageResizeTargetWidth('1000')
	//                 ->imageResizeTargetHeight('1250')
	//                 ->helperText('A taller picture that reads well on a small screen, for example 4 x 5. If you leave it empty, the main banner is used.')
	//                 ->columnSpanFull(),

	//             Actions::make([
	//                 MediaField::picker('theme_settings.hero_image_mobile'),
	//             ])->columnSpanFull(),

	//             TextInput::make('theme_settings.hero_alt')
	//                 ->label('Describe the picture (for screen readers)')
	//                 ->maxLength(125)
	//                 ->helperText('One short sentence, for example "Eid sale: 20% off all rice and oil".')
	//                 ->visible(fn (Get $get) => filled($get('theme_settings.hero_image')))
	//                 ->columnSpanFull(),

	//             TextInput::make('theme_settings.hero_title')
	//                 ->label('Headline')
	//                 ->maxLength(70)
	//                 ->placeholder('Eid collection is here')
	//                 ->helperText('Leave empty to show "Welcome to" and your store name.')
	//                 ->visible(fn (Get $get) => ! filled($get('theme_settings.hero_image')) || ($get('theme_settings.hero_style') ?? 'picture') !== 'picture'),

	//             TextInput::make('theme_settings.hero_subtitle')
	//                 ->label('Short text')
	//                 ->maxLength(160)
	//                 ->visible(fn (Get $get) => ! filled($get('theme_settings.hero_image')) || ($get('theme_settings.hero_style') ?? 'picture') !== 'picture'),

	//             TextInput::make('theme_settings.hero_button_text')
	//                 ->label('Button text')
	//                 ->maxLength(24)
	//                 ->placeholder('Shop now')
	//                 ->visible(fn (Get $get) => ! filled($get('theme_settings.hero_image')) || ($get('theme_settings.hero_style') ?? 'picture') !== 'picture'),

	//             Select::make('theme_settings.hero_button_target')
	//                 ->label('The banner and button open')
	//                 ->options(fn () => ['all' => 'All products'] + Filament::getTenant()->categories()->orderBy('name')->pluck('name', 'id')->all())
	//                 ->default('all')
	//                 ->native(false),
	//         ]);
	// }

    private function homeTab(): Tab
    {
        $isSlider = fn (Get $get): bool => ($get('theme_settings.hero_style') ?? 'picture') === 'slider';
        $notSlider = fn (Get $get): bool => ($get('theme_settings.hero_style') ?? 'picture') !== 'slider';

        // The headline, short text and button only show on banners that carry text
        $showsText = fn (Get $get): bool => ($get('theme_settings.hero_style') ?? 'picture') !== 'slider'
            && (! filled($get('theme_settings.hero_image')) || ($get('theme_settings.hero_style') ?? 'picture') !== 'picture');

        return Tab::make('Home page')
            ->icon('heroicon-o-home')
            ->columns(2)
            ->schema([
                Toggle::make('theme_settings.hero_enabled')
                    ->label('Show banner')
                    ->default(true),

                Toggle::make('theme_settings.show_new_arrivals')
                    ->label('Show the "New arrivals" section')
                    ->default(true),

                Radio::make('theme_settings.hero_style')
                    ->label('Banner style')
                    ->live()
                    ->default('picture')
                    ->options([
                        'picture' => 'Picture only',
                        'slider' => 'Slider (several pictures)',
                        'overlay' => 'Text on the picture',
                        'split' => 'Picture beside text',
                    ])
                    ->descriptions([
                        'picture' => 'Best when your picture already has words and a button on it. It is shown whole, and clicking it opens the page you choose below.',
                        'slider' => 'Several pictures that slide, like Picture only. Each picture can open its own page. Visitors can swipe, and slides can change by themselves.',
                        'overlay' => 'Your headline and button on top of the picture, with a dark shade so they stay readable. Good for plain photos.',
                        'split' => 'Picture on one side, your text on the other. Good for product photos.',
                    ])
                    ->columnSpanFull(),

                // ---- Slider ----
                Repeater::make('theme_settings.hero_slides')
                    ->label('Slides')
                    ->visible($isSlider)
                    ->maxItems(6)
                    ->defaultItems(0)
                    ->collapsible()
                    ->reorderable()
                    ->addActionLabel('Add a slide')
                    ->itemLabel(fn (array $state): ?string => ($state['alt'] ?? null) ?: 'Slide')
                    ->helperText('Use pictures of the same size, about 1600 x 600, with any words in the middle. The shape of the first slide sets the shape of the whole slider. Up to 6 slides.')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        MediaField::upload('image')
                            ->label('Picture')
                            ->required()
                            ->imageResizeMode('contain')
                            ->imageResizeTargetWidth('1920')
                            ->imageResizeTargetHeight('1080')
                            ->columnSpanFull(),

                        Actions::make([
                            MediaField::picker('image'),
                        ])->columnSpanFull(),

                        MediaField::upload('mobile_image')
                            ->label('Picture for phones (optional)')
                            ->imageResizeMode('contain')
                            ->imageResizeTargetWidth('1000')
                            ->imageResizeTargetHeight('1250')
                            ->helperText('A taller picture that reads well on a small screen, for example 4 x 5.')
                            ->columnSpanFull(),

                        Actions::make([
                            MediaField::picker('mobile_image'),
                        ])->columnSpanFull(),

                        TextInput::make('alt')
                            ->label('Describe the picture (for screen readers)')
                            ->maxLength(125)
                            ->placeholder('Eid sale: 20% off all rice and oil'),

                        Select::make('target')
                            ->label('Clicking the picture opens')
                            ->options(fn () => $this->slideTargets())
                            ->default('all')
                            ->searchable()
                            ->native(false),
                    ]),

                Toggle::make('theme_settings.hero_slider_autoplay')
                    ->label('Change slides automatically')
                    ->default(true)
                    ->live()
                    ->visible($isSlider),

                Select::make('theme_settings.hero_slider_seconds')
                    ->label('Time on each slide')
                    ->options([4 => '4 seconds', 5 => '5 seconds', 6 => '6 seconds', 8 => '8 seconds', 10 => '10 seconds'])
                    ->default(5)
                    ->native(false)
                    ->visible(fn (Get $get): bool => $isSlider($get) && (bool) ($get('theme_settings.hero_slider_autoplay') ?? true)),

                // ---- A single picture ----
                MediaField::upload('theme_settings.hero_image')
                    ->label('Banner photo')
                    ->imageResizeMode('contain')
                    ->imageResizeTargetWidth('1920')
                    ->imageResizeTargetHeight('1080')
                    ->live()
                    ->helperText('A wide picture, about 1600 x 600. It is never cropped.')
                    ->visible($notSlider)
                    ->columnSpanFull(),

                Actions::make([
                    MediaField::picker('theme_settings.hero_image'),
                ])->visible($notSlider)->columnSpanFull(),

                MediaField::upload('theme_settings.hero_image_mobile')
                    ->label('Banner photo for phones (optional)')
                    ->imageResizeMode('contain')
                    ->imageResizeTargetWidth('1000')
                    ->imageResizeTargetHeight('1250')
                    ->helperText('A taller picture that reads well on a small screen, for example 4 x 5. If you leave it empty, the main banner is used.')
                    ->visible($notSlider)
                    ->columnSpanFull(),

                Actions::make([
                    MediaField::picker('theme_settings.hero_image_mobile'),
                ])->visible($notSlider)->columnSpanFull(),

                TextInput::make('theme_settings.hero_alt')
                    ->label('Describe the picture (for screen readers)')
                    ->maxLength(125)
                    ->helperText('One short sentence, for example "Eid sale: 20% off all rice and oil".')
                    ->visible(fn (Get $get): bool => $notSlider($get) && filled($get('theme_settings.hero_image')))
                    ->columnSpanFull(),

                // ---- Text banners ----
                TextInput::make('theme_settings.hero_title')
                    ->label('Headline')
                    ->maxLength(70)
                    ->placeholder('Eid collection is here')
                    ->helperText('Leave empty to show "Welcome to" and your store name.')
                    ->visible($showsText),

                TextInput::make('theme_settings.hero_subtitle')
                    ->label('Short text')
                    ->maxLength(160)
                    ->visible($showsText),

                TextInput::make('theme_settings.hero_button_text')
                    ->label('Button text')
                    ->maxLength(24)
                    ->placeholder('Shop now')
                    ->visible($showsText),

                Select::make('theme_settings.hero_button_target')
                    ->label('The banner and button open')
                    ->options(fn () => ['all' => 'All products'] + Filament::getTenant()->categories()->orderBy('name')->pluck('name', 'id')->all())
                    ->default('all')
                    ->native(false)
                    ->visible($notSlider),
            ]);
    }

    private function deliveryTab(): Tab
    {
        return Tab::make('Delivery')
            ->icon('heroicon-o-truck')
            ->schema([
                Section::make('Inside Dhaka')
                    ->description('Orders delivered within Dhaka city.')
                    ->columns(3)
                    ->schema([
                        Toggle::make('delivery_inside_enabled')
                            ->label('Deliver inside Dhaka')
                            ->default(true)
                            ->live()
                            ->rules([
                                fn (Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                    if (! $value && ! $get('delivery_outside_enabled')) {
                                        $fail('Turn on at least one delivery area.');
                                    }
                                },
                            ]),

                        TextInput::make('shipping_fee')
                            ->label('Delivery charge')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('৳')
                            ->required(fn (Get $get) => (bool) $get('delivery_inside_enabled'))
                            ->disabled(fn (Get $get) => ! $get('delivery_inside_enabled')),

                        TextInput::make('delivery_inside_free_over')
                            ->label('Free delivery over (optional)')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('৳')
                            ->disabled(fn (Get $get) => ! $get('delivery_inside_enabled'))
                            ->helperText('Leave empty for no free delivery.'),
                    ]),

                Section::make('Outside Dhaka')
                    ->description('Orders delivered to the rest of the country.')
                    ->columns(3)
                    ->schema([
                        Toggle::make('delivery_outside_enabled')
                            ->label('Deliver outside Dhaka')
                            ->default(false)
                            ->live()
                            ->rules([
                                fn (Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                    if (! $value && ! $get('delivery_inside_enabled')) {
                                        $fail('Turn on at least one delivery area.');
                                    }
                                },
                            ]),

                        TextInput::make('delivery_outside_fee')
                            ->label('Delivery charge')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('৳')
                            ->required(fn (Get $get) => (bool) $get('delivery_outside_enabled'))
                            ->disabled(fn (Get $get) => ! $get('delivery_outside_enabled')),

                        TextInput::make('delivery_outside_free_over')
                            ->label('Free delivery over (optional)')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('৳')
                            ->disabled(fn (Get $get) => ! $get('delivery_outside_enabled'))
                            ->helperText('Leave empty for no free delivery.'),
                    ]),

                Section::make('Steadfast Courier')
                    ->description('Connect your own Steadfast merchant account to send parcels straight from your orders.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('steadfast_api_key')
                            ->label('API key')
                            ->password()
                            ->revealable()
                            ->autocomplete(false)
                            ->afterStateHydrated(fn (TextInput $component) => $component->state(null))
                            ->dehydrated(fn (?string $state) => filled($state))
                            ->helperText('Stored encrypted. Leave blank to keep the current key.'),

                        TextInput::make('steadfast_secret_key')
                            ->label('Secret key')
                            ->password()
                            ->revealable()
                            ->autocomplete(false)
                            ->afterStateHydrated(fn (TextInput $component) => $component->state(null))
                            ->dehydrated(fn (?string $state) => filled($state))
                            ->helperText('Stored encrypted. Leave blank to keep the current key.'),

                        Actions::make([
                            Action::make('testSteadfast')
                                ->label('Test connection')
                                ->icon('heroicon-o-signal')
                                ->color('gray')
                                ->action(function () {
                                    $store = Filament::getTenant()->refresh();

                                    if (! $store->hasSteadfast() && ! config('services.steadfast.fake')) {
                                        Notification::make()->warning()->title('Save your API key and secret key first, then test.')->send();

                                        return;
                                    }

                                    try {
                                        $balance = (new SteadfastClient($store))->balance();

                                        Notification::make()->success()
                                            ->title('Connected to Steadfast')
                                            ->body('Your balance is ৳' . number_format($balance, 2))
                                            ->send();
                                    } catch (CourierException $e) {
                                        Notification::make()->danger()->title('Steadfast said no')->body($e->getMessage())->send();
                                    } catch (ConnectionException) {
                                        Notification::make()->danger()->title('Could not reach Steadfast')->body('Please try again in a moment.')->send();
                                    }
                                }),
                        ])->columnSpanFull(),

                        TextInput::make('steadfast_webhook_url')
                            ->label('Webhook callback URL')
                            ->readOnly()
                            ->dehydrated(false)
                            ->copyable()
                            ->afterStateHydrated(function (TextInput $component) {
                                $store = Filament::getTenant();
                                $store->ensureWebhookCredentials();
                                $component->state(route('webhooks.steadfast', ['webhookKey' => $store->webhook_key]));
                            })
                            ->helperText('Paste this into the webhook settings of your Steadfast merchant panel so parcel updates arrive instantly. It must be a public https address, so it works once your site is live.')
                            ->columnSpanFull(),

                        TextInput::make('steadfast_webhook_token')
                            ->label('Webhook bearer token')
                            ->password()
                            ->revealable()
                            ->readOnly()
                            ->dehydrated(false)
                            ->copyable()
                            ->afterStateHydrated(function (TextInput $component) {
                                $store = Filament::getTenant();
                                $store->ensureWebhookCredentials();
                                $component->state($store->steadfast_webhook_token);
                            })
                            ->helperText('Use this as the auth token for the webhook. Keep it private.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    private function seoTab(): Tab
    {
        return Tab::make('Search engines')
            ->icon('heroicon-o-magnifying-glass')
            ->columns(2)
            ->schema([
                Toggle::make('seo.indexable')
                    ->label('Let search engines list my store')
                    ->default(false)
                    ->helperText('Keep this off while you are setting up. Turn it on when your products are ready.')
                    ->columnSpanFull(),

                TextInput::make('seo.title')
                    ->label('Home page title')
                    ->maxLength(70)
                    ->live(onBlur: true)
                    ->hint(fn (Get $get) => mb_strlen((string) $get('seo.title')) . ' / 60 recommended')
                    ->helperText('Leave empty to use your store name.')
                    ->columnSpanFull(),

                Textarea::make('seo.description')
                    ->label('Home page description')
                    ->rows(2)
                    ->maxLength(170)
                    ->live(onBlur: true)
                    ->hint(fn (Get $get) => mb_strlen((string) $get('seo.description')) . ' / 160 recommended')
                    ->helperText('One or two sentences about what you sell. This is what people read under your name on Google.')
                    ->columnSpanFull(),

                TextInput::make('seo.keyword')
                    ->label('Main keyword for your home page (optional)')
                    ->maxLength(60)
                    ->live(onBlur: true)
                    ->helperText(fn (Get $get) => SeoCheck::summary($get('seo.keyword'), [
                        'In the home page title' => trim((string) $get('seo.title')) ?: (string) $get('name'),
                        'In the home page description' => (string) $get('seo.description'),
                    ]))
                    ->columnSpanFull(),

                MediaField::upload('seo.image')
                    ->label('Share image')
                    ->imageCropAspectRatio('40:21')
                    ->imageResizeMode('cover')
                    ->imageResizeTargetWidth('1200')
                    ->imageResizeTargetHeight('630')
                    ->helperText('Shown when your store link is shared on Facebook or WhatsApp. Best size: 1200 x 630.')
                    ->columnSpanFull(),

                Actions::make([
                    MediaField::picker('seo.image'),
                ])->columnSpanFull(),

                TextInput::make('seo.google_verification')
                    ->label('Google Search Console verification code')
                    ->trim()
                    ->regex('/^[A-Za-z0-9_\-]{10,100}$/')
                    ->helperText('Only the code from the "HTML tag" method, not the whole tag.'),

                TextInput::make('seo.bing_verification')
                    ->label('Bing Webmaster verification code')
                    ->trim()
                    ->regex('/^[A-Za-z0-9_\-]{10,100}$/'),

                TextInput::make('seo_sitemap_url')
                    ->label('Your sitemap address')
                    ->readOnly()
                    ->dehydrated(false)
                    ->copyable()
                    ->afterStateHydrated(fn (TextInput $component) => $component->state(Filament::getTenant()->storefrontUrl() . '/sitemap.xml'))
                    ->helperText('Give this address to Google Search Console after your store is live.')
                    ->columnSpanFull(),
            ]);
    }

    private function trackingTab(): Tab
    {
        return Tab::make('Tracking')
            ->icon('heroicon-o-chart-bar')
            ->columns(2)
            ->schema([
                TextInput::make('facebook_pixel_id')
                    ->label('Facebook (Meta) Pixel ID')
                    ->placeholder('1234567890123456')
                    ->trim()
                    ->regex('/^\d{10,20}$/')
                    ->nullable()
                    ->helperText('Digits only. Find it in Meta Events Manager under Data sources. Leave blank to turn it off.'),

                TextInput::make('ga_measurement_id')
                    ->label('Google Analytics 4 Measurement ID')
                    ->placeholder('G-XXXXXXXXXX')
                    ->trim()
                    ->regex('/^G-[A-Z0-9]{6,12}$/i')
                    ->dehydrateStateUsing(fn (?string $state) => filled($state) ? strtoupper($state) : null)
                    ->nullable()
                    ->helperText('Starts with G-. Find it in Google Analytics under Admin, then Data streams. Leave blank to turn it off.'),
            ]);
    }

    private function sectionsTab(): Tab
    {
        return Tab::make('Promos & FAQ')
            ->icon('heroicon-o-megaphone')
            ->schema([
                Section::make('Promotions')
                    ->description('Banners with a button, for offers and seasons. The first appears above your Featured products, the second between your product sections, and the third below New arrivals.')
                    ->schema([
                        Repeater::make('theme_settings.promos')
                            ->label('Promotions')
                            ->maxItems(3)
                            ->defaultItems(0)
                            ->collapsible()
                            ->reorderable()
                            ->addActionLabel('Add a promotion')
                            ->itemLabel(fn (array $state): ?string => ($state['title'] ?? null) ?: 'New promotion')
                            ->columns(2)
                            ->schema([
                                Toggle::make('enabled')
                                    ->label('Show this promotion')
                                    ->default(true)
                                    ->columnSpanFull(),

                                TextInput::make('title')
                                    ->label('Headline')
                                    ->required()
                                    ->maxLength(70)
                                    ->placeholder('Winter gur is here'),

                                TextInput::make('text')
                                    ->label('Short text')
                                    ->maxLength(140)
                                    ->placeholder('Fresh from Jessore, only this winter'),

                                TextInput::make('button_text')
                                    ->label('Button text')
                                    ->maxLength(24)
                                    ->placeholder('Order now')
                                    ->helperText('Leave empty for no button.'),

                                Select::make('target')
                                    ->label('The button opens')
                                    ->options(fn () => $this->promoTargets())
                                    ->default('all')
                                    ->searchable()
                                    ->native(false),

                                ColorPicker::make('color')
                                    ->label('Accent colour')
                                    ->regex('/^#[0-9a-fA-F]{6}$/')
                                    ->helperText('Used for the headline and the button, or as the whole background of a banner without a picture. Leave empty to use your brand colour.'),

                                Select::make('text_color')
                                    ->label('Text on the colour')
                                    ->options([
                                        'auto' => 'Automatic (recommended)',
                                        'light' => 'Always white',
                                        'dark' => 'Always dark',
                                    ])
                                    ->default('auto')
                                    ->native(false),

                                ColorPicker::make('background')
                                    ->label('Background behind the picture (optional)')
                                    ->regex('/^#[0-9a-fA-F]{6}$/')
                                    ->visible(fn (Get $get) => filled($get('image')))
                                    ->helperText('Pick the colour of your picture\'s own background, so the picture blends into the banner without a visible edge. Leave empty for a soft tint of your accent colour.'),

                                MediaField::upload('image')
                                    ->label('Picture (optional)')
                                    ->imageResizeMode('contain')
                                    ->imageResizeTargetWidth('1600')
                                    ->imageResizeTargetHeight('900')
                                    ->live()
                                    ->helperText('A wide picture, about 1600 x 600, with the product toward the middle or right. The left edge fades into the background, so a plain or light background looks best. Without a picture, the promotion is a slim colour strip.')
                                    ->columnSpanFull(),

                                Actions::make([
                                    MediaField::picker('image'),
                                ])->columnSpanFull(),
                            ]),
                    ]),

                Section::make('Questions and answers')
                    ->description('Answer what customers ask most, so they can order without messaging you. It shows on your home page once you add a question.')
                    ->schema([
                        TextInput::make('theme_settings.faq_title')
                            ->label('Section title')
                            ->maxLength(60)
                            ->placeholder('Questions and answers'),

                        TextInput::make('theme_settings.faq_text')
                            ->label('Short text under the title (optional)')
                            ->maxLength(140)
                            ->placeholder('Everything you need to know before you order'),

                        Repeater::make('theme_settings.faq')
                            ->label('Questions')
                            ->maxItems(10)
                            ->defaultItems(0)
                            ->collapsible()
                            ->reorderable()
                            ->addActionLabel('Add a question')
                            ->itemLabel(fn (array $state): ?string => ($state['question'] ?? null) ?: 'New question')
                            ->schema([
                                TextInput::make('question')
                                    ->label('Question')
                                    ->required()
                                    ->maxLength(120)
                                    ->placeholder('How long does delivery take?'),

                                Textarea::make('answer')
                                    ->label('Answer')
                                    ->required()
                                    ->rows(3)
                                    ->maxLength(600),
                            ])
                            ->helperText('Good ones to answer: how to pay, how long delivery takes, returns and exchanges, and whether your products are original.'),
                    ]),
            ]);
    }

    /** What a promotion button can open: all products, or a category or product of this store. */
    private function promoTargets(): array
    {
        $store = Filament::getTenant();

        $options = ['Pages' => ['all' => 'All products']];

        $categories = $store->categories()->orderBy('name')->pluck('name', 'id')
            ->mapWithKeys(fn ($name, $id) => ['c:' . $id => $name])
            ->all();

        if ($categories) {
            $options['Categories'] = $categories;
        }

        $products = $store->visibleProducts()->latest()->limit(100)->pluck('name', 'id')
            ->mapWithKeys(fn ($name, $id) => ['p:' . $id => $name])
            ->all();

        if ($products) {
            $options['Products'] = $products;
        }

        return $options;
    }

    /** Where clicking a slide can go: any promotion target, or nowhere. */
    private function slideTargets(): array
    {
        $options = $this->promoTargets();
        $options['Pages']['none'] = 'No link';

        return $options;
    }
}