<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\FraudClaim;
use App\Models\SupportMessage;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $clientOptions = User::query()
            ->where('is_admin', false)
            ->orderBy('name')
            ->pluck('name', 'id');

        $selectedUserId = $request->integer('user');
        if ($selectedUserId && ! $clientOptions->has($selectedUserId)) {
            $selectedUserId = null;
        }

        if (! $selectedUserId && $clientOptions->isNotEmpty()) {
            $selectedUserId = (int) $clientOptions->keys()->first();
        }

        $selectedUser = null;
        $accounts = collect();
        $transactions = collect();
        $documents = collect();
        $fraudClaims = collect();
        $withdrawals = collect();

        $selectedAccount = null;

        if ($selectedUserId) {
            $selectedUser = User::query()
                ->with([
                    'accounts' => function (Relation $query) {
                        $query
                            ->orderByDesc('is_default')
                            ->orderBy('status')
                            ->orderByDesc('created_at');
                    },
                    'transactions' => fn (Relation $query) => $query->latest()->limit(10),
                    'documents' => fn (Relation $query) => $query->latest()->limit(5),
                    'fraudClaims' => fn (Relation $query) => $query->latest()->limit(5),
                    'withdrawals' => fn (Relation $query) => $query->latest()->limit(5),
                ])
                ->find($selectedUserId);

            if ($selectedUser) {
                $accounts = $selectedUser->accounts;
                $transactions = $selectedUser->transactions;
                $documents = $selectedUser->documents;
                $fraudClaims = $selectedUser->fraudClaims;
                $withdrawals = $selectedUser->withdrawals;

                if ($accounts->isNotEmpty()) {
                    $requestedAccountId = $request->integer('account');
                    $selectedAccount = $accounts->firstWhere('id', $requestedAccountId) ?? $accounts->first();
                }
            }
        }

        $primaryAccount = $accounts->firstWhere('is_default', true) ?? $accounts->first();
        $accountOptions = $accounts->mapWithKeys(fn (Account $account) => [$account->id => $account->number]);

        return view('admin.dashboard', [
            'clientOptions' => $clientOptions,
            'selectedUserId' => $selectedUserId,
            'selectedUser' => $selectedUser,
            'accounts' => $accounts,
            'transactions' => $transactions,
            'documents' => $documents,
            'fraudClaims' => $fraudClaims,
            'withdrawals' => $withdrawals,
            'primaryAccount' => $primaryAccount,
            'selectedAccount' => $selectedAccount,
            'accountOptions' => $accountOptions,
            'accountTypeOptions' => config('accounts.types', []),
            'withdrawalMethods' => [
                'card' => 'Card',
                'bank' => 'Bank account',
                'crypto' => 'Crypto',
            ],
            'accountStatusOptions' => [
                'Active' => 'Active',
                'Hold' => 'Hold',
                'Blocked' => 'Blocked',
            ],
            'currencyOptions' => collect(config('currencies.allowed', []))
                ->mapWithKeys(fn ($currency) => [$currency => $currency])
                ->all(),
        ]);
    }

    public function storeSupport(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('support', [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'message' => ['required', 'string', 'min:5'],
        ]);

        $user = User::query()->where('is_admin', false)->findOrFail($data['user_id']);

        SupportMessage::create([
            'user_id' => $user->id,
            'direction' => 'inbound',
            'message' => $data['message'],
        ]);

        return redirect()
            ->route('admin.dashboard', ['user' => $user->id])
            ->with('status', 'Support message sent.');
    }

    public function storeFraudClaim(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('fraud', [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'details' => ['required', 'string', 'min:10'],
        ]);

        $user = User::query()->where('is_admin', false)->findOrFail($data['user_id']);

        FraudClaim::create([
            'user_id' => $user->id,
            'details' => $data['details'],
            'status' => 'В рассмотрении',
        ]);

        return redirect()
            ->route('admin.dashboard', ['user' => $user->id])
            ->with('status', 'Fraud claim created.');
    }

    public function storeWithdrawal(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('withdrawal', [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'method' => ['required', Rule::in(['card', 'bank', 'crypto'])],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'from_account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'details' => ['nullable', 'array'],
            'details.*' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::query()->where('is_admin', false)->findOrFail($data['user_id']);

        $fromAccountId = $data['from_account_id'] ?? null;
        if ($fromAccountId) {
            $account = Account::query()->where('user_id', $user->id)->findOrFail($fromAccountId);
            $fromAccountId = $account->id;
        }

        $details = collect($data['details'] ?? [])
            ->filter(fn ($value) => filled($value))
            ->all();

        Withdrawal::create([
            'user_id' => $user->id,
            'amount' => $data['amount'],
            'method' => $data['method'],
            'from_account_id' => $fromAccountId,
            'requisites' => $details ? json_encode($details) : null,
            'status' => 'pending',
        ]);

        return redirect()
            ->route('admin.dashboard', ['user' => $user->id])
            ->with('status', 'Withdrawal request created.');
    }

    public function storeAccount(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('account', [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'number' => ['required', 'string', 'max:255', 'unique:accounts,number'],
            'type' => ['required', Rule::in(array_keys(config('accounts.types', [])))],
            'balance' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', Rule::in(config('currencies.allowed', []))],
            'organization' => ['required', 'string', 'max:255'],
            'bank' => ['nullable', 'string', 'max:255'],
            'client_initials' => ['required', 'string', 'max:255'],
            'broker_initials' => ['required', 'string', 'max:255'],
            'term' => ['required', 'date'],
            'status' => ['required', Rule::in(['Active', 'Hold', 'Blocked'])],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $user = User::query()->where('is_admin', false)->findOrFail($data['user_id']);

        Account::create([
            'user_id' => $user->id,
            'number' => $data['number'],
            'type' => $data['type'],
            'balance' => $data['balance'],
            'currency' => $data['currency'] ?: $user->currency,
            'organization' => $data['organization'],
            'bank' => $data['bank'],
            'client_initials' => $data['client_initials'],
            'broker_initials' => $data['broker_initials'],
            'term' => $data['term'],
            'status' => $data['status'],
            'is_default' => (bool) ($data['is_default'] ?? false),
        ]);

        return redirect()
            ->route('admin.dashboard', ['user' => $user->id])
            ->with('status', 'Account created.');
    }
}
