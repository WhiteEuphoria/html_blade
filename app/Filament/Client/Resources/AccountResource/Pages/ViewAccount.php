<?php

namespace App\Filament\Client\Resources\AccountResource\Pages;

use App\Filament\Client\Resources\AccountResource;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewAccount extends ViewRecord
{
    protected static string $resource = AccountResource::class;

    protected function getHeaderActions(): array
    {
        // Read-only for clients: no edit/delete
        return [];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('General')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('number')
                            ->label('Account number'),
                        TextEntry::make('type')
                            ->label('Type')
                            ->formatStateUsing(fn ($state) => config('accounts.types')[$state] ?? $state),
                        TextEntry::make('status')
                            ->label('Status'),
                        IconEntry::make('is_default')
                            ->label('Default account')
                            ->boolean(),
                    ]),

                Section::make('Financial')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('balance')
                            ->label('Amount')
                            ->formatStateUsing(function ($state, $record) {
                                $amount = number_format((float) ($state ?? 0), 2);
                                $currency = $record->currency ?: (auth()->user()?->currency ?? 'EUR');
                                return $amount . ' ' . $currency;
                            }),
                        TextEntry::make('currency')
                            ->label('Currency'),
                        TextEntry::make('bank')
                            ->label('Bank')
                            ->placeholder('-'),
                    ]),

                Section::make('Details')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('organization')->label('Organization'),
                        TextEntry::make('beneficiary')->label('Beneficiary')->placeholder('-'),
                        TextEntry::make('investment_control')->label('Investment Control')->placeholder('-'),
                    ]),

                Section::make('Signatures')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('client_initials')->label('Client Initials'),
                        TextEntry::make('broker_initials')->label('Broker Initials')->placeholder('-'),
                    ]),

                Section::make('Dates')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('term')->label('Expiration Date')->date(),
                        TextEntry::make('created_at')->label('Created At')->dateTime(),
                    ]),
            ]);
    }
}
