<x-layouts.admin :header="__('Dashboard')" :subheader="__('Welcome to your dashboard')">
    <div class="card">
        <div class="card-body text-center py-16">
            <p class="text-slate-500 text-lg font-medium">{{ __('Welcome to your dashboard') }}</p>
        </div>
    </div>

    <div class="mt-8 pt-6 border-t border-slate-100 flex flex-col md:flex-row items-center justify-between gap-4 text-xs font-medium text-slate-400 uppercase tracking-widest">
        <div class="flex items-center">
            <span class="w-2 h-2 rounded-full bg-emerald-500 mr-2 animate-pulse"></span>
            {{ __('All Systems Operational') }}
        </div>
        <div class="flex items-center gap-6">
            <span>{{ __('Premium Admin Console') }}</span>
            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
            <span>v2.0.4</span>
        </div>
    </div>
</x-layouts.admin>
