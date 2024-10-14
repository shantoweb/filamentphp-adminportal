<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Events\UserUpdated;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Redis;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $adminCount = Role::findByName('super_admin')->users->count();
        Redis::set('admin_count', $adminCount);
        broadcast(new UserUpdated(Redis::get('admin_count')))->toOthers();
        // dd(Redis::get('admin_count'));
    }
} 
