<?php

namespace App\Filament\Resources\Media;

use App\Filament\Resources\Media\Pages\ManageMedia;
use App\Models\Media;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Stack;
use Illuminate\Support\Number;

class MediaResource extends Resource
{
    protected static ?string $model = Media::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Name')->required()->maxLength(100),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Stack::make([
                    ImageColumn::make('path')
                        ->disk('public')
                        ->imageHeight(170)
                        ->extraImgAttributes(['style' => 'width:100%;height:170px;object-fit:cover;border-radius:8px;']),

                    TextColumn::make('name')->searchable()->limit(30),

                    TextColumn::make('size')
                        ->formatStateUsing(fn ($state) => Number::fileSize((int) $state, precision: 1))
                        ->color('gray'),

                    TextColumn::make('usage')
                        ->badge()
                        ->state(fn (Media $record) => count($record->usages()) ? 'In use (' . count($record->usages()) . ')' : 'Not used')
                        ->color(fn (string $state) => str_starts_with($state, 'In use') ? 'success' : 'gray'),
                ])->space(2),
            ])
            ->contentGrid(['default' => 2, 'md' => 3, 'xl' => 4])
            ->defaultSort('created_at', 'desc')
            ->paginated([12, 24, 48])
            ->recordActions([
                EditAction::make()->label('Rename'),

                DeleteAction::make()
                    ->before(function (DeleteAction $action, Media $record) {
                        $uses = $record->usages();

                        if ($uses) {
                            Notification::make()
                                ->danger()
                                ->title('This image is still in use')
                                ->body('Remove it from: ' . implode(', ', array_slice($uses, 0, 5)))
                                ->send();

                            $action->cancel();
                        }
                    }),
            ])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageMedia::route('/'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return 'Media library';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Catalog';
    }

    public static function getNavigationSort(): ?int
    {
        return 3;
    }
}
