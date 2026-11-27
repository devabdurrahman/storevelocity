<?php

namespace App\Filament\Resources\BlockedPhones\Pages;

use App\Filament\Resources\BlockedPhones\BlockedPhoneResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBlockedPhone extends CreateRecord
{
    protected static string $resource = BlockedPhoneResource::class;
}
