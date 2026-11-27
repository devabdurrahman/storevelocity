<?php

namespace App\Filament\Resources\Media\Pages;

use App\Filament\Resources\Media\MediaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use App\Filament\Support\MediaField;
use App\Support\MediaLibrary;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Support\Number;

class ManageMedia extends ManageRecords
{
    protected static string $resource = MediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('upload')
                ->label('Upload images')
                ->icon('heroicon-o-arrow-up-tray')
                ->modalHeading('Upload images')
                ->modalSubmitActionLabel('Done')
                ->schema([
                    MediaField::upload('files', multiple: true)
                        ->label('Images')
                        ->required()
                        ->maxFiles(10),
                ])
                // The files are already saved into the library while uploading
                ->action(fn () => Notification::make()->success()->title('Images uploaded')->send()),
        ];
    }

    public function getSubheading(): ?string
    {
        $store = Filament::getTenant();
        $used = app(MediaLibrary::class)->usedBytes($store);

        return Number::fileSize($used, precision: 1) . ' used across ' . $store->media()->count() . ' images';
    }
}
