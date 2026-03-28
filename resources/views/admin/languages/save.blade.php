@extends('layouts.app')

@php
    $isEdit = isset($language);
    $actionUrl = $isEdit ? route('admin.languages.update', $language->id) : route('admin.languages.store');

    $title = $isEdit ? __('Edit Language') : __('Create New Language');
    $subtitle = $isEdit ? __('Update language details') : __('Language Registration');

    $gradientClass = $isEdit ? 'bg-gradient-to-r from-amber-500 via-orange-500 to-rose-500' : 'bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500';
    $blurClass = $isEdit ? 'bg-amber-500/20' : 'bg-indigo-500/20';
    $iconBgClass = $isEdit ? 'bg-amber-50 border-amber-100' : 'bg-indigo-50 border-indigo-100 shadow-inner';
    $iconClass = $isEdit ? 'fa-edit text-amber-600 rotate-3' : 'fa-plus text-indigo-600 -rotate-3';
    $cardTitle = $isEdit ? __('Edit Language') : __('Language Registration');
    $cardDesc = $isEdit
        ? __('Update language details')
        : __('Define a new language for the system.');

    $focusRing = $isEdit ? 'focus:border-amber-400' : 'focus:border-indigo-500';
    $focusText = $isEdit ? 'group-focus-within:text-amber-500' : 'group-focus-within:text-indigo-500';

    $btnClass = $isEdit
        ? 'bg-amber-500 text-white hover:bg-amber-600 shadow-amber-200'
        : 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-indigo-200';

    $btnText = $isEdit ? __('Save Changes') : __('Create Language');
    $btnIcon = $isEdit ? 'fa-save' : 'fa-check-circle';
@endphp

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-[#18191a] py-12 px-4 sm:px-6 lg:px-8 transition-colors duration-300">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="flex items-center space-x-4 mb-8">
            <a href="{{ route('admin.languages.index') }}"
               class="group flex items-center justify-center w-10 h-10 rounded-xl bg-white dark:bg-[#242526] shadow-sm border border-gray-200 dark:border-[#3a3b3c] text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-300 transition-all duration-200">
                <i class="fas fa-arrow-left group-hover:-translate-x-0.5 transition-transform"></i>
            </a>
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">{{ $title }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium px-1">{{ $subtitle }}</p>
            </div>
        </div>

        <!-- Main Card -->
        <div class="bg-white dark:bg-[#242526] rounded-[2.5rem] shadow-xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-[#3a3b3c] overflow-hidden transform transition-all">
            <div class="h-2 {{ $gradientClass }}"></div>

            <div class="p-8 sm:p-10">
                <!-- Visual Identity -->
                <div class="flex flex-col items-center text-center mb-10">
                    <div class="relative">
                        <div class="absolute inset-0 {{ $blurClass }} blur-2xl rounded-full"></div>
                        <div class="relative w-20 h-20 rounded-2xl flex items-center justify-center mb-4 transform border {{ $iconBgClass }}">
                            <i class="fas {{ $iconClass }} text-3xl"></i>
                        </div>
                    </div>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-2">{{ $cardTitle }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 max-w-xs mx-auto">{{ $cardDesc }}</p>
                </div>

                <!-- Form -->
                <form action="{{ $actionUrl }}" method="POST" class="space-y-6">
                    @csrf
                    @if($isEdit)
                        @method('PUT')
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Language Code -->
                        <div class="space-y-2">
                            <label for="code" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">
                                {{ __('Language Code') }}
                            </label>
                            <div class="group relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 {{ $focusText }} transition-colors">
                                    <i class="fas fa-code text-sm"></i>
                                </div>
                                <input type="text" id="code" name="code"
                                    class="block w-full pl-10 pr-4 py-4 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-0 {{ $focusRing }} focus:bg-white dark:focus:bg-[#3a3b3c] transition-all duration-200 @error('code') border-rose-300 bg-rose-50 dark:bg-rose-900/20 @enderror"
                                    placeholder="e.g. en, th, zh"
                                    value="{{ old('code', $isEdit ? $language->code : '') }}"
                                    required />
                            </div>
                            @error('code')
                                <p class="text-[10px] font-bold text-rose-500 mt-1 px-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Language Name -->
                        <div class="space-y-2">
                            <label for="name" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">
                                {{ __('Language Name') }}
                            </label>
                            <div class="group relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 {{ $focusText }} transition-colors">
                                    <i class="fas fa-language text-sm"></i>
                                </div>
                                <input type="text" id="name" name="name"
                                    class="block w-full pl-10 pr-4 py-4 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-0 {{ $focusRing }} focus:bg-white dark:focus:bg-[#3a3b3c] transition-all duration-200 @error('name') border-rose-300 bg-rose-50 dark:bg-rose-900/20 @enderror"
                                    placeholder="e.g. English, Thai"
                                    value="{{ old('name', $isEdit ? $language->name : '') }}"
                                    required />
                            </div>
                            @error('name')
                                <p class="text-[10px] font-bold text-rose-500 mt-1 px-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Native Name -->
                        <div class="space-y-2">
                            <label for="native_name" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">
                                {{ __('Native Name') }}
                            </label>
                            <div class="group relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 {{ $focusText }} transition-colors">
                                    <i class="fas fa-globe text-sm"></i>
                                </div>
                                <input type="text" id="native_name" name="native_name"
                                    class="block w-full pl-10 pr-4 py-4 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-0 {{ $focusRing }} focus:bg-white dark:focus:bg-[#3a3b3c] transition-all duration-200 @error('native_name') border-rose-300 bg-rose-50 dark:bg-rose-900/20 @enderror"
                                    placeholder="e.g. ภาษาไทย, 中文"
                                    value="{{ old('native_name', $isEdit ? $language->native_name : '') }}"
                                    required />
                            </div>
                            @error('native_name')
                                <p class="text-[10px] font-bold text-rose-500 mt-1 px-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Flag Emoji -->
                        <div class="space-y-2">
                            <label for="flag" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">
                                {{ __('Flag') }}
                            </label>
                            <div class="group relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 {{ $focusText }} transition-colors">
                                    <i class="fas fa-flag text-sm"></i>
                                </div>
                                <input type="text" id="flag" name="flag"
                                    class="block w-full pl-10 pr-4 py-4 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-0 {{ $focusRing }} focus:bg-white dark:focus:bg-[#3a3b3c] transition-all duration-200 @error('flag') border-rose-300 bg-rose-50 dark:bg-rose-900/20 @enderror"
                                    placeholder="e.g. 🇺🇸, 🇹🇭, 🇨🇳"
                                    value="{{ old('flag', $isEdit ? $language->flag : '') }}" />
                            </div>
                            @error('flag')
                                <p class="text-[10px] font-bold text-rose-500 mt-1 px-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Direction -->
                        <div class="space-y-2">
                            <label for="direction" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">
                                {{ __('Direction') }}
                            </label>
                            <div class="group relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 {{ $focusText }} transition-colors">
                                    <i class="fas fa-arrows-alt-h text-sm"></i>
                                </div>
                                <select id="direction" name="direction"
                                    class="block w-full pl-10 pr-4 py-4 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-gray-900 dark:text-white focus:outline-none focus:ring-0 {{ $focusRing }} focus:bg-white dark:focus:bg-[#3a3b3c] transition-all duration-200 appearance-none">
                                    <option value="ltr" {{ old('direction', $isEdit ? $language->direction : 'ltr') == 'ltr' ? 'selected' : '' }}>{{ __('Left to Right') }} (LTR)</option>
                                    <option value="rtl" {{ old('direction', $isEdit ? $language->direction : 'ltr') == 'rtl' ? 'selected' : '' }}>{{ __('Right to Left') }} (RTL)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Sort Order -->
                        <div class="space-y-2">
                            <label for="sort_order" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">
                                {{ __('Sort Order') }}
                            </label>
                            <div class="group relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 {{ $focusText }} transition-colors">
                                    <i class="fas fa-sort-numeric-up text-sm"></i>
                                </div>
                                <input type="number" id="sort_order" name="sort_order"
                                    class="block w-full pl-10 pr-4 py-4 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-0 {{ $focusRing }} focus:bg-white dark:focus:bg-[#3a3b3c] transition-all duration-200"
                                    placeholder="0"
                                    value="{{ old('sort_order', $isEdit ? $language->sort_order : 0) }}" />
                            </div>
                        </div>
                    </div>

                    <!-- Set as Default -->
                    <div class="space-y-3">
                        <label class="flex items-center cursor-pointer group">
                            <input type="hidden" name="is_default" value="0">
                            <input type="checkbox" name="is_default" value="1"
                                class="w-5 h-5 rounded-lg border-2 border-gray-200 dark:border-[#3a3b3c] text-indigo-600 focus:ring-indigo-500 focus:ring-offset-0 transition-colors"
                                {{ old('is_default', $isEdit ? $language->is_default : false) ? 'checked' : '' }}>
                            <span class="ml-3 text-sm font-bold text-gray-700 dark:text-gray-300 group-hover:text-indigo-600 transition-colors">{{ __('Set as Default') }}</span>
                        </label>
                    </div>

                    <!-- Status -->
                    <div class="space-y-3">
                        <label class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">
                            {{ __('Status') }}
                        </label>
                        <div class="flex flex-wrap gap-2">
                            <label class="relative group cursor-pointer">
                                <input type="radio" name="status" value="1" class="peer hidden" {{ old('status', $isEdit ? $language->status : 1) == 1 ? 'checked' : '' }}>
                                <div class="px-4 py-2.5 rounded-xl border-2 border-gray-100 dark:border-[#3a3b3c] bg-white dark:bg-[#242526] text-sm font-bold text-gray-500 dark:text-gray-400 transition-all duration-200 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 dark:peer-checked:bg-emerald-900/30 peer-checked:text-emerald-600 dark:peer-checked:text-emerald-400 group-hover:border-gray-200 dark:group-hover:border-[#4a4b4c]">
                                    <div class="flex items-center">
                                        <i class="fas fa-check-circle mr-2 text-[10px] opacity-50"></i>
                                        {{ __('Active') }}
                                    </div>
                                </div>
                                <div class="absolute -top-1 -right-1 w-4 h-4 bg-emerald-500 rounded-full flex items-center justify-center text-[8px] text-white opacity-0 peer-checked:opacity-100 transition-all scale-50 peer-checked:scale-100">
                                    <i class="fas fa-check"></i>
                                </div>
                            </label>
                            <label class="relative group cursor-pointer">
                                <input type="radio" name="status" value="2" class="peer hidden" {{ old('status', $isEdit ? $language->status : 1) == 2 ? 'checked' : '' }}>
                                <div class="px-4 py-2.5 rounded-xl border-2 border-gray-100 dark:border-[#3a3b3c] bg-white dark:bg-[#242526] text-sm font-bold text-gray-500 dark:text-gray-400 transition-all duration-200 peer-checked:border-rose-500 peer-checked:bg-rose-50 dark:peer-checked:bg-rose-900/30 peer-checked:text-rose-600 dark:peer-checked:text-rose-400 group-hover:border-gray-200 dark:group-hover:border-[#4a4b4c]">
                                    <div class="flex items-center">
                                        <i class="fas fa-times-circle mr-2 text-[10px] opacity-50"></i>
                                        {{ __('Not Active') }}
                                    </div>
                                </div>
                                <div class="absolute -top-1 -right-1 w-4 h-4 bg-rose-500 rounded-full flex items-center justify-center text-[8px] text-white opacity-0 peer-checked:opacity-100 transition-all scale-50 peer-checked:scale-100">
                                    <i class="fas fa-check"></i>
                                </div>
                            </label>
                        </div>
                        @error('status')
                            <p class="text-[10px] font-bold text-rose-500 mt-1 px-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4 pt-6">
                        <button type="submit"
                            class="flex-1 group relative flex items-center justify-center px-8 py-4 {{ $btnClass }} font-bold rounded-2xl active:scale-95 transition-all duration-200 shadow-lg overflow-hidden">
                            <span class="relative z-10 flex items-center">
                                <i class="fas {{ $btnIcon }} mr-2 opacity-50 group-hover:opacity-100 transition-opacity"></i>
                                {{ $btnText }}
                            </span>
                            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000 ease-in-out"></div>
                        </button>
                        <a href="{{ route('admin.languages.index') }}"
                           class="flex-1 flex items-center justify-center px-8 py-4 bg-white dark:bg-[#242526] text-gray-700 dark:text-gray-300 font-bold rounded-2xl border-2 border-gray-100 dark:border-[#3a3b3c] hover:border-gray-200 dark:hover:border-[#4a4b4c] hover:bg-gray-50 dark:hover:bg-[#3a3b3c] active:scale-95 transition-all duration-200">
                            {{ $isEdit ? __('Cancel') : __('Back to List') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
