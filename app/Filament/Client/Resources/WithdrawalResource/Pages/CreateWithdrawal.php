<?php

namespace App\Filament\Client\Resources\WithdrawalResource\Pages;

use App\Filament\Client\Resources\WithdrawalResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateWithdrawal extends CreateRecord
{
    protected static string $resource = WithdrawalResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();

        $method = $data['method'] ?? 'card';
        $currency = $data['currency'] ?? (Auth::user()?->currency ?? config('currencies.default'));
        if ($method === 'crypto') {
            $details = [
                'type' => 'crypto',
                'address' => (string)($data['crypto_address'] ?? ''),
                'network' => (string)($data['crypto_network'] ?? ''),
                'currency' => (string) $currency,
            ];
            $data['requisites'] = json_encode($details, JSON_UNESCAPED_UNICODE);
            unset($data['crypto_address'], $data['crypto_network']);
            unset($data['currency']);
        } else {
            if ($method === 'bank') {
                $details = [
                    'type' => 'bank',
                    'recipient_name' => (string)($data['beneficiary_name'] ?? ''),
                    'bank_name' => (string)($data['bank_name'] ?? ''),
                    'swift' => (string)($data['swift'] ?? ''),
                    'bank_account' => (string)($data['bank_account'] ?? ''),
                    'currency' => (string) $currency,
                ];
                $data['requisites'] = json_encode($details, JSON_UNESCAPED_UNICODE);
                unset($data['beneficiary_name'], $data['bank_name'], $data['swift'], $data['bank_account']);
                unset($data['currency']);
            } else {
                // Normalize card details
                $pan = preg_replace('/\D+/', '', (string)($data['card_number'] ?? ''));
                $last4 = substr($pan, -4) ?: null;

                // Parse expiration (supports MM/YY or YYYY-MM-DD from DatePicker)
                $exp = (string)($data['card_expiration'] ?? '');
                $matches = [];
                $mm = null; $yy = null;
                if (preg_match('/^(0[1-9]|1[0-2])\/(\d{2})$/', $exp, $matches)) {
                    $mm = (int) $matches[1];
                    $yy = (int) $matches[2];
                } elseif (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $exp, $matches)) {
                    $yy = (int) substr($matches[1], -2);
                    $mm = (int) $matches[2];
                }

                $details = [
                    'type' => 'card',
                    'pan' => $pan,
                    'masked' => $pan ? trim(chunk_split($pan, 4, ' ')) : null,
                    'last4' => $last4,
                    'exp_month' => $mm,
                    'exp_year' => $yy,
                    'cvc' => preg_replace('/\D+/', '', (string)($data['card_cvc'] ?? '')),
                    'currency' => (string) $currency,
                ];
                $data['requisites'] = json_encode($details, JSON_UNESCAPED_UNICODE);

                // Do not persist sensitive fields
                unset($data['card_number'], $data['card_expiration'], $data['card_cvc']);
                unset($data['currency']);
            }
        }

        return $data;
    }
}
