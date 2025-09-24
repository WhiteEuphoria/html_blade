@php
    $currency = auth()->user()->currency ?? 'EUR';
    $formatted = number_format($balance, 2, '.', ' ');
@endphp

<div class="user-info__text-lg">
    <span>{{ $currency }} {{ $formatted }}</span>
</div>
