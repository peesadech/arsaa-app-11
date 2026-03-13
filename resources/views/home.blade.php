@extends('layouts.app')

@push('styles')
<style>
    a.group.block:hover {
        text-decoration: none !important;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50/50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto space-y-8">

        <!-- Content Area -->
        <div class="text-center py-12">
            <p class="text-gray-400 text-lg font-medium">{{ __('Welcome to your dashboard') }}</p>
        </div>

        <!-- System Status Footer -->
        <div class="pt-12 border-t border-gray-100 flex flex-col md:flex-row items-center justify-between gap-4 text-xs font-bold text-gray-400 uppercase tracking-widest">
            <div class="flex items-center">
                <span class="w-2 h-2 rounded-full bg-emerald-500 mr-2 shadow-sm shadow-emerald-200 animate-pulse"></span>
                {{ __('All Systems Operational') }}
            </div>
            <div class="flex items-center space-x-6">
                <span class="hover:text-gray-600 cursor-default transition-colors">{{ __('Premium Admin Console') }}</span>
                <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                <span class="hover:text-gray-600 cursor-default transition-colors">v2.0.4</span>
            </div>
        </div>

    </div>
</div>
@endsection
