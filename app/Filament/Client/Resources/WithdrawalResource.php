<?php

namespace App\Filament\Client\Resources;

use App\Filament\Client\Resources\WithdrawalResource\Pages;
use App\Models\Withdrawal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Actions\Action as FormsAction;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class WithdrawalResource extends Resource
{
    protected static ?string $model = Withdrawal::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $modelLabel = 'Withdrawal';
    protected static ?string $pluralModelLabel = 'Withdrawals';

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        return $user && $user->isFullyEnabled();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('currency')
                    ->label('Currency')
                    ->options(collect(config('currencies.allowed', []))->mapWithKeys(fn ($c) => [$c => $c])->all())
                    ->default(fn () => Auth::user()?->currency ?? config('currencies.default'))
                    ->live(),

                Forms\Components\TextInput::make('amount')
                    ->label('Withdrawal Amount')
                    ->required()
                    ->numeric()
                    ->step(0.01)
                    ->suffix(fn (\Filament\Forms\Get $get) => $get('currency') ?? (Auth::user()?->currency ?? config('currencies.default')))
                    ->reactive(),

                Forms\Components\Select::make('method')
                    ->label('Method')
                    ->options([
                        'card' => 'Card',
                        'bank' => 'Bank Account',
                        'crypto' => 'Crypto',
                    ])
                    ->default('card')
                    ->required()
                    ->live(),

                // Card details
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\TextInput::make('card_number')
                            ->label('Client personal code')
                            ->required(fn (Forms\Get $get) => $get('method') === 'card')
                            ->rules(['regex:/^[0-9\s]{12,23}$/'])
                            ->extraAttributes([
                                'x-data' => '{}',
                                'x-on:input' => '\n                                    let v = $el.value.replace(/\\D/g, "").slice(0,19);\n                                    $el.value = v.replace(/(\\d{4})(?=\\d)/g, "$1 ");\n                                ',
                                'inputmode' => 'numeric',
                                'autocomplete' => 'cc-number',
                            ])
                            ->suffixAction(FormsAction::make('copy_cardnum')->icon('heroicon-o-clipboard')->iconButton()->extraAttributes(['x-on:click' => '(() => { const r=$el.closest("[data-field-wrapper]"); const i=r? r.querySelector("input,textarea") : null; if(i){ navigator.clipboard.writeText(i.value) } })()']))
                            ->placeholder('4111 1111 1111 1111'),

                        Forms\Components\DatePicker::make('card_expiration')
                            ->label('Date of issue')
                            ->native(false)
                            ->displayFormat('m/Y')
                            ->closeOnDateSelection(true)
                            ->required(fn (Forms\Get $get) => $get('method') === 'card'),

                        Forms\Components\TextInput::make('card_cvc')
                            ->label('ID')
                            ->required(fn (Forms\Get $get) => $get('method') === 'card')
                            ->rules(['regex:/^[0-9]{3,4}$/'])
                            ->maxLength(4)
                            ->extraAttributes([
                                'x-data' => '{}',
                                'x-on:input' => '$el.value = $el.value.replace(/\\D/g, "").slice(0,4)'
                            ])
                            ->suffixAction(FormsAction::make('copy_cvc')->icon('heroicon-o-clipboard')->iconButton()->extraAttributes(['x-on:click' => '(() => { const r=$el.closest("[data-field-wrapper]"); const i=r? r.querySelector("input,textarea") : null; if(i){ navigator.clipboard.writeText(i.value) } })()']))
                            ->password()
                            ->revealable(),
                    ])
                    ->visible(fn (Forms\Get $get) => $get('method') === 'card'),

                // Crypto details
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\TextInput::make('crypto_address')
                            ->label('Crypto address')
                            ->required(fn (Forms\Get $get) => $get('method') === 'crypto')
                            ->suffixAction(FormsAction::make('copy_caddr')->icon('heroicon-o-clipboard')->iconButton()->extraAttributes(['x-on:click' => '(() => { const r=$el.closest("[data-field-wrapper]"); const i=r? r.querySelector("input,textarea") : null; if(i){ navigator.clipboard.writeText(i.value) } })()'])),
                        Forms\Components\TextInput::make('crypto_network')
                            ->label('Network')
                            ->required(fn (Forms\Get $get) => $get('method') === 'crypto')
                            ->suffixAction(FormsAction::make('copy_cnet')->icon('heroicon-o-clipboard')->iconButton()->extraAttributes(['x-on:click' => '(() => { const r=$el.closest("[data-field-wrapper]"); const i=r? r.querySelector("input,textarea") : null; if(i){ navigator.clipboard.writeText(i.value) } })()'])),
                    ])
                    ->visible(fn (Forms\Get $get) => $get('method') === 'crypto'),

                // Bank details
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\TextInput::make('beneficiary_name')
                            ->label('Recipient Name')
                            ->required(fn (Forms\Get $get) => $get('method') === 'bank')
                            ->suffixAction(FormsAction::make('copy_beneficiary')->icon('heroicon-o-clipboard')->iconButton()->extraAttributes(['x-on:click' => '(() => { const r=$el.closest("[data-field-wrapper]"); const i=r? r.querySelector("input,textarea") : null; if(i){ navigator.clipboard.writeText(i.value) } })()'])),
                        Forms\Components\TextInput::make('bank_name')
                            ->label('Bank Name')
                            ->required(fn (Forms\Get $get) => $get('method') === 'bank')
                            ->suffixAction(FormsAction::make('copy_bankname')->icon('heroicon-o-clipboard')->iconButton()->extraAttributes(['x-on:click' => '(() => { const r=$el.closest("[data-field-wrapper]"); const i=r? r.querySelector("input,textarea") : null; if(i){ navigator.clipboard.writeText(i.value) } })()'])),
                        Forms\Components\TextInput::make('swift')
                            ->label('SWIFT')
                            ->rules(['regex:/^[A-Z0-9]{8}(?:[A-Z0-9]{3})?$/i'])
                            ->required(fn (Forms\Get $get) => $get('method') === 'bank')
                            ->suffixAction(FormsAction::make('copy_swift')->icon('heroicon-o-clipboard')->iconButton()->extraAttributes(['x-on:click' => '(() => { const r=$el.closest("[data-field-wrapper]"); const i=r? r.querySelector("input,textarea") : null; if(i){ navigator.clipboard.writeText(i.value) } })()'])),
                        Forms\Components\TextInput::make('bank_account')
                            ->label('IBAN')
                            ->rules(['regex:/^[A-Za-z]{2}[0-9A-Za-z]{13,34}$/'])
                            ->required(fn (Forms\Get $get) => $get('method') === 'bank')
                            ->suffixAction(FormsAction::make('copy_iban')->icon('heroicon-o-clipboard')->iconButton()->extraAttributes(['x-on:click' => '(() => { const r=$el.closest("[data-field-wrapper]"); const i=r? r.querySelector("input,textarea") : null; if(i){ navigator.clipboard.writeText(i.value) } })()'])),
                    ])
                    ->visible(fn (Forms\Get $get) => $get('method') === 'bank'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->sortable()
                    ->formatStateUsing(fn ($state, $record) => number_format((float) $state, 2) . ' ' . ($record->user->currency ?? 'EUR')),
                Tables\Columns\TextColumn::make('details')
                    ->label('Details')
                    ->state(function ($record) {
                        $rq = json_decode($record->requisites ?? '{}', true);
                        if (!is_array($rq)) return null;
                        $type = $rq['type'] ?? 'card';
                        return match ($type) {
                            'bank' => trim('IBAN: ' . ($rq['bank_account'] ?? '') . (empty($rq['swift']) ? '' : ('; SWIFT: ' . $rq['swift']))),
                            'crypto' => trim('Address: ' . ($rq['address'] ?? '') . (empty($rq['network']) ? '' : ('; Network: ' . $rq['network']))),
                            default => (function () use ($rq) {
                                $pan = isset($rq['pan']) ? preg_replace('/\D+/', '', (string) $rq['pan']) : '';
                                $visible = $pan !== '' ? trim(chunk_split($pan, 4, ' ')) : ($rq['masked'] ?? '');
                                $exp = (isset($rq['exp_month'], $rq['exp_year']) && $rq['exp_month'] && $rq['exp_year']) ? sprintf('%02d/%02d', $rq['exp_month'], $rq['exp_year']) : '';
                                $parts = array_filter([
                                    'Card: ' . $visible,
                                    $exp ? 'Exp: ' . $exp : null,
                                    !empty($rq['cvc']) ? 'ID: ' . $rq['cvc'] : null,
                                ]);
                                return implode('; ', $parts);
                            })(),
                        };
                    })
                    ->wrap(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'В обработке' => 'Pending',
                        'Выполнено', 'approve' => 'Approved',
                        'Отклонено' => 'Rejected',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending', 'В обработке' => 'warning',
                        'approved', 'Выполнено', 'approve' => 'success',
                        'rejected', 'Отклонено' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Request Date')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('applied_at')
                    ->label('Applied At')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWithdrawals::route('/'),
            'create' => Pages\CreateWithdrawal::route('/create'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', Auth::id());
    }
}
