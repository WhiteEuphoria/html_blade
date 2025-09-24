<div class="dashboard-widget">
    <div class="dashboard-widget__header">
        <h3>{{ __('Select Currency') }}</h3>
    </div>
    <div class="dashboard-form">
        <div class="field">
            <label for="currency">{{ __('Currency') }}</label>
            <select id="currency" wire:model="currency">
                @foreach($options as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
            @error('currency') <span class="error-message">{{ $message }}</span> @enderror
        </div>
        <button wire:click="save" type="button" class="btn btn--md">
            {{ __('Save') }}
        </button>
    </div>
</div>
