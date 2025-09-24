@extends('layouts.admin')
@section('title', 'Админка')
@section('content')
@php
    $formatMoney = static function ($amount, ?string $currency): string {
        if ($amount === null) {
            return '—';
        }

        $formatted = number_format((float) $amount, 2, '.', ' ');
        $currencyCode = $currency ?: (config('currencies.default') ?? 'EUR');

        return $formatted . ' ' . $currencyCode;
    };

    $formatDate = static function ($value, string $format = 'd.m.Y H:i'): string {
        if (! $value) {
            return '—';
        }

        if ($value instanceof \Carbon\CarbonInterface) {
            return $value->format($format);
        }

        try {
            return \Illuminate\Support\Carbon::parse($value)->format($format);
        } catch (\Throwable $e) {
            return (string) $value;
        }
    };

    $statusBadgeStyle = static function (?string $status): string {
        return match (strtolower((string) $status)) {
            'approved', 'active', 'verified', 'verificated', 'success', 'completed' => 'display:inline-flex; align-items:center; padding:0.25rem 0.75rem; border-radius:9999px; font-weight:600; font-size:0.75rem; background:#dcfce7; color:#166534; text-transform:uppercase;',
            'pending', 'hold', 'processing' => 'display:inline-flex; align-items:center; padding:0.25rem 0.75rem; border-radius:9999px; font-weight:600; font-size:0.75rem; background:#fef3c7; color:#92400e; text-transform:uppercase;',
            'blocked', 'rejected', 'failed', 'declined', 'canceled' => 'display:inline-flex; align-items:center; padding:0.25rem 0.75rem; border-radius:9999px; font-weight:600; font-size:0.75rem; background:#fee2e2; color:#b91c1c; text-transform:uppercase;',
            default => 'display:inline-flex; align-items:center; padding:0.25rem 0.75rem; border-radius:9999px; font-weight:600; font-size:0.75rem; background:#e2e8f0; color:#334155; text-transform:uppercase;',
        };
    };

    $selectedUserCurrency = $selectedUser?->currency ?? (config('currencies.default') ?? 'EUR');
    $hasClients = $clientOptions->isNotEmpty();
    $withdrawalTab = old('method', 'card');
@endphp

<div class="wrapper">
    <main class="page">
        <div class="admin-page">
            <div class="container">
                <div class="admin-panel">
                    @if(session('status'))
                        <div style="margin-bottom: 1.5rem; padding: 1rem 1.5rem; background: #ecfdf5; border-radius: 1rem; color: #047857; font-weight: 600;">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if($errors->any() && ! session('status'))
                        <div style="margin-bottom: 1.5rem; padding: 1rem 1.5rem; background: #fef2f2; border-radius: 1rem; color: #b91c1c; font-weight: 600;">
                            Исправьте ошибки формы и попробуйте снова.
                        </div>
                    @endif

                    <div class="admin-panel__block">
                        <div class="admin-panel__title">User selection</div>

                        @if(! $hasClients)
                            <p class="admin-panel__empty">Нет клиентов для отображения. Создайте клиента через Filament панель.</p>
                        @else
                            <form method="GET" action="{{ route('admin.dashboard') }}" class="admin-panel__line" style="gap: 1rem; flex-wrap: wrap;" id="admin-dashboard-user-form">
                                <select id="admin-dashboard-client" name="user" class="admin-panel__select" onchange="window.adminDashboardChangeUser(this.value)">
                                    @foreach($clientOptions as $id => $name)
                                        <option value="{{ $id }}" @selected($selectedUserId === (int) $id)>{{ $name }}</option>
                                    @endforeach
                                </select>
                                @if($selectedUser)
                                    <div class="admin-panel__item">
                                        <div style="font-size: 0.75rem; text-transform: uppercase; color: #63616C; letter-spacing: 0.04em;">Primary account</div>
                                        <div style="font-weight: 600; font-size: 1.125rem;">{{ $primaryAccount ? $formatMoney($primaryAccount->balance, $primaryAccount->currency ?? $selectedUserCurrency) : '—' }}</div>
                                        @if($primaryAccount)
                                            <div style="font-size: 0.85rem; color: #63616C;">{{ $primaryAccount->number }}</div>
                                        @endif
                                    </div>
                                @endif
                            </form>

                            @if($selectedUser)
                                <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; margin-top: 1rem;">
                                    <button type="button" class="btn btn--md" data-popup="#support-modal">Написать в поддержку</button>
                                    <button type="button" class="btn btn--md" data-popup="#withdraw-modal">Создать вывод средств</button>
                                    <button type="button" class="btn btn--md" data-popup="#violation">Сообщить о нарушении</button>
                                    <a class="btn btn--md" style="display: inline-flex; align-items: center; justify-content: center;" href="{{ route('filament.admin.resources.users.edit', $selectedUser) }}" target="_blank" rel="noopener">Профиль в панели</a>
                                </div>
                            @endif
                        @endif
                    </div>

                    <div class="admin-panel__block">
                        <div class="admin-panel__title">User info</div>

                        @if(! $selectedUser)
                            <p class="admin-panel__empty">Выберите клиента, чтобы увидеть информацию об аккаунте.</p>
                        @else
                            <div class="admin-panel__grid">
                                <div class="admin-panel__field">
                                    <div class="admin-panel__field-label">Full name</div>
                                    <input class="admin-panel__field-input" type="text" value="{{ $selectedUser->name }}" readonly>
                                </div>
                                <div class="admin-panel__field">
                                    <div class="admin-panel__field-label">Email</div>
                                    <input class="admin-panel__field-info" type="text" value="{{ $selectedUser->email }}" readonly>
                                </div>
                                <div class="admin-panel__field">
                                    <div class="admin-panel__field-label">Verification</div>
                                    <input class="admin-panel__field-info" type="text" value="{{ strtoupper($selectedUser->verification_status ?? 'unknown') }}" readonly>
                                </div>
                                <div class="admin-panel__field">
                                    <div class="admin-panel__field-label">Main balance</div>
                                    <input class="admin-panel__field-info" type="text" value="{{ $formatMoney($selectedUser->main_balance, $selectedUserCurrency) }}" readonly>
                                </div>
                                <div class="admin-panel__field">
                                    <div class="admin-panel__field-label">Currency</div>
                                    <input class="admin-panel__field-info" type="text" value="{{ $selectedUserCurrency }}" readonly>
                                </div>
                                <div class="admin-panel__field">
                                    <div class="admin-panel__field-label">Created at</div>
                                    <input class="admin-panel__field-info" type="text" value="{{ $formatDate($selectedUser->created_at, 'd.m.Y') }}" readonly>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="admin-panel__block">
                        <div class="admin-panel__title">Bank accounts</div>

                        @if(! $selectedUser || $accounts->isEmpty())
                            <p class="admin-panel__empty">Нет активных счетов для клиента.</p>
                        @else
                            <form method="GET" action="{{ route('admin.dashboard') }}" class="admin-panel__grid" style="gap: 1.25rem;">
                                <input type="hidden" name="user" value="{{ $selectedUserId }}">
                                <div class="admin-panel__field" style="min-width: 220px;">
                                    <div class="admin-panel__field-label">Выберите счёт</div>
                                    <select name="account" onchange="this.form.submit()">
                                        @foreach($accountOptions as $id => $number)
                                            <option value="{{ $id }}" @selected($selectedAccount && $selectedAccount->id === (int) $id)>{{ $number }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="admin-panel__field">
                                    <div class="admin-panel__field-label">Статус</div>
                                    <input class="admin-panel__field-info" type="text" value="{{ strtoupper($selectedAccount->status ?? '—') }}" readonly>
                                </div>
                                <div class="admin-panel__field">
                                    <div class="admin-panel__field-label">Тип счёта</div>
                                    <input class="admin-panel__field-info" type="text" value="{{ $selectedAccount->type ?? '—' }}" readonly>
                                </div>
                                <div class="admin-panel__field">
                                    <div class="admin-panel__field-label">Организация</div>
                                    <input class="admin-panel__field-info" type="text" value="{{ $selectedAccount->organization ?? '—' }}" readonly>
                                </div>
                                <div class="admin-panel__field">
                                    <div class="admin-panel__field-label">Банк</div>
                                    <input class="admin-panel__field-info" type="text" value="{{ $selectedAccount->bank ?? '—' }}" readonly>
                                </div>
                                <div class="admin-panel__field">
                                    <div class="admin-panel__field-label">Баланс счёта</div>
                                    <input class="admin-panel__field-info" type="text" value="{{ $selectedAccount ? $formatMoney($selectedAccount->balance, $selectedAccount->currency ?? $selectedUserCurrency) : '—' }}" readonly>
                                </div>
                                <div class="admin-panel__field">
                                    <div class="admin-panel__field-label">Срок действия</div>
                                    <input class="admin-panel__field-info" type="text" value="{{ $selectedAccount ? $formatDate($selectedAccount->term, 'd.m.Y') : '—' }}" readonly>
                                </div>
                                <div class="admin-panel__field">
                                    <div class="admin-panel__field-label">Инициалы клиента</div>
                                    <input class="admin-panel__field-info" type="text" value="{{ $selectedAccount->client_initials ?? '—' }}" readonly>
                                </div>
                                <div class="admin-panel__field">
                                    <div class="admin-panel__field-label">Инициалы брокера</div>
                                    <input class="admin-panel__field-info" type="text" value="{{ $selectedAccount->broker_initials ?? '—' }}" readonly>
                                </div>
                                <div class="admin-panel__field">
                                    <div class="admin-panel__field-label">Primary</div>
                                    <input class="admin-panel__field-info" type="text" value="{{ $selectedAccount && $selectedAccount->is_default ? 'YES' : 'NO' }}" readonly>
                                </div>
                            </form>
                        @endif

                        <button class="btn btn--md" data-popup="#create-modal" type="button" @disabled(! $selectedUser)>Создать новый счёт</button>
                    </div>

                    <div class="admin-panel__block">
                        <div class="admin-panel__title">Последние транзакции</div>
                        @if(! $selectedUser || $transactions->isEmpty())
                            <p class="admin-panel__empty">Для выбранного клиента нет транзакций.</p>
                        @else
                            <div class="admin-panel__table">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Дата</th>
                                            <th>Тип</th>
                                            <th>От</th>
                                            <th>Кому</th>
                                            <th>Сумма</th>
                                            <th>Статус</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($transactions as $transaction)
                                            <tr>
                                                <td>{{ $formatDate($transaction->created_at, 'd.m.Y H:i') }}</td>
                                                <td>{{ strtoupper($transaction->type) }}</td>
                                                <td>{{ $transaction->from }}</td>
                                                <td>{{ $transaction->to }}</td>
                                                <td>{{ $formatMoney($transaction->amount, $transaction->currency ?? $selectedUserCurrency) }}</td>
                                                <td><span style="{{ $statusBadgeStyle($transaction->status) }}">{{ strtoupper($transaction->status) }}</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    <div class="admin-panel__block">
                        <div class="admin-panel__title">Заявки на вывод средств</div>
                        @if(! $selectedUser || $withdrawals->isEmpty())
                            <p class="admin-panel__empty">Данных пока нет.</p>
                        @else
                            <div class="admin-panel__table">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Дата</th>
                                            <th>Метод</th>
                                            <th>Счёт</th>
                                            <th>Сумма</th>
                                            <th>Статус</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($withdrawals as $withdrawal)
                                            <tr>
                                                <td>{{ $formatDate($withdrawal->created_at, 'd.m.Y H:i') }}</td>
                                                <td>{{ ucfirst($withdrawal->method) }}</td>
                                                <td>{{ $withdrawal->fromAccount?->number ?? 'Main balance' }}</td>
                                                <td>{{ $formatMoney($withdrawal->amount, $selectedUserCurrency) }}</td>
                                                <td><span style="{{ $statusBadgeStyle($withdrawal->status) }}">{{ strtoupper($withdrawal->status) }}</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    <div class="admin-panel__block">
                        <div class="admin-panel__title">Документы</div>
                        @if(! $selectedUser || $documents->isEmpty())
                            <p class="admin-panel__empty">Документы отсутствуют.</p>
                        @else
                            <ul class="admin-panel__list">
                                @foreach($documents as $document)
                                    <li>
                                        <div>
                                            <div style="font-weight: 600;">{{ $document->original_name }}</div>
                                            <div style="font-size: 0.85rem; color: #63616C;">{{ $formatDate($document->created_at, 'd.m.Y H:i') }}</div>
                                        </div>
                                        <span style="{{ $statusBadgeStyle($document->status) }}">{{ strtoupper($document->status ?? 'PENDING') }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    <div class="admin-panel__block">
                        <div class="admin-panel__title">Сообщения о нарушениях</div>
                        @if(! $selectedUser || $fraudClaims->isEmpty())
                            <p class="admin-panel__empty">Заявок нет.</p>
                        @else
                            <ul class="admin-panel__list">
                                @foreach($fraudClaims as $claim)
                                    <li>
                                        <div>
                                            <div style="font-weight: 600;">{{ str(strip_tags($claim->details))->limit(80) }}</div>
                                            <div style="font-size: 0.85rem; color: #63616C;">{{ $formatDate($claim->created_at, 'd.m.Y H:i') }}</div>
                                        </div>
                                        <span style="{{ $statusBadgeStyle($claim->status) }}">{{ strtoupper($claim->status) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

@php
    $supportErrors = $errors->support ?? null;
    $fraudErrors = $errors->fraud ?? null;
    $withdrawalErrors = $errors->withdrawal ?? null;
    $accountErrors = $errors->account ?? null;
@endphp

<div aria-hidden="true" class="popup popup--sm" id="support-modal">
    <div class="popup__wrapper">
        <div class="popup__content">
            <button class="popup__close" data-close type="button">
                <svg fill="none" height="20" viewBox="0 0 20 20" width="20" xmlns="http://www.w3.org/2000/svg">
                    <path d="M1 1L19 19M19 1L1 19" stroke="black" stroke-linecap="round" stroke-width="2"></path>
                </svg>
            </button>
            <div class="modal-content">
                <div class="modal-content__top">
                    <div class="logo"><img alt="logo" src="{{ asset('personal-acc/img/logo.svg') }}"></div>
                    <div class="modal-content__text">
                        <p>Отправить сообщение в поддержку</p>
                    </div>
                </div>
                <div class="modal-content__body">
                    @if($supportErrors && $supportErrors->any())
                        <div style="margin-bottom: 1rem; padding: 0.75rem 1rem; background: #fef2f2; color: #b91c1c; border-radius: 0.75rem;">{{ $supportErrors->first() }}</div>
                    @endif
                    <form method="POST" action="{{ route('admin.dashboard.support') }}">
                        @csrf
                        <div class="field">
                            <select name="user_id" @disabled(! $hasClients)>
                                <option value="">Выберите клиента</option>
                                @foreach($clientOptions as $id => $name)
                                    <option value="{{ $id }}" @selected(old('user_id', $selectedUser?->id) == (int) $id)>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <textarea name="message" placeholder="Сообщение" rows="6">{{ old('message') }}</textarea>
                            @error('message', 'support')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                        <button class="btn btn--md" type="submit" @disabled(! $hasClients)>Отправить</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div aria-hidden="true" class="popup" id="withdraw-modal">
    <div class="popup__wrapper">
        <div class="popup__content">
            <button class="popup__close" data-close type="button">
                <svg fill="none" height="20" viewBox="0 0 20 20" width="20" xmlns="http://www.w3.org/2000/svg">
                    <path d="M1 1L19 19M19 1L1 19" stroke="black" stroke-linecap="round" stroke-width="2"></path>
                </svg>
            </button>
            <div class="modal-content">
                <div class="modal-content__top">
                    <div class="logo"><img alt="logo" src="{{ asset('personal-acc/img/logo.svg') }}"></div>
                    <div class="modal-content__text">
                        <p>Создать заявку на вывод средств</p>
                    </div>
                </div>
                <div class="modal-content__body">
                    @if($withdrawalErrors && $withdrawalErrors->any())
                        <div style="margin-bottom: 1rem; padding: 0.75rem 1rem; background: #fef2f2; color: #b91c1c; border-radius: 0.75rem;">{{ $withdrawalErrors->first() }}</div>
                    @endif
                    <div class="tabs" data-tabs data-tabs-animate="300">
                        <nav class="tabs__navigation" data-tabs-titles>
                            <button class="tabs__title {{ $withdrawalTab === 'card' ? '_tab-active' : '' }}" type="button" data-tabs-title>
                                <span>На банковскую карту</span>
                            </button>
                            <button class="tabs__title {{ $withdrawalTab === 'bank' ? '_tab-active' : '' }}" type="button" data-tabs-title>
                                <span>По IBAN</span>
                            </button>
                            <button class="tabs__title {{ $withdrawalTab === 'crypto' ? '_tab-active' : '' }}" type="button" data-tabs-title>
                                <span>В криптовалюте</span>
                            </button>
                        </nav>
                        <div class="tabs__content" data-tabs-body>
                            <div class="tabs__body" data-tabs-item @if($withdrawalTab !== 'card') hidden @endif>
                                <form method="POST" action="{{ route('admin.dashboard.withdrawals.store') }}">
                                    @csrf
                                    <input type="hidden" name="method" value="card">
                                    <input type="hidden" name="user_id" value="{{ old('user_id', $selectedUser?->id) }}">
                                    <div class="field">
                                        <select name="from_account_id">
                                            <option value="">Основной баланс</option>
                                            @foreach($accountOptions as $id => $number)
                                                <option value="{{ $id }}" @selected(old('from_account_id') == (int) $id)>{{ $number }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="field">
                                        <input name="details[card_number]" placeholder="1111 2222 3333 4444" type="text" value="{{ old('details.card_number') }}">
                                        @error('details.card_number', 'withdrawal')
                                            <span class="error-message">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="field">
                                        <input name="details[card_holder]" placeholder="Fullname card holder" type="text" value="{{ old('details.card_holder') }}">
                                        @error('details.card_holder', 'withdrawal')
                                            <span class="error-message">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="field">
                                        <input name="amount" placeholder="Amount" type="number" step="0.01" min="0" value="{{ old('amount') }}">
                                        @error('amount', 'withdrawal')
                                            <span class="error-message">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <button class="btn btn--md" type="submit" @disabled(! $selectedUser)>Отправить</button>
                                </form>
                            </div>
                            <div class="tabs__body" data-tabs-item @if($withdrawalTab !== 'bank') hidden @endif>
                                <form method="POST" action="{{ route('admin.dashboard.withdrawals.store') }}">
                                    @csrf
                                    <input type="hidden" name="method" value="bank">
                                    <input type="hidden" name="user_id" value="{{ old('user_id', $selectedUser?->id) }}">
                                    <div class="field">
                                        <select name="from_account_id">
                                            <option value="">Основной баланс</option>
                                            @foreach($accountOptions as $id => $number)
                                                <option value="{{ $id }}" @selected(old('from_account_id') == (int) $id)>{{ $number }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="field">
                                        <input name="details[iban]" placeholder="Enter IBAN" type="text" value="{{ old('details.iban') }}">
                                        @error('details.iban', 'withdrawal')
                                            <span class="error-message">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="field">
                                        <input name="details[bic]" placeholder="BIC code" type="text" value="{{ old('details.bic') }}">
                                        @error('details.bic', 'withdrawal')
                                            <span class="error-message">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="field">
                                        <input name="details[holder]" placeholder="Fullname bank account holder" type="text" value="{{ old('details.holder') }}">
                                    </div>
                                    <div class="field">
                                        <input name="details[country]" placeholder="Country" type="text" value="{{ old('details.country') }}">
                                    </div>
                                    <div class="field">
                                        <input name="details[bank_name]" placeholder="Name of the bank" type="text" value="{{ old('details.bank_name') }}">
                                    </div>
                                    <div class="field">
                                        <input name="amount" placeholder="Amount" type="number" step="0.01" min="0" value="{{ old('amount') }}">
                                        @error('amount', 'withdrawal')
                                            <span class="error-message">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <button class="btn btn--md" type="submit" @disabled(! $selectedUser)>Отправить</button>
                                </form>
                            </div>
                            <div class="tabs__body" data-tabs-item @if($withdrawalTab !== 'crypto') hidden @endif>
                                <form method="POST" action="{{ route('admin.dashboard.withdrawals.store') }}" class="form-crypto">
                                    @csrf
                                    <input type="hidden" name="method" value="crypto">
                                    <input type="hidden" name="user_id" value="{{ old('user_id', $selectedUser?->id) }}">
                                    <div class="field">
                                        <select name="from_account_id">
                                            <option value="">Основной баланс</option>
                                            @foreach($accountOptions as $id => $number)
                                                <option value="{{ $id }}" @selected(old('from_account_id') == (int) $id)>{{ $number }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="field">
                                        <input name="details[address]" placeholder="Deposit address" type="text" value="{{ old('details.address') }}">
                                        @error('details.address', 'withdrawal')
                                            <span class="error-message">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="field">
                                        <input name="details[network]" placeholder="Network" type="text" value="{{ old('details.network') }}">
                                    </div>
                                    <div class="field">
                                        <input name="details[coin]" placeholder="Coin" type="text" value="{{ old('details.coin') }}">
                                    </div>
                                    <div class="field">
                                        <input name="amount" placeholder="Amount" type="number" step="0.01" min="0" value="{{ old('amount') }}">
                                        @error('amount', 'withdrawal')
                                            <span class="error-message">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <button class="btn btn--md" type="submit" @disabled(! $selectedUser)>Отправить</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div aria-hidden="true" class="popup popup--md" id="violation">
    <div class="popup__wrapper">
        <div class="popup__content">
            <button class="popup__close" data-close type="button">
                <svg fill="none" height="20" viewBox="0 0 20 20" width="20" xmlns="http://www.w3.org/2000/svg">
                    <path d="M1 1L19 19M19 1L1 19" stroke="black" stroke-linecap="round" stroke-width="2"></path>
                </svg>
            </button>
            <div class="modal-content">
                <div class="modal-content__top">
                    <div class="logo"><img alt="logo" src="{{ asset('personal-acc/img/logo.svg') }}"></div>
                    <div class="modal-content__text">
                        <p>Сообщить о нарушении</p>
                    </div>
                </div>
                <div class="modal-content__body">
                    @if($fraudErrors && $fraudErrors->any())
                        <div style="margin-bottom: 1rem; padding: 0.75rem 1rem; background: #fef2f2; color: #b91c1c; border-radius: 0.75rem;">{{ $fraudErrors->first() }}</div>
                    @endif
                    <form method="POST" action="{{ route('admin.dashboard.fraud-claims.store') }}">
                        @csrf
                        <div class="field">
                            <select name="user_id" @disabled(! $hasClients)>
                                <option value="">Выберите клиента</option>
                                @foreach($clientOptions as $id => $name)
                                    <option value="{{ $id }}" @selected(old('user_id', $selectedUser?->id) == (int) $id)>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <textarea name="details" placeholder="Опишите нарушение" rows="6">{{ old('details') }}</textarea>
                            @error('details', 'fraud')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                        <button class="btn" type="submit" @disabled(! $hasClients)>Отправить</button>
                        <label class="modal-content__file">
                            <input hidden type="file" disabled>
                            <span>Прикрепление файлов реализуется отдельно</span>
                        </label>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div aria-hidden="true" class="popup popup--sm" id="create-modal">
    <div class="popup__wrapper">
        <div class="popup__content">
            <div class="create-account">
                <div class="create-account__title">Создание нового счёта</div>
                @if($accountErrors && $accountErrors->any())
                    <div style="margin-bottom: 1rem; padding: 0.75rem 1rem; background: #fef2f2; color: #b91c1c; border-radius: 0.75rem;">{{ $accountErrors->first() }}</div>
                @endif
                <form action="{{ route('admin.dashboard.accounts.store') }}" method="POST" class="create-account__form">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ old('user_id', $selectedUser?->id) }}">

                    <div class="field">
                        <input name="number" placeholder="Номер счёта" type="text" value="{{ old('number') }}">
                        @error('number', 'account')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field">
                        <select name="type">
                            <option value="">Тип счёта</option>
                            @foreach($accountTypeOptions as $code => $label)
                                <option value="{{ $code }}" @selected(old('type') === $code)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('type', 'account')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field">
                        <input name="balance" placeholder="Баланс" type="number" step="0.01" min="0" value="{{ old('balance') }}">
                        @error('balance', 'account')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field">
                        <select name="currency">
                            <option value="">Валюта (по умолчанию {{ $selectedUserCurrency }})</option>
                            @foreach($currencyOptions as $code => $label)
                                <option value="{{ $code }}" @selected(old('currency') === $code)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('currency', 'account')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field">
                        <input name="organization" placeholder="Организация" type="text" value="{{ old('organization') }}">
                        @error('organization', 'account')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field">
                        <input name="bank" placeholder="Банк" type="text" value="{{ old('bank') }}">
                    </div>

                    <div class="field">
                        <input name="client_initials" placeholder="Инициалы клиента" type="text" value="{{ old('client_initials') }}">
                        @error('client_initials', 'account')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field">
                        <input name="broker_initials" placeholder="Инициалы брокера" type="text" value="{{ old('broker_initials') }}">
                        @error('broker_initials', 'account')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field">
                        <input name="term" placeholder="Срок действия" type="date" value="{{ old('term') }}">
                        @error('term', 'account')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field">
                        <select name="status">
                            <option value="">Статус</option>
                            @foreach($accountStatusOptions as $code => $label)
                                <option value="{{ $code }}" @selected(old('status') === $code)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status', 'account')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="checkbox">
                        <input class="checkbox__input" id="account_is_default" name="is_default" type="checkbox" value="1" @checked(old('is_default'))>
                        <label class="checkbox__label" for="account_is_default"><span class="checkbox__text">Сделать основным</span></label>
                    </div>

                    <button class="btn btn--md" type="submit" @disabled(! $selectedUser)>Добавить счёт</button>
                </form>
            </div>
        </div>
</div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const baseDashboardUrl = @json(route('admin.dashboard'));

        window.adminDashboardChangeUser = function (value) {
            const url = new URL(baseDashboardUrl, window.location.origin);

            if (value) {
                url.searchParams.set('user', value);
            }

            window.location.assign(url.toString());
        };

        const userForm = document.getElementById('admin-dashboard-user-form');
        if (userForm) {
            userForm.addEventListener('submit', function (event) {
                event.preventDefault();
                const select = userForm.querySelector('select[name="user"]');
                if (select) {
                    window.adminDashboardChangeUser(select.value);
                }
            });
        }
    })();
</script>
@endpush
