@extends('layouts.app')
@section('title', __('Личный кабинет'))
@section('content')

<div class="wrapper">
    <header class="header">
        <div class="container">
            <div class="header__inner">
                <a class="header__logo logo" href="{{ url('/') }}">
                    <img alt="logo" src="{{ asset('personal-acc/img/logo.svg') }}">
                </a>
                <div class="header__actions">
                    <a class="btn btn--light btn-support" href="{{ route('filament.client.pages.support-chat') }}">
                        {{ __('Support') }}
                        <span class="btn__icon">
                            <img alt="support" src="{{ asset('personal-acc/img/icons/support.svg') }}">
                        </span>
                    </a>
                    <div class="desktop">
                        <a class="btn" href="{{ route('filament.client.resources.withdrawals.create') }}">
                            <span>{{ __('Withdrawal of funds') }}</span>
                        </a>
                    </div>
                    <div class="mobile">
                        <a class="btn" href="{{ route('filament.client.resources.withdrawals.create') }}">
                            {{ __('Withdrawal') }}
                            <span class="btn__icon">
                                <img alt="withdraw" src="{{ asset('personal-acc/img/icons/withdraw.svg') }}">
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="page">
        <div class="container">
            <div class="dashboard-grid">
                @php
                    $user = auth()->user();
                    $status = $user->verification_status ?? 'pending';
                @endphp
                <div class="dashboard-main">
                    <section class="user-info">
                        <div class="user-info__col">
                            <div class="user-info__title">{{ __('Welcome') }} {{ $user->name }}</div>
                            <div class="user-info__text">{{ $user->email }}</div>
                            <div class="user-info__status user-info__status--verify">
                                {{ ucfirst($status) }}
                            </div>
                        </div>
                        <div class="user-info__col">
                            <div class="user-info__title" style="font-weight: 600;">
                                {{ __('Balance') }}
                                <img alt="wallet" src="{{ asset('personal-acc/img/icons/wallet.svg') }}">
                            </div>
                            @livewire('client.balance-display')
                        </div>
                    </section>

                    @livewire('client.withdrawal-form')

                    @livewire('client.document-upload-form')
                </div>

                <aside class="dashboard-aside">
                    @livewire('client.support-chat')
                </aside>
            </div>
        </div>
    </main>
</div>
@endsection
