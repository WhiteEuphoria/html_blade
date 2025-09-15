<?php

namespace App\Filament\Client\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Client\Widgets\BrokerageAccountWidget;
use App\Filament\Client\Widgets\ClientStatsOverview;
use App\Filament\Client\Widgets\TransitAccountWidget;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BaseDashboard
{
    public function mount(): void
    {
        // Keep verification flow as configured by middleware; no extra redirects here
    }

    public function getWidgets(): array
    {
        // Minimal dashboard: initials, total funds, cabinet status
        return [
            \App\Filament\Client\Widgets\ClientStatsOverview::class,
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        // Dashboard is available to all client users; middleware controls access
        return Auth::check() && !Auth::user()->is_admin;
    }
}
