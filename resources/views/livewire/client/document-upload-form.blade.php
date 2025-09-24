<div class="dashboard-widget">
    <div class="dashboard-widget__header">
        <h3>{{ __('Upload Document') }}</h3>
    </div>

    @if ($successMessage)
        <div class="dashboard-alert dashboard-alert--success" role="alert">
            {{ $successMessage }}
        </div>
    @endif

    <form wire:submit.prevent="submit" class="dashboard-form" enctype="multipart/form-data">
        <div class="field">
            <label for="document_type">{{ __('Document Type') }}</label>
            <select id="document_type" wire:model="document_type">
                <option value="passport">{{ __('Passport') }}</option>
                <option value="utility_bill">{{ __('Utility bill') }}</option>
                <option value="other">{{ __('Other') }}</option>
            </select>
            @error('document_type') <span class="error-message">{{ $message }}</span> @enderror
        </div>

        <div class="field">
            <label for="file">{{ __('File') }}</label>
            <input type="file" id="file" wire:model="file" accept=".pdf,.png,.jpg,.jpeg">
            <div wire:loading wire:target="file" class="dashboard-hint">{{ __('Uploading...') }}</div>
            @error('file') <span class="error-message">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="btn btn--md">
            {{ __('Upload') }}
        </button>
    </form>
</div>
