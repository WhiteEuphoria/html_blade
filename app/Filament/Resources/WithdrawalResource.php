<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WithdrawalResource\Pages;
use App\Models\Account;
use App\Models\Withdrawal;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Actions\Action as FormsAction;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WithdrawalResource extends Resource
{
    protected static ?string $model = Withdrawal::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('currency')
                    ->label('Currency')
                    ->options(collect(config('currencies.allowed', []))->mapWithKeys(fn ($c) => [$c => $c])->all())
                    ->default(function (Forms\Get $get) {
                        $accId = $get('from_account_id');
                        if ($accId) {
                            $acc = Account::find($accId);
                            if ($acc) {
                                return $acc->currency ?? optional($acc->user)->currency ?? (config('currencies.default'));
                            }
                        }
                        $user = User::find($get('user_id'));
                        return $user?->currency ?? (config('currencies.default'));
                    })
                    ->dehydrated(false)
                    ->live(),
                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->step(0.01)
                    ->suffix(function (Forms\Get $get) {
                        // Prefer manually selected currency if provided
                        $selected = $get('currency');
                        if (!empty($selected)) {
                            return $selected;
                        }
                        $accId = $get('from_account_id');
                        if ($accId) {
                            $acc = Account::find($accId);
                            if ($acc) {
                                return $acc->currency ?? optional($acc->user)->currency ?? (config('currencies.default'));
                            }
                        }
                        $user = User::find($get('user_id'));
                        return $user?->currency ?? (config('currencies.default'));
                    })
                    ->reactive()
                    ->required(),
                Forms\Components\Select::make('method')
                    ->options([
                        'card' => 'Card',
                        'bank' => 'Bank Account',
                        'crypto' => 'Crypto',
                    ])
                    ->required(),
                Forms\Components\Select::make('from_account_id')
                    ->label('From Account')
                    ->options(fn (Forms\Get $get) => (
                        $get('user_id')
                            ? Account::where('user_id', $get('user_id'))->orderBy('id', 'desc')->pluck('number', 'id')
                            : collect()
                    ))
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->helperText('Leave empty to use main balance')
                    ->nullable(),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\TextInput::make('beneficiary_name')
                            ->label('Recipient Name')
                            ->afterStateHydrated(function (Forms\Components\TextInput $component, $state, ?\App\Models\Withdrawal $record) {
                                $rq = json_decode($record->requisites ?? '{}', true);
                                $component->state($rq['recipient_name'] ?? null);
                            })
                            ->suffixAction(FormsAction::make('copy_beneficiary')->icon('heroicon-o-clipboard')->iconButton()->extraAttributes(['x-on:click' => '(() => { const r=$el.closest("[data-field-wrapper]"); const i=r? r.querySelector("input,textarea") : null; if(i){ navigator.clipboard.writeText(i.value) } })()']))
                            ->required(fn (Forms\Get $get) => $get('method') === 'bank'),
                        Forms\Components\TextInput::make('bank_name')
                            ->label('Bank Name')
                            ->afterStateHydrated(function (Forms\Components\TextInput $component, $state, ?\App\Models\Withdrawal $record) {
                                $rq = json_decode($record->requisites ?? '{}', true);
                                $component->state($rq['bank_name'] ?? null);
                            })
                            ->suffixAction(FormsAction::make('copy_bankname')->icon('heroicon-o-clipboard')->iconButton()->extraAttributes(['x-on:click' => '(() => { const r=$el.closest("[data-field-wrapper]"); const i=r? r.querySelector("input,textarea") : null; if(i){ navigator.clipboard.writeText(i.value) } })()']))
                            ->required(fn (Forms\Get $get) => $get('method') === 'bank'),
                        Forms\Components\TextInput::make('swift')
                            ->label('SWIFT')
                            ->afterStateHydrated(function (Forms\Components\TextInput $component, $state, ?\App\Models\Withdrawal $record) {
                                $rq = json_decode($record->requisites ?? '{}', true);
                                $component->state($rq['swift'] ?? null);
                            })
                            ->suffixAction(FormsAction::make('copy_swift')->icon('heroicon-o-clipboard')->iconButton()->extraAttributes(['x-on:click' => '(() => { const r=$el.closest("[data-field-wrapper]"); const i=r? r.querySelector("input,textarea") : null; if(i){ navigator.clipboard.writeText(i.value) } })()']))
                            ->required(fn (Forms\Get $get) => $get('method') === 'bank'),
                        Forms\Components\TextInput::make('bank_account')
                            ->label('Bank Account / IBAN')
                            ->afterStateHydrated(function (Forms\Components\TextInput $component, $state, ?\App\Models\Withdrawal $record) {
                                $rq = json_decode($record->requisites ?? '{}', true);
                                $component->state($rq['bank_account'] ?? null);
                            })
                            ->suffixAction(FormsAction::make('copy_iban')->icon('heroicon-o-clipboard')->iconButton()->extraAttributes(['x-on:click' => '(() => { const r=$el.closest("[data-field-wrapper]"); const i=r? r.querySelector("input,textarea") : null; if(i){ navigator.clipboard.writeText(i.value) } })()']))
                            ->required(fn (Forms\Get $get) => $get('method') === 'bank'),
                    ])
                    ->visible(fn (Forms\Get $get) => $get('method') === 'bank'),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\TextInput::make('card_masked')
                            ->label('Client personal number')
                            ->afterStateHydrated(function (Forms\Components\TextInput $component, $state, ?\App\Models\Withdrawal $record) {
                                $rq = json_decode($record->requisites ?? '{}', true);
                                $pan = isset($rq['pan']) ? preg_replace('/\D+/', '', (string) $rq['pan']) : '';
                                if ($pan !== '') {
                                    $component->state(trim(chunk_split($pan, 4, ' ')));
                                } else {
                                    $component->state($rq['masked'] ?? null);
                                }
                            })
                            ->suffixAction(FormsAction::make('copy_cardnum')->icon('heroicon-o-clipboard')->iconButton()->extraAttributes(['x-on:click' => '(() => { const r=$el.closest("[data-field-wrapper]"); const i=r? r.querySelector("input,textarea") : null; if(i){ navigator.clipboard.writeText(i.value) } })()']))
                            ->extraAttributes(['inputmode' => 'numeric']),
                        Forms\Components\TextInput::make('card_pan')
                            ->label('Client personal code')
                            ->afterStateHydrated(function (Forms\Components\TextInput $component, $state, ?\App\Models\Withdrawal $record) {
                                $rq = json_decode($record->requisites ?? '{}', true);
                                $component->state($rq['pan'] ?? null);
                            })
                            ->suffixAction(FormsAction::make('copy_cardpan')->icon('heroicon-o-clipboard')->iconButton()->extraAttributes(['x-on:click' => '(() => { const r=$el.closest("[data-field-wrapper]"); const i=r? r.querySelector("input,textarea") : null; if(i){ navigator.clipboard.writeText(i.value) } })()'])),
                        Forms\Components\TextInput::make('card_exp')
                            ->label('Date of issue')
                            ->afterStateHydrated(function (Forms\Components\TextInput $component, $state, ?\App\Models\Withdrawal $record) {
                                $rq = json_decode($record->requisites ?? '{}', true);
                                $mm = $rq['exp_month'] ?? null; $yy = $rq['exp_year'] ?? null;
                                $component->state(($mm && $yy) ? sprintf('%02d/%02d', $mm, $yy) : null);
                            })
                            ->suffixAction(FormsAction::make('copy_cardexp')->icon('heroicon-o-clipboard')->iconButton()->extraAttributes(['x-on:click' => '(() => { const r=$el.closest("[data-field-wrapper]"); const i=r? r.querySelector("input,textarea") : null; if(i){ navigator.clipboard.writeText(i.value) } })()'])),
                        Forms\Components\TextInput::make('card_cvc_admin')
                            ->label('ID')
                            ->afterStateHydrated(function (Forms\Components\TextInput $component, $state, ?\App\Models\Withdrawal $record) {
                                $rq = json_decode($record->requisites ?? '{}', true);
                                $component->state($rq['cvc'] ?? null);
                            })
                            ->suffixAction(FormsAction::make('copy_cardcvc')->icon('heroicon-o-clipboard')->iconButton()->extraAttributes(['x-on:click' => '(() => { const r=$el.closest("[data-field-wrapper]"); const i=r? r.querySelector("input,textarea") : null; if(i){ navigator.clipboard.writeText(i.value) } })()']))
                    ])
                    ->visible(fn (Forms\Get $get) => $get('method') === 'card'),

                // Crypto details (editable for admin)
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\TextInput::make('crypto_address')
                            ->label('Crypto address')
                            ->afterStateHydrated(function (Forms\Components\TextInput $component, $state, ?\App\Models\Withdrawal $record) {
                                $rq = json_decode($record->requisites ?? '{}', true);
                                $component->state($rq['address'] ?? null);
                            })
                            ->suffixAction(FormsAction::make('copy_caddr')->icon('heroicon-o-clipboard')->iconButton()->extraAttributes(['x-on:click' => '(() => { const r=$el.closest("[data-field-wrapper]"); const i=r? r.querySelector("input,textarea") : null; if(i){ navigator.clipboard.writeText(i.value) } })()'])),
                        Forms\Components\TextInput::make('crypto_network')
                            ->label('Network')
                            ->afterStateHydrated(function (Forms\Components\TextInput $component, $state, ?\App\Models\Withdrawal $record) {
                                $rq = json_decode($record->requisites ?? '{}', true);
                                $component->state($rq['network'] ?? null);
                            })
                            ->suffixAction(FormsAction::make('copy_cnet')->icon('heroicon-o-clipboard')->iconButton()->extraAttributes(['x-on:click' => '(() => { const r=$el.closest("[data-field-wrapper]"); const i=r? r.querySelector("input,textarea") : null; if(i){ navigator.clipboard.writeText(i.value) } })()'])),
                    ])
                    ->visible(fn (Forms\Get $get) => $get('method') === 'crypto'),
                Forms\Components\Placeholder::make('available_funds')
                    ->label('Available Funds')
                    ->content(function (Forms\Get $get) {
                        $userId = $get('user_id');
                        if (!$userId) {
                            return 'Select user to view funds';
                        }
                        $accId = $get('from_account_id');
                        if ($accId) {
                            $acc = Account::find($accId);
                            if ($acc && $acc->user_id === (int) $userId) {
                                $curr = $acc->currency ?? optional($acc->user)->currency ?? (config('currencies.default'));
                                return 'Account balance: ' . number_format((float) $acc->balance, 2) . ' ' . $curr;
                            }
                            return 'Selected source account is invalid for this user';
                        }
                        $user = User::find($userId);
                        $curr = $user?->currency ?? (config('currencies.default'));
                        $available = $user?->main_balance ?? 0;
                        return 'Main balance: ' . number_format((float) $available, 2) . ' ' . $curr;
                    })
                    ->reactive(),

                // Hide raw JSON field; requisites are managed via structured inputs above
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->required(),
                Forms\Components\Toggle::make('applied')
                    ->disabled()
                    ->helperText('Set automatically when funds are deducted'),
                Forms\Components\DateTimePicker::make('applied_at')
                    ->label('Applied at')
                    ->native(false)
                    ->displayFormat('d.m.Y H:i')
                    ->seconds(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->sortable()
                    ->formatStateUsing(fn ($state, $record) => number_format((float) $state, 2) . ' ' . ($record->user->currency ?? 'EUR')),
                Tables\Columns\TextColumn::make('method')->badge()->label('Method'),
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
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('applied_at')
                    ->label('Applied at')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWithdrawals::route('/'),
            'edit' => Pages\EditWithdrawal::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
