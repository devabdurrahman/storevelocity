<?php

namespace App\Filament\Resources\BlockedPhones\Pages;

use App\Filament\Resources\BlockedPhones\BlockedPhoneResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBlockedPhone extends EditRecord
{
    protected static string $resource = BlockedPhoneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
