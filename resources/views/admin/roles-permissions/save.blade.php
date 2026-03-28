@extends('layouts.app')

@php
    $isEdit = isset($role);
    $actionUrl = $isEdit ? route('admin.roles.update', $role->id) : route('admin.roles.store');

    $title = $isEdit ? __('Edit Role') : __('Create New Role');
    $subtitle = $isEdit ? __('Update role details') : __('Role Registration');

    // Theme Configuration
    $gradientClass = $isEdit ? 'bg-gradient-to-r from-amber-500 via-orange-500 to-rose-500' : 'bg-gradient-to-r from-teal-500 via-emerald-500 to-emerald-600';
    $blurClass = $isEdit ? 'bg-amber-500/20' : 'bg-emerald-500/20';
    $iconBgClass = $isEdit ? 'bg-amber-50 border-amber-100' : 'bg-emerald-50 border-emerald-100 shadow-inner';
    $iconClass = $isEdit ? 'fa-user-shield text-amber-600 rotate-3' : 'fa-plus text-emerald-600 -rotate-3';
    $cardTitle = $isEdit ? __('Modify Role') : __('Role Details');
    $cardDesc = $isEdit
        ? __('You are updating role #:id. Ensure permissions are assigned correctly.', ['id' => $role->id])
        : __('Define a new system role.');

    $focusRing = $isEdit ? 'focus:border-amber-400' : 'focus:border-emerald-500';
    $focusText = $isEdit ? 'group-focus-within:text-amber-500' : 'group-focus-within:text-emerald-500';

    $btnClass = $isEdit
        ? 'bg-amber-500 text-white hover:bg-amber-600 shadow-amber-200'
        : 'bg-emerald-600 text-white hover:bg-emerald-700 shadow-emerald-200';

    $btnText = $isEdit ? __('Save Changes') : __('Create Role');
    $btnIcon = $isEdit ? 'fa-save' : 'fa-check-circle';
@endphp

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-[#18191a] py-12 px-4 sm:px-6 lg:px-8 transition-colors duration-300">
    <div class="max-w-2xl mx-auto">
        <!-- Breadcrumb / Header -->
        <div class="flex items-center space-x-4 mb-8">
            <a href="{{ route('admin.roles-permissions') }}"
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
            <!-- Decorative Top Border -->
            <div class="h-2 {{ $gradientClass }}"></div>

            <div class="p-8 sm:p-10">
                <!-- Visual Identity Section -->
                <div class="flex flex-col items-center text-center mb-10">
                    <div class="relative">
                        <div class="absolute inset-0 {{ $blurClass }} blur-2xl rounded-full"></div>
                        <div class="relative w-20 h-20 rounded-2xl flex items-center justify-center mb-4 transform border {{ $iconBgClass }}">
                            <i class="fas {{ $iconClass }} text-3xl"></i>
                        </div>
                    </div>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-2">{{ $cardTitle }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 max-w-xs mx-auto">
                        {{ $cardDesc }}
                    </p>
                </div>

                <!-- Form -->
                <form action="{{ $actionUrl }}" method="POST" class="space-y-6" id="roleForm">
                    @csrf
                    @if($isEdit)
                        @method('PUT')
                    @endif

                    <div class="grid grid-cols-1 gap-6">
                        <!-- Name -->
                        <div class="space-y-2">
                            <label for="name" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">
                                {{ __('Role Name') }}
                            </label>
                            <div class="group relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 {{ $focusText }} transition-colors">
                                    <i class="fas fa-user-tag text-sm"></i>
                                </div>
                                <input
                                    type="text"
                                    id="name"
                                    name="name"
                                    class="block w-full pl-10 pr-4 py-4 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-0 {{ $focusRing }} focus:bg-white dark:focus:bg-[#3a3b3c] transition-all duration-200 @error('name') border-rose-300 bg-rose-50 dark:bg-rose-900/20 @enderror"
                                    placeholder="{{ __('e.g. Administrator') }}"
                                    value="{{ old('name', $isEdit ? $role->name : '') }}"
                                    required
                                />
                            </div>
                            @error('name')
                                <p class="text-[10px] font-bold text-rose-500 mt-1 px-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Permissions -->
                        <div class="space-y-4">
                            <label class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">
                                {{ __('Permissions') }}
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-64 overflow-y-auto p-4 bg-gray-50 dark:bg-[#18191a]/30 rounded-[2rem] border border-gray-100 dark:border-[#3a3b3c]/50">
                                @foreach($permissions as $permission)
                                    <label class="relative flex items-center p-3 rounded-xl border-2 border-transparent hover:bg-white dark:hover:bg-[#242526] cursor-pointer transition-all">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                            class="w-5 h-5 rounded-lg border-2 border-gray-200 text-indigo-600 focus:ring-indigo-500 transition-all cursor-pointer"
                                            {{ (is_array(old('permissions')) && in_array($permission->id, old('permissions'))) || ($isEdit && $role->permissions->contains($permission->id)) ? 'checked' : '' }}>
                                        <span class="ml-3 text-xs font-bold text-gray-700 dark:text-gray-300">{{ $permission->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('permissions')
                                <p class="text-[10px] font-bold text-rose-500 mt-1 px-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>


                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4 pt-6">
                        <button
                            type="submit"
                            class="flex-1 group relative flex items-center justify-center px-8 py-4 {{ $btnClass }} font-bold rounded-2xl active:scale-95 transition-all duration-200 shadow-lg overflow-hidden"
                        >
                            <span class="relative z-10 flex items-center">
                                <i class="fas {{ $btnIcon }} mr-2 opacity-50 group-hover:opacity-100 transition-opacity"></i>
                                {{ $btnText }}
                            </span>
                            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000 ease-in-out"></div>
                        </button>

                        <a href="{{ route('admin.roles-permissions') }}"
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
