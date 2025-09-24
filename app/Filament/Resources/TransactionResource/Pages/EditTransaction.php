<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use App\Models\Account;
use App\Models\User;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;

class EditTransaction extends EditRecord
{
    protected static string $resource = TransactionResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $userId = $data['user_id'] ?? $this->getRecord()->user_id;

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

        if (isset($data['amount'])) {
            $data['amount'] = round((float) $data['amount'], 2);
        }

        return $data;
    }
}
