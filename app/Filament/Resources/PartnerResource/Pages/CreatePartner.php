<?php

namespace App\Filament\Resources\PartnerResource\Pages;

use App\Filament\Resources\PartnerResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Permission\Models\Permission;

class CreatePartner extends CreateRecord
{
    protected static string $resource = PartnerResource::class;

    protected function afterSave(): void
    {
        $customPermissions = $this->form->getState()['custom_permissions'] ?? [];
        $permissions = Permission::whereIn('id', $customPermissions)->get();
        $this->record->syncPermissions($permissions);
    }
}
