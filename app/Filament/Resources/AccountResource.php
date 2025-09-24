<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountResource\Pages;
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
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Model;

class AccountResource extends Resource
{
    protected static ?string $model = Account::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Accounts';
    protected static ?string $modelLabel = 'Account';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')
                ->relationship('user', 'name')
                ->live()
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
            Forms\Components\Select::make('type')
                ->options(config('accounts.types'))
                ->default('Classic')
                ->live()
                ->required(),

            // Currency: editable in admin
            Forms\Components\Select::make('currency')
                ->label('Currency')
                ->options(
                    collect(config('currencies.allowed', []))
                        ->filter(fn ($c) => is_string($c) && strlen($c) <= 3)
                        ->mapWithKeys(fn ($c) => [$c => $c])
                        ->all()
                )
                ->default(function (\Filament\Forms\Get $get) {
                    $uid = $get('user_id');
                    return \App\Models\User::find($uid)?->currency ?? (config('currencies.default') ?? 'EUR');
                })
                ->required()
                ->live(),

            Forms\Components\TextInput::make('balance')
                ->label('Balance')
                ->numeric()
                ->step(0.01)
                ->suffix(fn (\Filament\Forms\Get $get) => $get('currency') ?? (config('currencies.default') ?? 'EUR'))
                ->reactive()
                ->required(),

            // Removed Name field as requested; using Account number only
            Forms\Components\TextInput::make('bank')->label('Bank')->nullable(),
            Forms\Components\TextInput::make('organization')->label('Organization')->required(),
            Forms\Components\TextInput::make('beneficiary')->label('Beneficiary')->nullable(),
            Forms\Components\TextInput::make('investment_control')->label('Investment Control')->nullable(),
            Forms\Components\TextInput::make('client_initials')->label('Client Initials')->required(),
            Forms\Components\TextInput::make('broker_initials')->label('Broker Initials')->required(),
            Forms\Components\DatePicker::make('term')->label('Expiration Date')->required(),
            Forms\Components\Select::make('status')
                ->options([
                    'Active' => 'Active',
                    'Hold' => 'Hold',
                    'Blocked' => 'Blocked',
                ])
                ->default('Active')
                ->required(),

            Forms\Components\Toggle::make('is_default')
                ->label('Primary (show first)')
                ->helperText('Mark as the main account shown first in the client area')
                ->default(false),

            // Allow admin to edit creation date/time
            Forms\Components\DateTimePicker::make('created_at')
                ->label('Created at')
                ->native(false)
                ->displayFormat('d.m.Y H:i')
                ->seconds(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('user.name')->sortable(),
            Tables\Columns\TextColumn::make('number')
                ->label('Account number')
                ->searchable(),
            Tables\Columns\TextColumn::make('type')
                ->label('Type')
                ->formatStateUsing(fn (string $state): string => (config('accounts.types')[$state] ?? $state))
                ->badge(),
            Tables\Columns\TextColumn::make('balance')
                ->label('Balance')
                ->sortable()
                ->formatStateUsing(fn ($state, $record) => number_format((float) $state, 2) . ' ' . ($record->currency ?? 'EUR')),
            Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                'Active' => 'success', 'Hold' => 'warning', 'Blocked' => 'danger',
                default => 'gray',
            }),
        ])->actions([Tables\Actions\EditAction::make()])->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }
    
    public static function getRelations(): array
    {
        return [];
    }
    
    public static function getPages(): array
    {
        return ['index' => Pages\ListAccounts::route('/'), 'create' => Pages\CreateAccount::route('/create'), 'edit' => Pages\EditAccount::route('/{record}/edit')];
    }
}
