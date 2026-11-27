<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Facades\Filament;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use App\Filament\Support\MediaField;
use Filament\Schemas\Components\Actions;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Get;
use App\Support\SeoCheck;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(80)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (?string $state, Set $set) => $set('slug', Str::slug($state ?? ''))),

                TextInput::make('slug')
                    ->required()
                    ->maxLength(80)
                    ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                    ->helperText('English lowercase letters, numbers and dashes only. Used in the web address.')
                    ->unique(
                        ignoreRecord: true,
                        modifyRuleUsing: fn (Unique $rule) => $rule->where('store_id', Filament::getTenant()->id),
                    ),

                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->helperText('Lower numbers appear first.'),

                Toggle::make('is_featured')
                    ->label('Show on home page')
                    ->helperText('Featured categories appear as tiles on your home page (up to six).'),

                // FileUpload::make('image')
                //     ->label('Tile picture')
                //     ->image()
                //     ->disk('public')
                //     ->visibility('public')
                //     ->directory(fn () => 'stores/' . Filament::getTenant()->id . '/categories')
                //     ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                //     ->maxSize(2048)
                //     ->imageCropAspectRatio('1:1')
                //     ->imageResizeTargetWidth('800')
                //     ->imageResizeTargetHeight('800')
                //     ->imageResizeMode('cover')
                //     ->helperText('Optional. If you leave it empty, we use a photo of one of its products.'),
                MediaField::upload('image')
                    ->label('Tile picture')
                    ->imageCropAspectRatio('1:1')
                    ->imageResizeTargetWidth('800')
                    ->imageResizeTargetHeight('800')
                    ->imageResizeMode('cover')
                    ->helperText('Optional. If you leave it empty, we use a photo of one of its products.'),

                Actions::make([
                    MediaField::picker('image'),
                ]),

                TextInput::make('seo_keyword')
                    ->label('Main keyword (optional)')
                    ->maxLength(60)
                    ->live(onBlur: true)
                    ->helperText(fn (Get $get) => SeoCheck::summary($get('seo_keyword'), [
                        'In the category name' => (string) $get('name'),
                        'In the page title' => trim((string) $get('seo_title')) ?: (string) $get('name'),
                        'In the page description' => trim((string) $get('seo_description')) ?: 'Shop ' . $get('name'),
                    ])),

                TextInput::make('seo_title')
                    ->label('SEO title (optional)')
                    ->maxLength(70)
                    ->live(onBlur: true)
                    ->hint(fn (Get $get) => mb_strlen((string) $get('seo_title')) . ' / 60 recommended'),

                Textarea::make('seo_description')
                    ->label('SEO description (optional)')
                    ->rows(2)
                    ->maxLength(170)
                    ->live(onBlur: true)
                    ->hint(fn (Get $get) => mb_strlen((string) $get('seo_description')) . ' / 160 recommended'),
            ]);
    }
}
