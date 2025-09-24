<?php

namespace App\Filament\Client\Resources;

use App\Filament\Client\Resources\AccountResource\Pages;
use App\Models\Account;
use App\Support\AccountNumber;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Forms\Components\Actions\Action as FormsAction;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Model;

class AccountResource extends Resource
{
    protected static ?string $model = Account::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Accounts';
    protected static ?string $modelLabel = 'Account';
    protected static ?string $pluralModelLabel = 'Accounts';
    protected static ?string $slug = 'accounts';

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        return $user && $user->isFullyEnabled();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('type')
                ->label('Type')
                ->options(config('accounts.types'))
                ->default('Classic')
                ->required(),

            Forms\Components\TextInput::make('number')
                ->label('Account number')
                ->required()
                ->default(fn () => AccountNumber::generate())
                ->rule(fn (?Model $record) => Rule::unique('accounts', 'number')->ignore($record))
                ->suffixAction(
                    FormsAction::make('generate')
                        ->icon('heroicon-o-sparkles')
                        ->label('Generate')
                        ->action(fn (Set $set) => $set('number', AccountNumber::generate()))
                ),

            Forms\Components\TextInput::make('balance')
                ->label('Balance')
                ->numeric()
                ->step(0.01)
                ->suffix(fn () => auth()->user()?->currency ?? config('currencies.default'))
                ->required(),

            // Removed name input per request; use account number only on client side

            Forms\Components\TextInput::make('organization')
                ->label('Organization')
                ->required(),

            Forms\Components\TextInput::make('beneficiary')
                ->label('Beneficiary')
                ->nullable(),

            Forms\Components\TextInput::make('investment_control')
                ->label('Investment Control')
                ->nullable(),

            Forms\Components\TextInput::make('client_initials')
                ->label('Client Initials')
                ->required(),

            Forms\Components\TextInput::make('broker_initials')
                ->label('Broker Initials'),

            Forms\Components\DatePicker::make('term')
                ->label('Expiration Date')
                ->native(false)
                ->nullable(),

            // Allow client to set Status (admin can edit everything in admin panel)
            Forms\Components\Select::make('status')
                ->label('Status')
                ->options([
                    'Pending' => 'Pending',
                    'Active' => 'Active',
                    'Hold' => 'Hold',
                    'Blocked' => 'Blocked',
                ])
                ->default('Pending')
                ->required(),

            // End of client-editable fields reflecting dashboard items
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            // Beneficiary
            Tables\Columns\TextColumn::make('beneficiary')
                ->label('Beneficiary')
                ->searchable(),

            // Amount on the account (balance + currency)
            Tables\Columns\TextColumn::make('balance')
                ->label('Amount')
                ->sortable()
                ->formatStateUsing(function ($state, $record) {
                    $amount = number_format((float) ($state ?? 0), 2);
                    $currency = $record->currency ?: (auth()->user()?->currency ?? 'EUR');
                    return $amount . ' ' . $currency;
                }),

            // Organization name
            Tables\Columns\TextColumn::make('organization')
                ->label('Organization')
                ->searchable(),
        ])
        ->actions([
            Tables\Actions\ViewAction::make(),
        ])
        ->bulkActions([]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', Auth::id());
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccounts::route('/'),
            'view' => Pages\ViewAccount::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();
        return $user ? ($user->accounts()->count() === 0) : false;
    }
}
