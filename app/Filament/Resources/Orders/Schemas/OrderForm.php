<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use App\Models\Order;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer Information')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('customer_name')
                            ->required(),
                        TextInput::make('customer_phone')
                            ->tel()
                            ->required(),
                        Textarea::make('customer_address')
                            ->required()
                            ->columnSpanFull(),
                        Textarea::make('customer_note')
                            ->columnSpanFull(),
                    ]),

                Section::make('Steadfast Customer Delivery History')
                    ->description('Customer delivery performance and return risk across all merchants on Steadfast.')
                    ->icon('heroicon-o-shield-check')
                    ->columnSpanFull()
                    ->visible(fn (?Order $record) => $record !== null)
                    ->schema([
                        ViewField::make('steadfast_customer_profile')
                            ->hiddenLabel()
                            ->columnSpanFull()
                            ->view('filament.components.steadfast-customer-history'),
                    ]),

                Section::make('Order Details')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('number')
                            ->label('Order number')
                            ->disabled()
                            ->dehydrated(false),
                        Select::make('status')
                            ->options(Order::STATUSES)
                            ->required()
                            ->default('pending')
                            ->disabled(fn (?Order $record) => $record?->status === 'cancelled'),
                        TextInput::make('payment_method')
                            ->required()
                            ->default('cod'),
                        TextInput::make('subtotal')
                            ->numeric()
                            ->prefix('৳')
                            ->required(),
                        TextInput::make('shipping_fee')
                            ->numeric()
                            ->prefix('৳')
                            ->required(),
                        TextInput::make('total')
                            ->numeric()
                            ->prefix('৳')
                            ->required(),
                    ]),

                Section::make('Courier (Steadfast)')
                    ->columnSpanFull()
                    ->columns(2)
                    ->visible(fn (?Order $record) => filled($record?->consignment_id))
                    ->schema([
                        TextInput::make('courier_status')->label('Status')->disabled()->dehydrated(false),
                        TextInput::make('tracking_code')->label('Tracking code')->disabled()->dehydrated(false)->copyable(),
                        TextInput::make('consignment_id')->label('Consignment ID')->disabled()->dehydrated(false),
                        TextInput::make('courier_message')->label('Latest update')->disabled()->dehydrated(false),
                    ]),
            ]);
    }
}
