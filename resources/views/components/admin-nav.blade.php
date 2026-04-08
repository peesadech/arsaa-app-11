@php
    $setting = \App\Models\Setting::first() ?? new \App\Models\Setting(['theme' => 'light']);
    $theme = $setting->theme ?? 'light';
    $isDark = $theme === 'dark';

    $navItems = [

    ];
@endphp

@if(count($navItems))
<div class="bg-white dark:bg-[#242526] border-b border-gray-100 dark:border-[#3a3b3c] shadow-sm">
    <div class="container mx-auto px-4">
        <div class="flex items-center space-x-1 overflow-x-auto py-2 scrollbar-hide">
            @foreach($navItems as $item)
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider whitespace-nowrap transition-all duration-200
                   {{ request()->routeIs($item['route']) || request()->routeIs(str_replace('.index', '.*', $item['route']))
                       ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800'
                       : 'text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-[#3a3b3c] hover:text-gray-700 dark:hover:text-gray-300 border border-transparent' }}">
                    <i class="{{ $item['icon'] }} text-[10px]"></i>
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>
    </div>
</div>
@endif
