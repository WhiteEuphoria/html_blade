@php
    $formatMoney = fn ($amount, $currency) => $amount === null
        ? '—'
        : number_format((float) $amount, 2, '.', ' ') . ' ' . ($currency ?: (config('currencies.default') ?? 'EUR'));

    $verificationColor = fn (?string $status) => match (strtolower((string) $status)) {
        'approved', 'active', 'verified', 'verificated' => 'text-emerald-600 bg-emerald-100',
        'rejected', 'blocked' => 'text-rose-600 bg-rose-100',
        default => 'text-amber-600 bg-amber-100',
    };

    $statusBadge = static function (?string $status): string {
        $status = (string) $status;
        return match (strtolower($status)) {
            'active' => 'bg-emerald-100 text-emerald-700',
            'hold', 'on hold' => 'bg-amber-100 text-amber-700',
            'blocked', 'blocked by client', 'blocked by admin' => 'bg-rose-100 text-rose-700',
            default => 'bg-slate-100 text-slate-600',
        };
    };

    $txStatus = static function (?string $status): array {
        return match (strtolower((string) $status)) {
            'success', 'completed' => ['✅', 'text-emerald-600'],
            'failed', 'declined', 'rejected' => ['⛔', 'text-rose-600'],
            default => ['•', 'text-slate-500'],
        };
    };
@endphp

<x-filament-panels::page>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="flex flex-col gap-3">
                <div class="flex items-center gap-3 text-slate-500">
                    <img src="{{ asset('personal-acc/img/logo.svg') }}" alt="Brand" class="h-10 hidden sm:block">
                    <div>
                        <div class="text-xs uppercase tracking-wider">Admin panel</div>
                        <h1 class="text-2xl font-semibold text-slate-800">Unified client dashboard</h1>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <label class="text-sm font-medium text-slate-600" for="dashboard-client">Client</label>
                    @if($clientOptions)
                        <select
                            wire:model.live="selectedUserId"
                            id="dashboard-client"
                            class="fi-input block rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/50"
                        >
                            @foreach($clientOptions as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    @else
                        <div class="text-sm text-slate-500">No clients yet</div>
                    @endif
                    @if($selectedUser)
                        <a
                            href="{{ route('filament.admin.resources.users.edit', $selectedUser) }}"
                            class="text-sm font-medium text-primary-600 transition hover:text-primary-700"
                        >
                            Manage profile
                        </a>
                    @endif
                </div>
            </div>

            @php
                $supportAction = $this->getAction('support');
                $withdrawalAction = $this->getAction('withdrawal');
            @endphp

            <div class="flex flex-wrap items-center gap-3">
                @if($supportAction)
                    <x-filament-actions::action :action="$supportAction" class="fi-btn fi-btn--light" />
                @endif
                @if($withdrawalAction)
                    <x-filament-actions::action :action="$withdrawalAction" class="fi-btn" />
                @endif
            </div>
        </div>

        @if(!$selectedUser)
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center text-slate-500">
                <div class="text-lg font-semibold text-slate-700">Select a client to see their dashboard</div>
                <p class="mt-2 text-sm">Create a client account first or choose one from the list above.</p>
            </div>
        @else
            <div class="grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
                <div class="space-y-6">
                    <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                            <div class="space-y-2">
                                <div class="text-sm font-medium text-slate-400">Welcome</div>
                                <div class="text-2xl font-semibold text-slate-900">{{ $selectedUser->name }}</div>
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-slate-600">
                                    <span>{{ $selectedUser->email }}</span>
                                    <span class="hidden sm:inline">•</span>
                                    <span>Joined {{ optional($selectedUser->created_at)->format('d.m.Y') }}</span>
                                </div>
                                <div class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold {{ $verificationColor($selectedUser->verification_status) }}">
                                    {{ ucfirst($selectedUser->verification_status ?? 'pending') }}
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="flex items-center justify-end gap-2 text-sm font-medium text-slate-500">
                                    Balance
                                    <img src="{{ asset('personal-acc/img/icons/wallet.svg') }}" alt="Wallet" class="h-5">
                                </div>
                                <div class="mt-2 text-3xl font-semibold text-slate-900">
                                    {{ $formatMoney($selectedUser->main_balance, $selectedUser->currency) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3xl bg-white shadow-sm ring-1 ring-slate-100">
                        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                            <h2 class="text-lg font-semibold text-slate-900">Accounts</h2>
                            <a
                                href="{{ route('filament.admin.resources.accounts.index', ['table' => ['filters' => ['user_id' => ['value' => $selectedUser->getKey()]]]]) }}"
                                class="text-sm font-medium text-primary-600 transition hover:text-primary-700"
                            >
                                View all
                            </a>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    <tr>
                                        <th class="px-6 py-3">Company / Broker</th>
                                        <th class="px-6 py-3">Bank / Account no.</th>
                                        <th class="px-6 py-3">Owner</th>
                                        <th class="px-6 py-3">Type</th>
                                        <th class="px-6 py-3">Expiry date</th>
                                        <th class="px-6 py-3">Balance</th>
                                        <th class="px-6 py-3">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse($accounts as $account)
                                        <tr class="hover:bg-slate-50/60">
                                            <td class="px-6 py-4 align-top">
                                                <div class="font-medium text-slate-800">{{ $account->organization ?? '—' }}</div>
                                                <div class="text-xs text-slate-500">{{ $account->broker_initials ?? '—' }}</div>
                                            </td>
                                            <td class="px-6 py-4 align-top">
                                                <div class="font-medium text-slate-800">{{ $account->bank ?? '—' }}</div>
                                                <div class="text-xs text-slate-500">{{ $account->number }}</div>
                                            </td>
                                            <td class="px-6 py-4 align-top">
                                                <div class="font-medium text-slate-800">{{ $selectedUser->name }}</div>
                                                <div class="text-xs text-slate-500">{{ $account->client_initials ?? 'Client' }}</div>
                                            </td>
                                            <td class="px-6 py-4 align-top text-slate-700">{{ $account->type }}</td>
                                            <td class="px-6 py-4 align-top text-slate-700">{{ optional($account->term)->format('d/m/y') ?? '—' }}</td>
                                            <td class="px-6 py-4 align-top font-semibold text-slate-900">
                                                {{ $formatMoney($account->balance, $account->currency ?? $selectedUser->currency) }}
                                            </td>
                                            <td class="px-6 py-4 align-top">
                                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusBadge($account->status) }}">
                                                    {{ strtoupper($account->status ?? '—') }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-6 py-8 text-center text-sm text-slate-500">No accounts yet</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                            <div class="flex items-center justify-between">
                                <h3 class="text-base font-semibold text-slate-900">Documents</h3>
                                <a
                                    href="{{ route('filament.admin.resources.documents.index', ['table' => ['filters' => ['user_id' => ['value' => $selectedUser->getKey()]]]]) }}"
                                    class="text-xs font-semibold text-primary-600"
                                >View</a>
                            </div>
                            <ul class="mt-4 space-y-3 text-sm">
                                @forelse($documents as $document)
                                    <li class="flex items-center justify-between rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                                        <div class="flex flex-col">
                                            <span class="font-medium text-slate-800">{{ $document->original_name }}</span>
                                            <span class="text-xs text-slate-500">{{ optional($document->created_at)->format('d.m.Y H:i') }}</span>
                                        </div>
                                        <span class="text-xs font-semibold {{ $statusBadge($document->status) }} rounded-full px-3 py-1">
                                            {{ strtoupper($document->status ?? 'PENDING') }}
                                        </span>
                                    </li>
                                @empty
                                    <li class="rounded-2xl border border-dashed border-slate-200 px-4 py-6 text-center text-slate-500">No documents uploaded</li>
                                @endforelse
                            </ul>
                        </div>

                        <div class="space-y-4">
                            <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-base font-semibold text-slate-900">Fraud claims</h3>
                                    <a
                                        href="{{ route('filament.admin.resources.fraud-claims.index', ['table' => ['filters' => ['user_id' => ['value' => $selectedUser->getKey()]]]]) }}"
                                        class="text-xs font-semibold text-primary-600"
                                    >View</a>
                                </div>
                                <ul class="mt-4 space-y-3 text-sm">
                                    @forelse($fraudClaims as $claim)
                                        <li class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                                            <div class="text-sm font-medium text-slate-800">{{ \Illuminate\Support\Str::limit($claim->details, 80) }}</div>
                                            <div class="mt-2 flex items-center justify-between text-xs text-slate-500">
                                                <span>{{ optional($claim->created_at)->format('d.m.Y H:i') }}</span>
                                                <span class="inline-flex rounded-full px-3 py-1 font-semibold {{ $statusBadge($claim->status) }}">
                                                    {{ strtoupper($claim->status ?? 'В РАССМОТРЕНИИ') }}
                                                </span>
                                            </div>
                                        </li>
                                    @empty
                                        <li class="rounded-2xl border border-dashed border-slate-200 px-4 py-6 text-center text-slate-500">No claims</li>
                                    @endforelse
                                </ul>
                            </div>

                            <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-base font-semibold text-slate-900">Withdrawals</h3>
                                    <a
                                        href="{{ route('filament.admin.resources.withdrawals.index', ['table' => ['filters' => ['user_id' => ['value' => $selectedUser->getKey()]]]]) }}"
                                        class="text-xs font-semibold text-primary-600"
                                    >View</a>
                                </div>
                                <ul class="mt-4 space-y-3 text-sm">
                                    @forelse($withdrawals as $withdrawal)
                                        <li class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                                            <div class="flex items-center justify-between">
                                                <span class="font-medium text-slate-800">{{ $formatMoney($withdrawal->amount, optional($withdrawal->user)->currency) }}</span>
                                                <span class="text-xs font-semibold {{ $statusBadge($withdrawal->status) }} rounded-full px-3 py-1">
                                                    {{ strtoupper($withdrawal->status) }}
                                                </span>
                                            </div>
                                            <div class="mt-2 flex items-center justify-between text-xs text-slate-500">
                                                <span>{{ optional($withdrawal->created_at)->format('d.m.Y H:i') }}</span>
                                                <span>{{ ucfirst($withdrawal->method ?? '—') }}</span>
                                            </div>
                                        </li>
                                    @empty
                                        <li class="rounded-2xl border border-dashed border-slate-200 px-4 py-6 text-center text-slate-500">No withdrawals</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-3xl bg-white shadow-sm ring-1 ring-slate-100">
                        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                            <h2 class="text-lg font-semibold text-slate-900">Transactions</h2>
                            <a
                                href="{{ route('filament.admin.resources.transactions.index', ['table' => ['filters' => ['user_id' => ['value' => $selectedUser->getKey()]]]]) }}"
                                class="text-sm font-medium text-primary-600 transition hover:text-primary-700"
                            >
                                View all
                            </a>
                        </div>
                        <div class="space-y-3 p-4">
                            @forelse($transactions as $transaction)
                                @php([$icon, $iconColor] = $txStatus($transaction->status))
                                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex items-center gap-3">
                                            <span class="text-xl {{ $iconColor }}">{{ $icon }}</span>
                                            <div>
                                                <div class="text-sm font-semibold text-slate-800">{{ ucfirst($transaction->type ?? 'Transaction') }}</div>
                                                <div class="text-xs text-slate-500">{{ $transaction->from }} → {{ $transaction->to }}</div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-xs text-slate-400">{{ optional($transaction->created_at)->format('d.m.Y H:i') }}</div>
                                            <div class="mt-1 text-base font-semibold text-slate-900">
                                                {{ $formatMoney($transaction->amount, $transaction->currency ?? $selectedUser->currency) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="rounded-2xl border border-dashed border-slate-200 px-4 py-6 text-center text-slate-500">No transactions yet</div>
                            @endforelse
                        </div>
                    </div>

                    @php($reportAction = $this->getAction('reportViolation'))
                    @if($reportAction)
                        <div class="rounded-3xl bg-rose-600 p-6 text-white shadow-lg">
                            <div class="text-lg font-semibold">Need to report a violation?</div>
                            <p class="mt-2 text-sm text-rose-50/90">
                                Log a fraud claim and the team will follow up with the client immediately.
                            </p>
                            <div class="mt-4">
                                <x-filament-actions::action :action="$reportAction" class="fi-btn fi-btn--size-lg fi-btn--color-white/95" />
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
