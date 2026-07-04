@php
    $activeLanguages = \App\Models\Language::getActive();
    $currentLocale = app()->getLocale();
    $currentLang = $activeLanguages->firstWhere('code', $currentLocale);

    $isAdmin = auth()->check() && collect(auth()->user()?->getRoleNames() ?? [])
        ->map(fn($r) => strtoupper($r))->intersect(['ADMIN', 'SUPERADMIN'])->isNotEmpty();
@endphp
<header class="h-16 bg-white border-b border-slate-200 flex items-center gap-3 px-4 sm:px-6 sticky top-0 z-20">
    <button type="button" @click="mobileMenu = true"
            class="lg:hidden btn-ghost p-2 rounded-lg" title="{{ __('Menu') }}" aria-label="Open menu">
        <x-icon name="menu" class="h-5 w-5" />
    </button>

    {{-- Academic year / semester selector (admin) --}}
    @if($isAdmin)
        <button type="button" onclick="document.getElementById('academicYearModal').style.display='flex'"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border text-xs font-bold transition
                       {{ session('current_academic_year_id') ? 'bg-brand-50 text-brand-700 border-brand-200 hover:bg-brand-100' : 'bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-100' }}">
            <x-icon name="academic" class="h-3.5 w-3.5" />
            @if(session('current_academic_year_id') && session('current_semester_id'))
                @php
                    $sessionYear = \App\Models\AcademicYear::find(session('current_academic_year_id'));
                    $sessionSemester = \App\Models\Semester::find(session('current_semester_id'));
                @endphp
                {{ $sessionYear->year ?? '' }}/{{ $sessionSemester->semester_number ?? '' }}
            @else
                {{ __('Select Academic Year') }}
            @endif
        </button>
    @endif

    <div class="flex items-center gap-3 ml-auto">
        {{-- Language switcher --}}
        @if($activeLanguages->count() > 1)
        <x-dropdown align="right" width="48">
            <x-slot name="trigger">
                <button type="button" class="btn-secondary px-3 py-1.5 rounded-full text-xs">
                    <span class="text-base leading-none">{{ $currentLang->flag ?? '' }}</span>
                    <span class="hidden sm:inline">{{ $currentLang->native_name ?? strtoupper($currentLocale) }}</span>
                    <x-icon name="chevron-down" class="h-3.5 w-3.5 text-slate-400" />
                </button>
            </x-slot>
            <x-slot name="content">
                @foreach($activeLanguages as $lang)
                    <a href="{{ route('locale.switch', $lang->code) }}"
                       class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium transition-colors {{ $lang->code === $currentLocale ? 'bg-brand-50 text-brand-700' : 'text-slate-700 hover:bg-slate-50' }}">
                        <span class="text-lg">{{ $lang->flag }}</span>
                        <span>{{ $lang->native_name }}</span>
                        @if($lang->code === $currentLocale)
                            <x-icon name="check" class="h-4 w-4 ml-auto text-brand-500" />
                        @endif
                    </a>
                @endforeach
            </x-slot>
        </x-dropdown>
        @endif

        <a href="{{ url('/') }}" target="_blank" rel="noopener"
           class="btn-ghost p-2 rounded-lg" title="{{ __('View website') }}">
            <x-icon name="globe" class="h-5 w-5" />
        </a>

        @auth
            <x-dropdown align="right" width="56">
                <x-slot name="trigger">
                    <button type="button"
                            class="flex items-center gap-2 sm:gap-3 pl-3 border-l border-slate-200 focus:outline-none hover:bg-slate-50 rounded-lg py-1 pr-2 transition">
                        <div class="text-right leading-tight hidden sm:block">
                            <div class="text-sm font-medium text-slate-900">{{ auth()->user()->name }}</div>
                            <div class="text-xs text-slate-500">{{ auth()->user()->email }}</div>
                        </div>
                        <div class="h-9 w-9 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-semibold text-sm">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <x-icon name="chevron-down" class="h-4 w-4 text-slate-400" />
                    </button>
                </x-slot>
                <x-slot name="content">
                    <div class="px-4 py-3 border-b border-slate-100">
                        <div class="text-sm font-medium text-slate-900 truncate">{{ auth()->user()->name }}</div>
                        <div class="text-xs text-slate-500 truncate">{{ auth()->user()->email }}</div>
                    </div>
                    <a href="{{ route('profile.index') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">
                        <x-icon name="user" class="h-4 w-4" />
                        <span>{{ __('Profile') }}</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">
                            <x-icon name="logout" class="h-4 w-4" />
                            <span>{{ __('Sign out') }}</span>
                        </button>
                    </form>
                </x-slot>
            </x-dropdown>
        @endauth
    </div>
</header>
