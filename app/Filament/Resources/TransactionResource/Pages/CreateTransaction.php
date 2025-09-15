<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use App\Models\Account;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $userId = $data['user_id'] ?? null;

        // If currency selected and differs from user's, align user's currency
        if (!empty($data['currency']) && $userId) {
            $user = User::find($userId);
            if ($user && $user->currency !== $data['currency']) {
                $user->currency = $data['currency'];
                $user->save();
            }
        }
        // Ensure currency is set (fallback to user's/default)
        if (empty($data['currency'])) {
            $user = $userId ? User::find($userId) : null;
            $data['currency'] = $user?->currency ?? (config('currencies.default') ?? 'EUR');
        }

        // Normalize amount to 2 decimals
        if (isset($data['amount'])) {
            $data['amount'] = round((float) $data['amount'], 2);
        }

        return $data;
    }
}
