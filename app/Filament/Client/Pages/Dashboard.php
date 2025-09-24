<?php

namespace App\Filament\Client\Pages;

use App\Services\ClientDashboardService;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BaseDashboard
{
    protected static string $view = 'filament.client.pages.dashboard';

    public function mount(): void
    {
        // Keep verification flow as configured by middleware; no extra redirects here
    }

    public function getWidgets(): array
    {
        // Minimal dashboard: initials, total funds, cabinet status
        return [];
    }

    public static function shouldRegisterNavigation(): bool
    {
        // Dashboard is available to all client users; middleware controls access
        return Auth::check() && !Auth::user()->is_admin;
    }

    protected function getViewData(): array
    {
        $user = Auth::user();

        abort_if(! $user, 403);
        abort_if($user->is_admin, 403);

        return App::make(ClientDashboardService::class)->build($user);
    }
}
