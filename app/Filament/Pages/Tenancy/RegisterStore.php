<?php

namespace App\Filament\Pages\Tenancy;

use App\Models\Store;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Tenancy\RegisterTenant;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\DB;

class RegisterStore extends RegisterTenant
{
    protected static string $layout = 'filament.auth-layout';
    
    public static function getLabel(): string
    {
        return 'Create your store';
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Store name')
                ->required()
                ->maxLength(100)
                ->live(onBlur: true)
                ->afterStateUpdated(fn (?string $state, Set $set) => $set('slug', Str::slug($state ?? ''))),

            TextInput::make('slug')
                ->label('Store address')
                ->required()
                ->suffix('.' . config('app.central_domain'))
                ->regex('/^[a-z0-9](?:[a-z0-9-]{1,38}[a-z0-9])$/')
                ->unique(table: Store::class, column: 'slug')
                ->rules([
                    fn (): \Closure => function (string $attribute, $value, \Closure $fail) {
                        if (in_array($value, Store::RESERVED_SLUGS, true)) {
                            $fail('This address is reserved. Please choose another.');
                        }
                    },
                ])
                ->helperText('3 to 40 characters: lowercase English letters, numbers and dashes. For a Bangla store name, type the address in English letters.'),
                
            Select::make('business_type')
                ->label('What will you sell?')
                ->options(collect(config('themes.store_types'))->map(fn ($type) => $type['label'])->all())
                ->default('other')
                ->required()
                ->helperText('We will pick a matching look and starter categories. You can change everything later.'),
        ]);
    }

    protected function handleRegistration(array $data): Store
    {
        $type = config('themes.store_types')[$data['business_type']] ?? config('themes.store_types.other');

        return DB::transaction(function () use ($data, $type) {
            $store = Store::create([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'theme' => $type['theme'],
            ]);

            $store->users()->attach(auth()->user(), ['role' => Store::ROLE_OWNER]);

            foreach ($type['categories'] as $position => $name) {
                $store->categories()->create([
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'sort_order' => $position + 1,
                ]);
            }

            return $store;
        });
    }
}