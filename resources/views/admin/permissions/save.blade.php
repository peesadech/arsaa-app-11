@extends('layouts.app')

@php
    $isEdit = isset($permission);
    $actionUrl = $isEdit ? route('admin.permissions.update', $permission->id) : route('admin.permissions.store');
    $title = $isEdit ? __('Edit Permission') : __('Create New Permission');
    $subtitle = $isEdit ? __('Update access control details') : __('Access Control System');

    // Theme Configuration
    $theme = $isEdit ? 'amber' : 'indigo';
    $gradientClass = $isEdit ? 'bg-gradient-to-r from-amber-500 via-orange-500 to-rose-500' : 'bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500';
    $iconBgClass = $isEdit ? 'bg-amber-50 border-amber-100' : 'bg-indigo-50 border-indigo-100';
    $iconClass = $isEdit ? 'fa-pen-nib text-amber-600 rotate-3' : 'fa-key text-indigo-600 -rotate-3';
    $blurClass = $isEdit ? 'bg-amber-500/20' : 'bg-indigo-500/20';
    $cardTitle = $isEdit ? __('Modify Permission') : __('Permission Details');
    $cardDesc = $isEdit
        ? __('You are updating permission #:id. Ensure the name remains meaningful and consistent.', ['id' => $permission->id])
        : __('Define a new permission to control access to specific features or resources.');

    $focusRing = $isEdit ? 'focus:border-amber-400' : 'focus:border-indigo-500';
    $focusText = $isEdit ? 'group-focus-within:text-amber-500' : 'group-focus-within:text-indigo-500';

    $btnClass = $isEdit
        ? 'bg-amber-500 text-white hover:bg-amber-600 shadow-amber-200'
        : 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-indigo-200';

    $btnText = $isEdit ? __('Save Changes') : __('Create Permission');
    $btnIcon = $isEdit ? 'fa-save' : 'fa-check-circle';
@endphp

@section('content')
<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-xl mx-auto">
        <!-- Breadcrumb / Header -->
        <div class="flex items-center space-x-4 mb-8">
            <a href="{{ route('admin.permissions') }}"
               class="group flex items-center justify-center w-10 h-10 rounded-full bg-white shadow-sm border border-gray-200 text-gray-500 hover:text-indigo-600 hover:border-indigo-300 transition-all duration-200">
                <i class="fas fa-arrow-left group-hover:-translate-x-0.5 transition-transform"></i>
            </a>
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">{{ $title }}</h1>
                <p class="text-sm text-gray-500 font-medium px-1">{{ $subtitle }}</p>
            </div>
        </div>

        <!-- Main Card -->
        <div class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden transform transition-all">
            <!-- Decorative Top Border -->
            <div class="h-2 {{ $gradientClass }}"></div>

            <div class="p-8 sm:p-10">
                <!-- Visual Identity Section -->
                <div class="flex flex-col items-center text-center mb-10">
                    <div class="relative">
                        <div class="absolute inset-0 {{ $blurClass }} blur-2xl rounded-full"></div>
                        <div class="relative w-20 h-20 rounded-2xl flex items-center justify-center mb-4 transform border shadow-inner {{ $iconBgClass }}">
                            <i class="fas {{ $iconClass }} text-3xl"></i>
                        </div>
                    </div>
                    <h2 class="text-xl font-bold text-gray-800 mb-2">{{ $cardTitle }}</h2>
                    <p class="text-sm text-gray-500 max-w-xs mx-auto text-pretty">
                        {{ $cardDesc }}
                    </p>
                </div>

                <!-- Form -->
                <form action="{{ $actionUrl }}" method="POST" class="space-y-8" id="permissionForm">
                    @csrf
                    @if($isEdit)
                        @method('PUT')
                    @endif

                    <div class="space-y-2">
                        <label for="name" class="block text-xs font-bold text-gray-400 uppercase tracking-widest px-1">
                            {{ __('Permission Name') }}
                        </label>
                        <div class="group relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 {{ $focusText }} transition-colors">
                                <i class="fas fa-terminal text-sm"></i>
                            </div>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                class="block w-full pl-10 pr-4 py-4 bg-gray-50 border-2 border-gray-100 rounded-2xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-0 {{ $focusRing }} focus:bg-white transition-all duration-200 @error('name') border-red-300 bg-red-50 @enderror"
                                placeholder="{{ __('e.g. edit articles') }}"
                                value="{{ old('name', $isEdit ? $permission->name : '') }}"
                                required
                                autofocus
                            />
                        </div>
                        @error('name')
                            <p class="text-xs font-semibold text-red-500 mt-2 px-1 flex items-center">
                                <span class="w-1 h-1 bg-red-500 rounded-full mr-2"></span>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="guard_name" class="block text-xs font-bold text-gray-400 uppercase tracking-widest px-1">
                            {{ __('Guard Name') }}
                        </label>
                        <div class="group relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 {{ $focusText }} transition-colors z-10">
                                <i class="fas fa-shield-alt text-sm"></i>
                            </div>
                            <div class="relative">
                                <select
                                    id="guard_name"
                                    name="guard_name"
                                    class="block w-full pl-10 pr-4 py-4 bg-gray-50 border-2 border-gray-100 rounded-2xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-0 {{ $focusRing }} focus:bg-white transition-all duration-200 appearance-none font-medium"
                                    required
                                >
                                    <option value="web" {{ old('guard_name', $isEdit ? $permission->guard_name : 'web') === 'web' ? 'selected' : '' }}>WEB</option>
                                    <option value="api" {{ old('guard_name', $isEdit ? $permission->guard_name : 'web') === 'api' ? 'selected' : '' }}>API</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-400">
                                    <!-- <i class="fas fa-chevron-down text-xs"></i> -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4 pt-4">
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

                        <a href="{{ route('admin.permissions') }}"
                           class="flex-1 flex items-center justify-center px-8 py-4 bg-white text-gray-700 font-bold rounded-2xl border-2 border-gray-100 hover:border-gray-200 hover:bg-gray-50 active:scale-95 transition-all duration-200">
                            {{ $isEdit ? __('Cancel') : __('Back to List') }}
                        </a>
                    </div>
                </form>
            </div>

            <!-- Footer Help -->
            <div class="bg-gray-50/50 p-6 border-t border-gray-100">
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-white flex items-center justify-center border border-gray-200 shadow-sm text-indigo-500">
                        <i class="fas fa-lightbulb text-xs"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-gray-700 uppercase mb-1">{{ __('Naming Convention') }}</h4>
                        <p class="text-xs text-gray-400 leading-relaxed">
                            {{ __('Use consistent naming patterns like resource.action (e.g., posts.create, users.edit) to keep your permissions organized.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
