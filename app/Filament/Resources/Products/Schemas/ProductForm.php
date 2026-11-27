<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Filament\Support\MediaField;
use Filament\Facades\Filament;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TagsInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;
use App\Support\SeoCheck;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Basic information')
                ->columnSpanFull()
                ->schema([
                    TextInput::make('name')
                        ->label('Product name')
                        ->required()
                        ->maxLength(150)
                        ->live(onBlur: true),

                    Select::make('category_id')
                        ->label('Category')
                        ->relationship(
                            name: 'category',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn ($query) => $query->where('store_id', Filament::getTenant()->id),
                        )
                        ->searchable()
                        ->preload()
                        ->nullable(),

                    Textarea::make('short_description')
                        ->label('Short description')
                        ->rows(2)
                        ->maxLength(300)
                        ->live(onBlur: true)
                        ->helperText('One or two sentences, shown right under the product name and in search results.'),

                    MarkdownEditor::make('description')
                        ->label('Full description')
                        ->toolbarButtons(['bold', 'italic', 'link', 'bulletList', 'orderedList', 'undo', 'redo'])
                        ->maxLength(5000)
                        ->live(onBlur: true)
                        ->helperText('Use the buttons for bold text and bullet lists, for example sizes, ingredients or what is in the box.'),
                ]),

            Section::make('Price')
                ->columnSpanFull()
                ->columns(3)
                ->schema([
                    TextInput::make('price')
                        ->label('Selling price')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->prefix('৳'),

                    TextInput::make('compare_price')
                        ->label('Old price (optional)')
                        ->numeric()
                        ->minValue(0)
                        ->prefix('৳')
                        ->gt('price')
                        ->helperText('Shown crossed out next to the selling price, with a discount badge.'),

                    TextInput::make('unit')
                        ->label('Sold by (optional)')
                        ->maxLength(30)
                        ->placeholder('per kg')
                        ->helperText('For example: per kg, per dozen, 500 g.'),
                ]),

            Section::make('Stock')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    TextInput::make('stock')
                        ->label('Quantity in stock')
                        ->required()
                        ->numeric()
                        ->integer()
                        ->minValue(0)
                        ->default(0)
                        ->visible(fn (Get $get) => ! $get('has_variants'))
                        ->helperText('Products with variations track stock on each variation instead.'),

                    TextInput::make('sku')
                        ->label('SKU (optional)')
                        ->maxLength(60)
                        ->unique(
                            ignoreRecord: true,
                            modifyRuleUsing: fn (Unique $rule) => $rule->where('store_id', Filament::getTenant()->id),
                        )
                        ->helperText('Your own product code. Must be different for each product.'),
                ]),

            Section::make('Variations')
                ->columnSpanFull()
                ->description('For products that come in different sizes, colours, weights and so on. Each variation has its own stock.')
                ->schema([
                    Toggle::make('has_variants')
                        ->label('This product has variations')
                        ->live()
                        ->default(false),

                    TextInput::make('option1_name')
                        ->label('First option name')
                        ->placeholder('Size')
                        ->maxLength(30)
                        ->required(fn (Get $get) => (bool) $get('has_variants'))
                        ->visible(fn (Get $get) => (bool) $get('has_variants'))
                        ->live(onBlur: true),

                    TextInput::make('option2_name')
                        ->label('Second option name (optional)')
                        ->placeholder('Colour')
                        ->maxLength(30)
                        ->visible(fn (Get $get) => (bool) $get('has_variants'))
                        ->live(onBlur: true),

                    Actions::make([
                        Action::make('generateVariants')
                            ->label('Generate variations from lists')
                            ->icon('heroicon-o-sparkles')
                            ->color('gray')
                            ->modalHeading('Generate variations')
                            ->modalDescription('Type each value and press Enter. We create every combination. Existing ones are kept.')
                            ->schema([
                                TagsInput::make('values1')
                                    ->label('First option values')
                                    ->placeholder('For example S, M, L')
                                    ->required()
                                    ->nestedRecursiveRules(['max:60']),

                                TagsInput::make('values2')
                                    ->label('Second option values (optional)')
                                    ->placeholder('For example Red, Blue')
                                    ->nestedRecursiveRules(['max:60']),
                            ])
                            ->action(function (array $data, Get $get, Set $set) {
                                $first = collect($data['values1'] ?? [])->map(fn ($v) => trim((string) $v))->filter()->unique()->values();
                                $second = collect($data['values2'] ?? [])->map(fn ($v) => trim((string) $v))->filter()->unique()->values();

                                if ($second->isEmpty()) {
                                    $second = collect([null]);
                                }

                                if ($first->count() * $second->count() > 60) {
                                    Notification::make()->danger()->title('That makes more than 60 variations. Please use fewer values.')->send();

                                    return;
                                }

                                $current = (array) ($get('variants') ?? []);

                                $existing = collect($current)
                                    ->map(fn ($item) => mb_strtolower(trim((string) ($item['option1'] ?? '')) . '|' . trim((string) ($item['option2'] ?? ''))))
                                    ->all();

                                foreach ($first as $a) {
                                    foreach ($second as $b) {
                                        if (in_array(mb_strtolower($a . '|' . ($b ?? '')), $existing, true)) {
                                            continue;
                                        }

                                        $current[(string) Str::uuid()] = [
                                            'option1' => $a,
                                            'option2' => $b,
                                            'sku' => null,
                                            'price' => null,
                                            'stock' => 0,
                                            'is_active' => true,
                                        ];
                                    }
                                }

                                $set('variants', $current);

                                Notification::make()->success()->title('Variations added. Set the stock, then save.')->send();
                            }),
                    ])->visible(fn (Get $get) => (bool) $get('has_variants') && filled($get('option1_name'))),

                    Repeater::make('variants')
                        ->relationship('variants')
                        ->label('Variations')
                        ->visible(fn (Get $get) => (bool) $get('has_variants'))
                        ->minItems(fn (Get $get) => $get('has_variants') ? 1 : 0)
                        ->maxItems(60)
                        ->orderColumn('sort_order')
                        ->collapsible()
                        ->addActionLabel('Add a variation')
                        ->itemLabel(fn (array $state): ?string => collect([$state['option1'] ?? null, $state['option2'] ?? null])
                            ->filter()->implode(' / ') ?: 'New variation')
                        ->columns(['default' => 2, 'lg' => 5])
                        ->rules([
                            fn () => function (string $attribute, $value, \Closure $fail) {
                                $combos = collect($value)
                                    ->map(fn ($item) => mb_strtolower(trim((string) ($item['option1'] ?? '')) . '|' . trim((string) ($item['option2'] ?? ''))))
                                    ->all();

                                if (count($combos) !== count(array_unique($combos))) {
                                    $fail('Each variation must be a different combination of values.');
                                }
                            },
                        ])
                        ->schema([
                            TextInput::make('option1')
                                ->label(fn (Get $get) => $get('../../option1_name') ?: 'First option')
                                ->required()
                                ->maxLength(60),

                            TextInput::make('option2')
                                ->label(fn (Get $get) => $get('../../option2_name') ?: 'Second option')
                                ->maxLength(60)
                                ->visible(fn (Get $get) => filled($get('../../option2_name')))
                                ->required(fn (Get $get) => filled($get('../../option2_name'))),

                            TextInput::make('stock')
                                ->label('Stock')
                                ->required()
                                ->numeric()
                                ->integer()
                                ->minValue(0)
                                ->default(0),

                            TextInput::make('price')
                                ->label('Price (optional)')
                                ->numeric()
                                ->minValue(0)
                                ->prefix('৳')
                                ->helperText('Empty means the selling price above.'),

                            TextInput::make('sku')
                                ->label('SKU (optional)')
                                ->maxLength(60),

                            Toggle::make('is_active')
                                ->label('Available')
                                ->default(true),
                        ]),
                ]),

            Section::make('Photos')
                ->columnSpanFull()
                ->schema([
                    MediaField::upload('images', multiple: true)
                        ->label('Product images')
                        ->maxFiles(5)
                        ->reorderable()
                        ->imageResizeMode('contain')
                        ->imageResizeTargetWidth('1200')
                        ->imageResizeTargetHeight('1200')
                        ->helperText('The first photo is the main one. Drag to reorder.'),

                    Actions::make([
                        MediaField::picker('images', multiple: true, max: 5),
                    ]),
                ]),

            Section::make('Visibility')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    Toggle::make('is_active')
                        ->label('Visible in store')
                        ->default(true)
                        ->helperText('Turn off to hide this product from customers while you prepare it.'),

                    Toggle::make('is_featured')
                        ->label('Feature on home page')
                        ->helperText('Shows this product in the Featured section of your home page.'),
                ]),

            Section::make('Search engines (SEO)')
                ->columnSpanFull()
                ->collapsed()
                ->description('Leave these empty and we write them from your product name and description.')
                ->schema([
                    TextInput::make('seo_keyword')
                        ->label('Main keyword (optional)')
                        ->maxLength(60)
                        ->placeholder('cotton panjabi')
                        ->live(onBlur: true)
                        ->helperText(function (Get $get) {
                            $name = (string) $get('name');
                            $keywordSlug = trim((string) Str::slug((string) $get('seo_keyword'), '-', 'bn'));

                            return SeoCheck::summary($get('seo_keyword'), [
                                'In the product name' => $name,
                                'In the page title' => trim((string) $get('seo_title')) ?: $name,
                                'In the page description' => trim((string) $get('seo_description')) ?: ((string) $get('short_description') ?: (string) $get('description')),
                                'In the full description' => (string) $get('description'),
                                'In the web address' => $keywordSlug !== '' && str_contains((string) Str::slug($name, '-', 'bn'), $keywordSlug),
                            ]);
                        }),

                    TextInput::make('seo_title')
                        ->label('Page title')
                        ->maxLength(70)
                        ->live(onBlur: true)
                        ->hint(fn (Get $get) => mb_strlen((string) $get('seo_title')) . ' / 60 recommended')
                        ->helperText('The blue headline on Google. Include the product name.'),

                    Textarea::make('seo_description')
                        ->label('Page description')
                        ->rows(2)
                        ->maxLength(170)
                        ->live(onBlur: true)
                        ->hint(fn (Get $get) => mb_strlen((string) $get('seo_description')) . ' / 160 recommended')
                        ->helperText('The text under the headline. Say what the product is and why someone should buy it.'),
                ]),

            Section::make('Search in your store')
                ->columnSpanFull()
                ->collapsed()
                ->schema([
                    TextInput::make('search_words')
                        ->label('Other words customers might type (optional)')
                        ->maxLength(300)
                        ->placeholder('panjabi, punjabi, পাঞ্জাবি, kurta')
                        ->helperText('Different spellings, Bangla and English names. They help customers find this product with your store\'s search box. They are never shown on the page.'),
                ]),
        ]);
    }
}