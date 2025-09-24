<?php

namespace App\Filament\Pages;

use App\Models\Account;
use App\Models\FraudClaim;
use App\Models\SupportMessage;
use App\Models\User;
use App\Models\Withdrawal;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Relations\Relation;

class AdminDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $title = 'Client overview';

    protected static ?string $slug = 'dashboard';

    protected static string $view = 'filament.admin.pages.dashboard';

    public ?int $selectedUserId = null;

    public string $search = '';

    public function mount(): void
    {
        $this->selectedUserId = $this->getDefaultUserId();
    }

    public function updatedSelectedUserId($value): void
    {
        $this->selectedUserId = $value ? (int) $value : null;

        $this->dispatch('$refresh');
    }

    protected function getDefaultUserId(): ?int
    {
        return User::query()
            ->where('is_admin', false)
            ->orderBy('name')
            ->value('id');
    }

    /**
     * @return array<int, string>
     */
    public function getClientOptions(): array
    {
        return User::query()
            ->where('is_admin', false)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    protected function getViewData(): array
    {
        $user = null;
        $accounts = collect();
        $transactions = collect();
        $documents = collect();
        $fraudClaims = collect();
        $withdrawals = collect();

        if ($this->selectedUserId) {
            $user = User::query()
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
                ->find($this->selectedUserId);

            if ($user) {
                $accounts = $user->accounts;
                $transactions = $user->transactions;
                $documents = $user->documents;
                $fraudClaims = $user->fraudClaims;
                $withdrawals = $user->withdrawals;
            }
        }

        return [
            'selectedUser' => $user,
            'accounts' => $accounts,
            'transactions' => $transactions,
            'documents' => $documents,
            'fraudClaims' => $fraudClaims,
            'withdrawals' => $withdrawals,
            'clientOptions' => $this->getClientOptions(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->supportAction(),
            $this->withdrawalAction(),
            $this->reportViolationAction(),
        ];
    }

    protected function supportAction(): Action
    {
        return Action::make('support')
            ->label('Support')
            ->icon('heroicon-o-headset')
            ->color('gray')
            ->modalHeading('Send support message')
            ->slideOver()
            ->form([
                Forms\Components\Select::make('user_id')
                    ->label('Client')
                    ->options(fn () => $this->getClientOptions())
                    ->default(fn () => $this->selectedUserId)
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Textarea::make('message')
                    ->label('Message')
                    ->rows(6)
                    ->required(),
            ])
            ->action(function (array $data) {
                SupportMessage::create([
                    'user_id' => $data['user_id'],
                    'direction' => 'inbound',
                    'message' => $data['message'],
                ]);

                Notification::make()
                    ->title('Message sent')
                    ->success()
                    ->send();
            });
    }

    protected function withdrawalAction(): Action
    {
        return Action::make('withdrawal')
            ->label('Withdrawal of funds')
            ->icon('heroicon-o-banknotes')
            ->color('primary')
            ->modalHeading('Create withdrawal request')
            ->slideOver()
            ->form([
                Forms\Components\Select::make('user_id')
                    ->label('Client')
                    ->options(fn () => $this->getClientOptions())
                    ->default(fn () => $this->selectedUserId)
                    ->required()
                    ->reactive(),
                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->minValue(0.01)
                    ->required()
                    ->suffix(fn (Forms\Get $get) => optional(User::find($get('user_id')))->currency ?? config('currencies.default', 'EUR')),
                Forms\Components\Select::make('method')
                    ->options([
                        'card' => 'Card',
                        'bank' => 'Bank Account',
                        'crypto' => 'Crypto',
                    ])
                    ->required(),
                Forms\Components\Select::make('from_account_id')
                    ->label('From account')
                    ->options(function (Forms\Get $get) {
                        $userId = $get('user_id');
                        if (! $userId) {
                            return [];
                        }

                        return Account::query()
                            ->where('user_id', $userId)
                            ->orderByDesc('is_default')
                            ->orderBy('number')
                            ->pluck('number', 'id')
                            ->all();
                    })
                    ->searchable()
                    ->placeholder('Use main balance')
                    ->native(false)
                    ->columnSpanFull()
                    ->nullable(),
                Forms\Components\Textarea::make('requisites')
                    ->label('Requisites / Notes')
                    ->rows(4)
                    ->helperText('Optional details that help process the withdrawal')
                    ->nullable(),
            ])
            ->action(function (array $data) {
                $user = User::find($data['user_id']);

                Withdrawal::create([
                    'user_id' => $data['user_id'],
                    'amount' => $data['amount'],
                    'method' => $data['method'],
                    'from_account_id' => $data['from_account_id'] ?? null,
                    'requisites' => $data['requisites'] ? json_encode(['notes' => $data['requisites']]) : null,
                    'status' => 'pending',
                ]);

                Notification::make()
                    ->title('Withdrawal created')
                    ->body($user ? 'Request created for ' . $user->name : null)
                    ->success()
                    ->send();
            });
    }

    protected function reportViolationAction(): Action
    {
        return Action::make('reportViolation')
            ->label('Report a violation')
            ->icon('heroicon-o-megaphone')
            ->color('danger')
            ->modalHeading('Create fraud claim')
            ->slideOver()
            ->form([
                Forms\Components\Select::make('user_id')
                    ->label('Client')
                    ->options(fn () => $this->getClientOptions())
                    ->default(fn () => $this->selectedUserId)
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Textarea::make('details')
                    ->label('Details')
                    ->rows(6)
                    ->required(),
            ])
            ->action(function (array $data) {
                FraudClaim::create([
                    'user_id' => $data['user_id'],
                    'details' => $data['details'],
                    'status' => 'В рассмотрении',
                ]);

                Notification::make()
                    ->title('Fraud claim logged')
                    ->success()
                    ->send();
            });
    }
}
