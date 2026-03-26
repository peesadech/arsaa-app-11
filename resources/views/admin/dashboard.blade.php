@extends('layouts.app')

@push('styles')
<style>
    a.group.block:hover {
        text-decoration: none !important;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50/50 dark:bg-[#18191a] py-8 px-4 sm:px-6 lg:px-8 transition-colors duration-300">
    <div class="max-w-7xl mx-auto space-y-8">


      
        <!-- Footer -->
        <div class="pt-12 border-t border-gray-100 dark:border-[#3a3b3c] flex flex-col md:flex-row items-center justify-between gap-4 text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">
            <div class="flex items-center">
                <span class="w-2 h-2 rounded-full bg-emerald-500 mr-2 shadow-sm shadow-emerald-200 animate-pulse"></span>
                {{ __('All Systems Operational') }}
            </div>
            <div class="flex items-center space-x-6">
                <span>{{ __('Setting') }}</span>
                <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                <span>v2.0.4</span>
            </div>
        </div>

    </div>
</div>
@endsection
