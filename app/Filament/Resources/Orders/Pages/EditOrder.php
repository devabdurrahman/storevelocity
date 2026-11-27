<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Support\SteadfastActions;
use App\Filament\Support\CustomerCheckActions;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CustomerCheckActions::check(),
            SteadfastActions::book(),
            SteadfastActions::returnRequest(),
            DeleteAction::make(),
        ];
    }
}
