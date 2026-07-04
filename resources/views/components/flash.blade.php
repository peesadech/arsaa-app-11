@php
    $messages = [];

    if (session()->has('status')) {
        $raw = session('status');
        if (is_string($raw) && trim($raw) !== '') {
            $messages[] = ['type' => 'success', 'message' => $raw];
        }
    }
    foreach (['success' => 'success', 'error' => 'error', 'warning' => 'warning', 'info' => 'info', 'message' => 'info'] as $key => $type) {
        if (session()->has($key)) {
            $val = session($key);
            if (is_string($val) && trim($val) !== '') {
                $messages[] = ['type' => $type, 'message' => $val];
            }
        }
    }
@endphp

@if (count($messages) > 0)
    <div class="fixed bottom-4 right-4 left-4 sm:left-auto sm:max-w-sm z-50 space-y-2 pointer-events-none">
        @foreach ($messages as $m)
            <div x-data="{ show: false }"
                 x-cloak
                 x-show="show"
                 x-init="$nextTick(() => show = true); setTimeout(() => show = false, 3500)"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-x-4"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-x-0"
                 x-transition:leave-end="opacity-0 translate-x-4"
                 role="alert"
                 @class([
                    'pointer-events-auto rounded-xl border shadow-lg px-4 py-3 flex items-start gap-3 backdrop-blur',
                    'bg-emerald-50/95 border-emerald-200 text-emerald-800' => $m['type'] === 'success',
                    'bg-red-50/95 border-red-200 text-red-800'             => $m['type'] === 'error',
                    'bg-amber-50/95 border-amber-200 text-amber-800'       => $m['type'] === 'warning',
                    'bg-sky-50/95 border-sky-200 text-sky-800'             => $m['type'] === 'info',
                 ])>
                <svg class="h-5 w-5 mt-0.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    @if ($m['type'] === 'success')
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    @elseif ($m['type'] === 'error' || $m['type'] === 'warning')
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/>
                    @else
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    @endif
                </svg>

                <span class="flex-1 text-sm leading-snug">{{ $m['message'] }}</span>

                <button type="button" @click="show = false"
                        class="opacity-60 hover:opacity-100 transition focus:outline-none focus:opacity-100"
                        aria-label="Dismiss">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        @endforeach
    </div>
@endif
