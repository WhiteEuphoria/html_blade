<div class="dashboard-widget">
    <div class="dashboard-widget__header">
        <h3>{{ __('Request Withdrawal') }}</h3>
    </div>

    @if ($successMessage)
        <div class="dashboard-alert dashboard-alert--success" role="alert">
            {{ $successMessage }}
        </div>
    @endif

    <form wire:submit.prevent="submit" class="dashboard-form" x-data="{ method: @entangle('method') }">
        <div class="dashboard-form__grid">
            <div class="field">
                <label for="method">{{ __('Method') }}</label>
                <select id="method" wire:model="method">
                    <option value="card">{{ __('Card') }}</option>
                    <option value="bank">{{ __('Bank Account') }}</option>
                    <option value="crypto">{{ __('Crypto') }}</option>
                </select>
                @error('method') <span class="error-message">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="from_account_id">{{ __('From Account') }}</label>
                <select id="from_account_id" wire:model="from_account_id">
                    <option value="">— {{ __('Main Balance') }} —</option>
                    @foreach ($accounts as $acc)
                        <option value="{{ $acc->id }}">
                            {{ $acc->name ?? __('Account') }} ({{ $acc->type }}) — {{ $acc->currency ?? 'EUR' }} {{ number_format($acc->balance, 2, '.', ' ') }}
                        </option>
                    @endforeach
                </select>
                @error('from_account_id') <span class="error-message">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="amount">{{ __('Amount') }} ({{ $displayCurrency }})</label>
                <input type="number" step="0.01" id="amount" wire:model="amount" placeholder="0.00">
                @error('amount') <span class="error-message">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="dashboard-form__section" x-cloak>
            <div class="dashboard-form__grid" x-show="method === 'card'">
                <div class="field">
                    <label for="card_holder">{{ __('Card holder') }}</label>
                    <input type="text" id="card_holder" wire:model="card_holder" placeholder="{{ __('Card holder') }}">
                    @error('card_holder') <span class="error-message">{{ $message }}</span> @enderror
                </div>
                <div class="field">
                    <label for="card_number">{{ __('Card number') }}</label>
                    <input type="text" id="card_number" wire:model="card_number" placeholder="{{ __('Card number') }}">
                    @error('card_number') <span class="error-message">{{ $message }}</span> @enderror
                </div>
                <div class="field">
                    <label for="card_expiry">{{ __('Expiry (optional)') }}</label>
                    <input type="text" id="card_expiry" wire:model="card_expiry" placeholder="MM/YY">
                </div>
            </div>

            <div class="dashboard-form__grid" x-show="method === 'bank'">
                <div class="field">
                    <label for="beneficiary_name">{{ __('Beneficiary name') }}</label>
                    <input type="text" id="beneficiary_name" wire:model="beneficiary_name" placeholder="{{ __('Beneficiary name') }}">
                    @error('beneficiary_name') <span class="error-message">{{ $message }}</span> @enderror
                </div>
                <div class="field">
                    <label for="iban">{{ __('IBAN / Account') }}</label>
                    <input type="text" id="iban" wire:model="iban" placeholder="{{ __('IBAN / Account') }}">
                    @error('iban') <span class="error-message">{{ $message }}</span> @enderror
                </div>
                <div class="field">
                    <label for="swift">{{ __('SWIFT (optional)') }}</label>
                    <input type="text" id="swift" wire:model="swift" placeholder="SWIFT">
                </div>
                <div class="field">
                    <label for="bank_name">{{ __('Bank name (optional)') }}</label>
                    <input type="text" id="bank_name" wire:model="bank_name" placeholder="{{ __('Bank name') }}">
                </div>
            </div>

            <div class="dashboard-form__grid" x-show="method === 'crypto'">
                <div class="field">
                    <label for="network">{{ __('Network') }}</label>
                    <input type="text" id="network" wire:model="network" placeholder="{{ __('e.g. TRC20') }}">
                    @error('network') <span class="error-message">{{ $message }}</span> @enderror
                </div>
                <div class="field">
                    <label for="wallet_address">{{ __('Wallet address') }}</label>
                    <input type="text" id="wallet_address" wire:model="wallet_address" placeholder="{{ __('Wallet address') }}">
                    @error('wallet_address') <span class="error-message">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn--md">
            {{ __('Submit Request') }}
        </button>
    </form>
</div>
