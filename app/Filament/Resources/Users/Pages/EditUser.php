<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['role'] = $this->record->roleIn(Filament::getTenant());
        unset($data['password']);

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $role = $data['role'];
        unset($data['role']);

        $record->update($data); // name, and password only if one was typed

        $record->stores()->updateExistingPivot(Filament::getTenant()->id, ['role' => $role]);

        return $record;
    }
}
