<div class="dashboard-widget dashboard-widget--chat"
     wire:poll.5s="loadMessages"
     x-data="{ scroll(){ this.$nextTick(() => { const el = $refs.list; if (el) { el.scrollTop = el.scrollHeight } }) }, init(){ this.scroll(); this.$watch('$wire.messages', () => this.scroll()); } }">
    <div class="dashboard-widget__header">
        <h3>{{ __('Assistant') }}</h3>
    </div>

    <div class="chat-window" x-ref="list" @scroll="atBottom = ($refs.list.scrollTop + $refs.list.clientHeight) >= ($refs.list.scrollHeight - 4)">
        @foreach ($messages as $m)
            @php($isYou = (($m['direction'] ?? 'outbound') === 'outbound'))
            <div class="chat-row" x-data x-init="$el.classList.add('chat-row--hidden'); setTimeout(()=>{$el.classList.remove('chat-row--hidden')}, 0)">
                <div class="chat-bubble {{ $isYou ? 'chat-bubble--out' : 'chat-bubble--in' }}">
                    <div class="chat-bubble__meta">
                        <span>{{ $isYou ? __('You') : __('Assistant') }}</span>
                        <time>{{ $m['created_at'] ?? '' }}</time>
                    </div>
                    <div class="chat-bubble__message">{{ $m['message'] ?? '' }}</div>
                </div>
            </div>
        @endforeach

        <button x-show="!atBottom" @click="scroll()" class="chat-window__badge" x-transition.opacity>
            {{ __('New') }}
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path fill-rule="evenodd" d="M2.25 12a.75.75 0 0 1 .75-.75h15.19l-5.22-5.22a.75.75 0 1 1 1.06-1.06l6.5 6.5a.75.75 0 0 1 0 1.06l-6.5 6.5a.75.75 0 1 1-1.06-1.06l5.22-5.22H3a.75.75 0 0 1-.75-.75z" clip-rule="evenodd" />
            </svg>
        </button>
        @if (count($messages) === 0)
            <div class="chat-empty">{{ __('No messages yet. Ask us anything!') }}</div>
        @endif
    </div>
    <form wire:submit.prevent="send" class="chat-form" @submit.prevent="scroll()">
        <input type="text" wire:model.defer="newMessage" class="chat-input" placeholder="{{ __('Write a message...') }}">
        <button type="submit" aria-label="{{ __('Send') }}" class="chat-submit">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path fill-rule="evenodd" d="M2.25 12a.75.75 0 0 1 .75-.75h15.19l-5.22-5.22a.75.75 0 1 1 1.06-1.06l6.5 6.5a.75.75 0 0 1 0 1.06l-6.5 6.5a.75.75 0 1 1-1.06-1.06l5.22-5.22H3a.75.75 0 0 1-.75-.75z" clip-rule="evenodd" />
            </svg>
        </button>
    </form>
</div>
