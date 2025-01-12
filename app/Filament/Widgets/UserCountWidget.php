<?php

namespace App\Filament\Widgets;

use App\Models\Partner;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Support\Enums\IconPosition;
use App\Models\User;

class UserCountWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::count())
            ->description('Number of registered users')
            ->descriptionIcon('heroicon-o-users', IconPosition::Before),
        
            Stat::make('Total Admins', User::role('super_admin')->count())
            ->description('Number of registered admins')
            ->descriptionIcon('heroicon-o-users', IconPosition::Before),
        
            Stat::make('Total Partners', Partner::count())
            ->description('Number of registered Partners')
            ->descriptionIcon('heroicon-o-users', IconPosition::Before),
        
        ];
    }
    
    // protected static string $view = 'filament.widgets.user-count-widget-view';

    // protected function getViewData(): array
    // {
    //     return [
    //         'initialAdminCount' => User::role('super_admin')->count(), // Fetch the initial admin count
    //     ];
    // }
}
