<?php
namespace App\Filament\Client\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class ClientStatsOverview extends BaseWidget
{
    public static function canView(): bool
    {
        // Always show for authenticated client users; middleware governs access
        return Auth::check() && !Auth::user()->is_admin;
    }

    protected function getStats(): array
    {
        $user = Auth::user();

        // User name from account record; show as-is in the widget
        $name = trim((string) ($user->name ?? ''));

        // Sum of all user's account balances in user's currency
        $total = 0.0;
        if (method_exists($user, 'accounts')) {
            $total = (float) $user->accounts()->sum('balance');
        }
        $currency = $user->currency ?: (config('currencies.default') ?? 'EUR');

        // Cabinet status derived from verification status / enablement
        $isEnabled = method_exists($user, 'isFullyEnabled') ? $user->isFullyEnabled() : false;
        $statusRaw = (string) ($user->verification_status ?? 'pending');
        $statusText = $isEnabled
            ? 'Active'
            : (match (strtolower($statusRaw)) {
                'blocked' => 'Blocked',
                'pending' => 'Pending',
                'rejected' => 'Rejected',
                default => ucfirst($statusRaw),
            });

        return [
            Stat::make('Name', $name !== '' ? $name : '—')
                ->color('info'),

            Stat::make('Total Funds', number_format($total, 2) . ' ' . $currency)
                ->color('success'),

            Stat::make('Status', $statusText)
                ->color($isEnabled ? 'success' : 'warning'),
        ];
    }
}
