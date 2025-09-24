@php
    use Illuminate\Support\Str;

    $formatMoney = static function ($amount, ?string $currency = null): string {
        if ($amount === null) {
            return '—';
        }

        $currency = $currency ?: (config('currencies.default') ?? 'EUR');

        return number_format((float) $amount, 2, '.', ' ') . ' ' . $currency;
    };

    $statusBadge = static function (?string $status): string {
        return match (strtolower((string) $status)) {
            'active', 'approved', 'completed', 'success' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
            'hold', 'on hold', 'pending', 'processing', 'waiting' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-200',
            'blocked', 'rejected', 'declined', 'failed' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-200',
            default => 'bg-slate-100 text-slate-600 dark:bg-slate-800/60 dark:text-slate-200',
        };
    };

    $txIcon = static function (?string $status): string {
        return match (strtolower((string) $status)) {
            'success', 'approved', 'completed' => '✅',
            'blocked', 'failed', 'declined', 'rejected' => '⛔',
            default => '•',
        };
    };

    $transactionsList = $transactions->take(10);
    $currency = $displayCurrency;
    $locationPieces = array_filter([
        $country ?: null,
        $dateOfBirth ? $dateOfBirth->format('d.m.Y') : null,
    ]);
@endphp

<x-filament-panels::page>
    <div class="space-y-6">
        <div class="grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
            <div class="space-y-6">
                <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-100 dark:bg-slate-900 dark:ring-slate-700">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="space-y-2">
                            <div class="text-sm font-medium text-slate-400 dark:text-slate-500">{{ __('Welcome') }}</div>
                            <div class="text-2xl font-semibold text-slate-900 dark:text-white">{{ $user->name }}</div>
                            <div class="flex flex-wrap items-center gap-3 text-sm text-slate-600 dark:text-slate-400">
                                <span>{{ $user->email }}</span>
                                @if($locationPieces)
                                    <span class="hidden sm:inline">•</span>
                                    <span>{{ implode(', ', $locationPieces) }}</span>
                                @endif
                                <span class="hidden sm:inline">•</span>
                                <span>{{ __('Joined') }} {{ optional($user->created_at)->format('d.m.Y') }}</span>
                            </div>
                            <div class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold {{ $statusBadge($user->verification_status) }}">
                                {{ ucfirst($user->verification_status ?? 'pending') }}
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="flex items-center justify-end gap-2 text-sm font-medium text-slate-500 dark:text-slate-400">
                                {{ __('Portfolio balance') }}
                                <x-heroicon-o-wallet class="h-5 w-5 text-amber-500" />
                            </div>
                            <div class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">
                                {{ $formatMoney($totalAccountBalance ?: $mainBalance ?: optional($primaryAccount)->balance, $currency) }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl bg-white shadow-sm ring-1 ring-slate-100 dark:bg-slate-900 dark:ring-slate-700">
                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 dark:border-slate-800">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('Accounts') }}</h2>
                        <a
                            href="{{ route('filament.client.resources.accounts.index') }}"
                            class="text-sm font-medium text-primary-600 transition hover:text-primary-700 dark:text-primary-400"
                        >
                            {{ __('View all') }}
                        </a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100 text-left text-sm dark:divide-slate-800">
                            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-800/80 dark:text-slate-300">
                                <tr>
                                    <th class="px-6 py-3">{{ __('Account') }}</th>
                                    <th class="px-6 py-3">{{ __('Bank / Number') }}</th>
                                    <th class="px-6 py-3">{{ __('Type') }}</th>
                                    <th class="px-6 py-3">{{ __('Expiry date') }}</th>
                                    <th class="px-6 py-3">{{ __('Balance') }}</th>
                                    <th class="px-6 py-3">{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @forelse($accounts as $account)
                                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40">
                                        <td class="px-6 py-4 align-top">
                                            <div class="font-medium text-slate-900 dark:text-white">{{ $account->organization ?? '—' }}</div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400">{{ $account->broker_initials ?? '—' }}</div>
                                        </td>
                                        <td class="px-6 py-4 align-top">
                                            <div class="font-medium text-slate-900 dark:text-white">{{ $account->bank ?? '—' }}</div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400">{{ $account->number }}</div>
                                        </td>
                                        <td class="px-6 py-4 align-top text-slate-700 dark:text-slate-300">{{ $account->type }}</td>
                                        <td class="px-6 py-4 align-top text-slate-700 dark:text-slate-300">{{ optional($account->term)->format('d.m.Y') ?? '—' }}</td>
                                        <td class="px-6 py-4 align-top font-semibold text-slate-900 dark:text-white">
                                            {{ $formatMoney($account->balance, $account->currency ?? $currency) }}
                                        </td>
                                        <td class="px-6 py-4 align-top">
                                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusBadge($account->status) }}">
                                                {{ strtoupper($account->status ?? '—') }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                            {{ __('No accounts yet') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-100 dark:bg-slate-900 dark:ring-slate-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-semibold text-slate-900 dark:text-white">{{ __('Recent transactions') }}</h3>
                        <a
                            href="{{ route('filament.client.resources.transactions.index') }}"
                            class="text-xs font-semibold text-primary-600 transition hover:text-primary-700 dark:text-primary-400"
                        >
                            {{ __('View all') }}
                        </a>
                    </div>
                    <div class="mt-4 space-y-4">
                        @forelse($transactionsList as $transaction)
                            <div class="rounded-2xl border border-slate-100 p-4 dark:border-slate-800">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="space-y-1">
                                        <div class="text-sm font-medium text-slate-900 dark:text-white">
                                            {{ $transaction->type ?? __('Transaction') }}
                                        </div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">
                                            {{ optional($transaction->created_at)->format('d.m.Y H:i:s') ?? '—' }}
                                        </div>
                                        <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                                            <span>{{ $transaction->from }}</span>
                                            <span>→</span>
                                            <span>{{ $transaction->to }}</span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm font-semibold text-slate-900 dark:text-white">
                                            {{ $formatMoney($transaction->amount, $transaction->currency ?? $currency) }}
                                        </div>
                                        <div class="mt-1 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold {{ $statusBadge($transaction->status) }}">
                                            <span>{{ $txIcon($transaction->status) }}</span>
                                            <span>{{ Str::title($transaction->status ?? 'pending') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-200 px-4 py-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
                                {{ __('No transactions yet') }}
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-3xl bg-gradient-to-br from-primary-500 via-primary-600 to-primary-700 p-6 text-white shadow-lg">
                    <div class="text-lg font-semibold">{{ __('Need help?') }}</div>
                    <p class="mt-2 text-sm text-primary-100/90">
                        {{ __('Open a support request or initiate a withdrawal directly from the menu.') }}
                    </p>
                    <div class="mt-4 flex items-center gap-3">
                        <a
                            href="{{ route('filament.client.pages.support-chat') }}"
                            class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-sm font-medium text-white transition hover:bg-white/20"
                        >
                            <x-heroicon-o-chat-bubble-left-right class="h-4 w-4" />
                            {{ __('Contact support') }}
                        </a>
                        <a
                            href="{{ route('filament.client.resources.withdrawals.create') }}"
                            class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-semibold text-primary-600 transition hover:bg-primary-50"
                        >
                            <x-heroicon-o-banknotes class="h-4 w-4" />
                            {{ __('Withdraw funds') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
