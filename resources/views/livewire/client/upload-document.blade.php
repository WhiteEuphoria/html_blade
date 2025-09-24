<div class="dashboard-widget">
    <div class="dashboard-widget__header">
        <h3>{{ __('Upload Document') }}</h3>
    </div>

    @if (session()->has('message'))
        <div class="dashboard-alert dashboard-alert--success" role="alert">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit.prevent="save" class="dashboard-form" enctype="multipart/form-data">
        <div class="field">
            <label for="file">{{ __('File') }}</label>
            <input type="file" id="file" wire:model="file" accept=".pdf,.png,.jpg,.jpeg,.gif,.webp,.bmp">
            <div wire:loading wire:target="file" class="dashboard-hint">{{ __('Uploading...') }}</div>
            @error('file') <span class="error-message">{{ $message }}</span> @enderror
        </div>

        @if ($file)
            @php
                $tmpName = $file->getClientOriginalName();
                $ext = strtolower(pathinfo($tmpName, PATHINFO_EXTENSION));
                $isImg = in_array($ext, ['jpg','jpeg','png','gif','webp','bmp']);
            @endphp
            <div class="document-preview">
                @if ($isImg)
                    <img src="{{ $file->temporaryUrl() }}" alt="Preview">
                @elseif ($ext === 'pdf')
                    <embed src="{{ $file->temporaryUrl() }}#toolbar=0&navpanes=0" type="application/pdf" />
                @else
                    <span>{{ __('Selected:') }} {{ $tmpName }}</span>
                @endif
            </div>
        @endif

        <button type="submit" class="btn btn--md">
            {{ __('Upload') }}
        </button>
    </form>
</div>

<div class="dashboard-widget">
    <div class="dashboard-widget__header">
        <h3>{{ __('Your Documents') }}</h3>
    </div>

    <div class="document-list">
        @forelse ($documents as $document)
            @php
                $extension = strtolower(pathinfo($document->path, PATHINFO_EXTENSION));
                $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp']);
                $url = '/storage/' . ltrim($document->path, '/');
            @endphp
            <div class="document-list__item">
                <div class="document-list__info">
                    <div class="document-list__title">{{ $document->original_name }}</div>
                    <div class="document-list__meta">{{ __('Status:') }} <span>{{ $document->status }}</span></div>
                </div>
                <div class="document-list__preview">
                    @if ($isImage)
                        <img src="{{ $url }}" alt="Preview">
                    @elseif ($extension === 'pdf')
                        <embed src="{{ $url }}#toolbar=0&navpanes=0" type="application/pdf" />
                    @else
                        <a href="{{ $url }}" target="_blank" class="btn btn--light">{{ __('Download') }}</a>
                    @endif
                </div>
            </div>
        @empty
            <p class="dashboard-hint">{{ __('You have not uploaded any documents yet.') }}</p>
        @endforelse
    </div>
</div>
