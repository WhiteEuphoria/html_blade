@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $formatMoney = static function ($amount, ?string $currency = null): string {
        if ($amount === null) {
            return '—';
        }

        $currency = $currency ?: (config('currencies.default') ?? 'EUR');

        return number_format((float) $amount, 2, '.', ' ') . ' ' . $currency;
    };

    $maskValue = static function (?string $value, int $prefix = 4, int $suffix = 4): string {
        $value = trim((string) $value);

        if ($value === '') {
            return '—';
        }

        $length = Str::length($value);

        if ($length <= $prefix + $suffix + 3) {
            return $value;
        }

        return Str::substr($value, 0, $prefix) . ' ... ' . Str::substr($value, -$suffix);
    };

    $accountTypeLabel = static function ($type): string {
        return config('accounts.types')[$type] ?? (string) $type ?: '—';
    };

    $accountStatusClass = static function (?string $status): string {
        return match (strtolower((string) $status)) {
            'active' => 'user-table__status user-table__status--success',
            'hold', 'on hold', 'pending', 'processing' => 'user-table__status user-table__status--hold',
            'blocked', 'rejected', 'declined' => 'user-table__status user-table__status--block',
            default => 'user-table__status user-table__status--hold',
        };
    };

    $transactionStatusClass = static function (?string $status): string {
        return match (strtolower((string) $status)) {
            'success', 'approved', 'completed' => 'transaction-item transaction-item--success',
            'blocked', 'failed', 'declined', 'rejected' => 'transaction-item transaction-item--block',
            default => 'transaction-item transaction-item--wait',
        };
    };

    $transactionStatusLabel = static function (?string $status): string {
        $status = (string) $status;

        return $status !== '' ? Str::title($status) : 'Pending';
    };

    $transactionDateParts = static function ($transaction): array {
        $date = optional($transaction->created_at);

        return [
            $date?->format('d/m/y') ?? '—',
            $date?->format('H:i:s') ?? '—',
        ];
    };

    $userStatusClass = match (strtolower((string) $user->verification_status)) {
        'approved', 'active', 'verified', 'verificated' => 'user-info__status user-info__status--verify',
        'blocked', 'rejected' => 'user-info__status user-info__status--verify',
        default => 'user-info__status',
    };

    $userStatusLabel = ucfirst($user->verification_status ?? 'pending');

    $locationPieces = array_filter([
        $country ?: null,
        $dateOfBirth ? $dateOfBirth->format('m.d.Y') : null,
    ]);

    $locationText = implode(', ', $locationPieces);

    $portfolioBalance = $totalAccountBalance ?: $mainBalance;
    $portfolioBalance = $portfolioBalance ?: optional($primaryAccount)->balance;

    $transactionsList = $transactions->take(8);
    $transactionsTable = $transactions->take(12);
    $transactionsRoute = Route::has('filament.client.resources.transactions.index')
        ? route('filament.client.resources.transactions.index')
        : null;
@endphp

<div class="main active">
    <div class="user-info" data-da=".grid,1023.98,first">
        <div class="user-info__col">
            <div class="user-info__title">{{ __('Welcome') }} {{ $user->name }}</div>
            <div class="user-info__text">
                @if($locationText)
                    {{ $locationText }}
                @else
                    {{ $user->email }}
                @endif
            </div>
            <div class="{{ $userStatusClass }}">{{ $userStatusLabel }}</div>
        </div>
        <div class="user-info__col">
            <div class="user-info__title" style="font-weight: 600;">
                {{ __('Balance') }}
                <img alt="wallet" src="{{ asset('personal-acc/img/icons/wallet.svg') }}"/>
            </div>
            <div class="user-info__text-lg">{{ $formatMoney($portfolioBalance, $displayCurrency) }}</div>
        </div>
    </div>

    <div class="user-table">
        <table>
            <thead>
            <tr>
                <th>{{ __('Company') }} <br/> {{ __('Broker') }}</th>
                <th>{{ __('Bank') }} <br/> {{ __('Account No.') }}</th>
                <th>{{ __('Owner') }}</th>
                <th>{{ __('Type') }}</th>
                <th class="desktop">{{ __('Expiry date') }}</th>
                <th>
                    <span class="desktop">{{ __('Balance') }}</span>
                    <span class="mobile">{{ __('Expiry date') }} <br/> {{ __('Balance') }}</span>
                </th>
                <th>{{ __('Status') }}</th>
            </tr>
            </thead>
            <tbody>
            @forelse($accounts as $account)
                @php
                    $term = optional($account->term)->format('d/m/y');
                    $balanceLabel = $formatMoney($account->balance, $account->currency ?? $displayCurrency);
                    $statusClass = $accountStatusClass($account->status);
                @endphp
                <tr>
                    <td>
                        <div class="user-table__td">
                            {{ $account->organization ?? '—' }}
                            <span>{{ $account->broker_initials ?? '—' }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="user-table__td">
                            {{ $account->bank ?? '—' }}
                            <span>{{ $maskValue($account->number) }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="user-table__td">
                            <span>{{ $account->client_initials ?: $user->name }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="user-table__td">
                            <span>{{ $accountTypeLabel($account->type) }}</span>
                        </div>
                    </td>
                    <td class="desktop">
                        <div class="user-table__td">
                            <b>{{ $term ?: '—' }}</b>
                        </div>
                    </td>
                    <td>
                        <div class="user-table__td">
                            <span class="mobile"><b>{{ $term ?: '—' }}</b></span>
                            <b>{{ $balanceLabel }}</b>
                        </div>
                    </td>
                    <td>
                        <div class="{{ $statusClass }}">
                            {{ Str::upper($account->status ?? 'Pending') }}
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        <div class="user-table__td" style="text-align: center;">
                            {{ __('No accounts yet') }}
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="aside">
    <div class="transaction desktop">
        <div class="transaction-title">{{ __('Transactions') }} <img alt="transactions" src="{{ asset('personal-acc/img/icons/copy.svg') }}"/>
        </div>
        <div class="transaction-list">
            @forelse($transactionsList as $transaction)
                @php
                    [$datePart, $timePart] = $transactionDateParts($transaction);
                    $statusClass = $transactionStatusClass($transaction->status);
                @endphp
                <div class="{{ $statusClass }}">
                    <div class="transaction-item__top">
                        <div class="transaction-item__title">{{ $transaction->type ?? __('Transaction') }}</div>
                        <div class="transaction-item__date">
                            <span>{{ $datePart }}</span>
                            <span>{{ $timePart }}</span>
                        </div>
                    </div>
                    <div class="transaction-item__bottom">
                        <div class="transaction-item__block">
                            <div class="transaction-item__num">{{ $maskValue($transaction->from) }}</div>
                            <span><img alt="arrow" src="{{ asset('personal-acc/img/icons/arrow.svg') }}"/></span>
                            <div class="transaction-item__text-md">{{ $maskValue($transaction->to) }}</div>
                        </div>
                        <div class="transaction-item__sum">{{ $formatMoney($transaction->amount, $transaction->currency ?? $displayCurrency) }}</div>
                    </div>
                </div>
            @empty
                <div class="transaction-item transaction-item--wait">
                    <div class="transaction-item__top">
                        <div class="transaction-item__title">{{ __('No transactions yet') }}</div>
                    </div>
                </div>
            @endforelse
        </div>
        <div class="transaction-actions" style="display:flex; flex-direction:column; gap:0.75rem;">
            <a
                class="btn btn--secondary {{ $transactionsRoute ? '' : 'is-disabled' }}"
                @if($transactionsRoute)
                    href="{{ $transactionsRoute }}"
                @else
                    href="#"
                    style="pointer-events:none; opacity:0.6;"
                @endif
                style="text-align:center;"
            >
                {{ __('View all transactions') }}
                <span class="btn__icon"><img alt="arrow" src="{{ asset('personal-acc/img/icons/arrow.svg') }}"/></span>
            </a>
            <button class="btn btn--light" data-popup="#violation" type="button" style="text-align:center;">
                {{ __('Report a violation') }}
                <span class="btn__icon"><img alt="alert" src="{{ asset('personal-acc/img/icons/loudspeaker.svg') }}"/></span>
            </button>
        </div>
    </div>
    <div class="user-table">
        <table>
            <thead>
            <tr>
                <th>{{ __('From') }}</th>
                <th>{{ __('To') }}</th>
                <th>{{ __('Date') }} <br/> {{ __('Time') }}</th>
                <th>{{ __('Amount') }}</th>
                <th>{{ __('Status') }}</th>
            </tr>
            </thead>
            <tbody>
            @forelse($transactionsTable as $transaction)
                @php
                    [$datePart, $timePart] = $transactionDateParts($transaction);
                    $statusLabel = $transactionStatusLabel($transaction->status);
                @endphp
                <tr>
                    <td style="font-weight:400">
                        {{ $maskValue($transaction->from) }}
                    </td>
                    <td>
                        {{ $maskValue($transaction->to) }}
                    </td>
                    <td style="color:#747474">
                        {{ $datePart }}
                        <br/>
                        {{ $timePart }}
                    </td>
                    <td>
                        <b>{{ $formatMoney($transaction->amount, $transaction->currency ?? $displayCurrency) }}</b>
                    </td>
                    <td>
                        {{ $statusLabel }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center;">
                        {{ __('No transactions yet') }}
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
