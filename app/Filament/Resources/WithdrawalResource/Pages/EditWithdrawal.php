<?php

namespace App\Filament\Resources\WithdrawalResource\Pages;

use App\Filament\Resources\WithdrawalResource;
use App\Models\Account;
use App\Models\User;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;

class EditWithdrawal extends EditRecord
{
    protected static string $resource = WithdrawalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Soft-validate funds before approving, show a red validation error instead of exception
        $status = $data['status'] ?? null;
        $alreadyApplied = (bool) ($this->getRecord()->applied ?? false);

        // Align user's currency if admin selected a currency in the form
        $selectedCurrency = $this->data['currency'] ?? null;
        $userIdForCurrency = (int) ($data['user_id'] ?? $this->getRecord()->user_id);
        if (!empty($selectedCurrency) && $userIdForCurrency) {
            $user = \App\Models\User::find($userIdForCurrency);
            if ($user && $user->currency !== $selectedCurrency) {
                $user->currency = $selectedCurrency;
                $user->save();
            }
        }

        if ($status === 'approved' && !$alreadyApplied) {
            $amount = (float) ($data['amount'] ?? 0);
            $userId = (int) ($data['user_id'] ?? $this->getRecord()->user_id);
            $fromAccountId = $data['from_account_id'] ?? $this->getRecord()->from_account_id;

            $available = null;

            if ($fromAccountId) {
                $acc = Account::find($fromAccountId);
                if (!$acc || $acc->user_id !== $userId) {
                    $this->addError('from_account_id', 'Selected source account is invalid for this user.');
                    Notification::make()->title('Invalid source account')->danger()->send();
                    throw new Halt();
                }
                $available = (float) $acc->balance;
            } else {
                $user = User::find($userId);
                $available = (float) ($user->main_balance ?? 0);
            }

            if ($amount > $available + 1e-6) {
                $this->addError('amount', 'Insufficient funds.');
                Notification::make()->title('Insufficient funds')->danger()->send();
                throw new Halt();
            }
        }

        // Merge structured details (bank/card/crypto) into requisites JSON
        $existing = [];
        if (!empty($this->getRecord()->requisites)) {
            $decoded = json_decode($this->getRecord()->requisites, true);
            if (is_array($decoded)) {
                $existing = $decoded;
            }
        }

        $payload = $existing;

        // Bank
        $bank = array_filter([
            'recipient_name' => $data['beneficiary_name'] ?? null,
            'bank_name' => $data['bank_name'] ?? null,
            'swift' => $data['swift'] ?? null,
            'bank_account' => $data['bank_account'] ?? null,
        ], fn ($v) => $v !== null && $v !== '');
        if ($bank) { $payload = array_replace($payload, $bank); }

        // Card
        $cardPan = $data['card_pan'] ?? null;
        $cardMasked = $data['card_masked'] ?? null;
        $cardExp = $data['card_exp'] ?? null; // expected MM/YY
        $cardCvc = $data['card_cvc_admin'] ?? null;

        if ($cardPan !== null || $cardMasked !== null || $cardExp !== null || $cardCvc !== null) {
            $mm = $existing['exp_month'] ?? null;
            $yy = $existing['exp_year'] ?? null;
            if (is_string($cardExp) && preg_match('/^(0[1-9]|1[0-2])\/(\d{2})$/', $cardExp, $m)) {
                $mm = (int) $m[1];
                $yy = (int) $m[2];
            }
            $last4 = null;
            $digits = null;
            if (!empty($cardPan)) {
                $digits = preg_replace('/\D+/', '', (string) $cardPan);
            } elseif (!empty($cardMasked)) {
                // If admin edited the "number" field with digits, allow using it as PAN
                $maybe = preg_replace('/\D+/', '', (string) $cardMasked);
                if (strlen($maybe) >= 12) {
                    $digits = $maybe;
                }
            }
            if ($digits !== null && $digits !== '') {
                $last4 = substr($digits, -4) ?: null;
                $payload['pan'] = $digits;
                // Store a fully visible grouped number instead of stars, as requested
                $payload['masked'] = trim(chunk_split($digits, 4, ' '));
            } elseif ($cardMasked !== null) {
                // Fall back to admin-provided masked string
                $payload['masked'] = $cardMasked;
            }
            if ($mm !== null) { $payload['exp_month'] = (int) $mm; }
            if ($yy !== null) { $payload['exp_year'] = (int) $yy; }
            if ($cardCvc !== null) { $payload['cvc'] = preg_replace('/\D+/', '', (string) $cardCvc); }
            $payload['type'] = $payload['type'] ?? 'card';
        }

        // Crypto
        $crypto = array_filter([
            'address' => $data['crypto_address'] ?? null,
            'network' => $data['crypto_network'] ?? null,
        ], fn ($v) => $v !== null && $v !== '');
        if ($crypto) {
            $payload = array_replace($payload, $crypto);
            $payload['type'] = 'crypto';
        }

        if ($payload !== $existing) {
            $data['requisites'] = json_encode($payload, JSON_UNESCAPED_UNICODE);
        }

        unset(
            $data['beneficiary_name'], $data['bank_name'], $data['swift'], $data['bank_account'],
            $data['card_pan'], $data['card_masked'], $data['card_exp'], $data['card_cvc_admin'],
            $data['crypto_address'], $data['crypto_network']
        );

        return $data;
    }
}
