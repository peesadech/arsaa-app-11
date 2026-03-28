@extends('layouts.app')

@php
    $isEdit = isset($teacher);
    $actionUrl = $isEdit ? route('admin.teachers.update', $teacher->id) : route('admin.teachers.store');

    $title = $isEdit ? 'Edit Teacher' : 'Create New Teacher';
    $subtitle = $isEdit ? 'Update teacher details' : 'Teacher Registration';

    $gradientClass = $isEdit ? 'bg-gradient-to-r from-amber-500 via-orange-500 to-rose-500' : 'bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500';
    $blurClass = $isEdit ? 'bg-amber-500/20' : 'bg-indigo-500/20';
    $iconBgClass = $isEdit ? 'bg-amber-50 border-amber-100' : 'bg-indigo-50 border-indigo-100 shadow-inner';
    $iconClass = $isEdit ? 'fa-user-edit text-amber-600 rotate-3' : 'fa-chalkboard-teacher text-indigo-600 -rotate-3';
    $cardTitle = $isEdit ? 'Modify Teacher' : 'Teacher Details';
    $cardDesc = $isEdit
        ? "You are updating teacher #{$teacher->id}. Ensure all details are correct."
        : 'Register a new teacher and assign courses.';

    $focusRing = $isEdit ? 'focus:border-amber-400' : 'focus:border-indigo-500';
    $focusText = $isEdit ? 'group-focus-within:text-amber-500' : 'group-focus-within:text-indigo-500';

    $btnClass = $isEdit
        ? 'bg-amber-500 text-white hover:bg-amber-600 shadow-amber-200'
        : 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-indigo-200';

    $btnText = $isEdit ? 'Save Changes' : 'Create Teacher';
    $btnIcon = $isEdit ? 'fa-save' : 'fa-check-circle';

    $existingImage = $isEdit && $teacher->image_path ? $teacher->image_path : null;
@endphp

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-[#18191a] py-12 px-4 sm:px-6 lg:px-8 transition-colors duration-300">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex items-center space-x-4 mb-8">
            <a href="{{ route('admin.teachers.index') }}"
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
                <!-- Visual Identity Section -->
                <div class="flex flex-col items-center text-center mb-10">
                    <div class="relative">
                        <div class="absolute inset-0 {{ $blurClass }} blur-2xl rounded-full"></div>
                        <div id="image-preview-container" class="relative w-24 h-24 rounded-3xl flex items-center justify-center mb-4 transform border-2 border-white dark:border-[#3a3b3c] shadow-xl overflow-hidden {{ $iconBgClass }}">
                            @if($existingImage)
                                <img src="{{ asset($existingImage) }}" id="preview-img" class="w-full h-full object-cover">
                            @else
                                <i class="fas {{ $iconClass }} text-3xl"></i>
                            @endif
                        </div>
                        <label for="image_input" class="absolute -right-2 -bottom-2 w-8 h-8 rounded-xl bg-white dark:bg-[#3a3b3c] shadow-lg flex items-center justify-center text-gray-500 hover:text-indigo-600 cursor-pointer transition-all hover:scale-110 active:scale-90 border border-gray-100 dark:border-[#4a4b4c]">
                            <i class="fas fa-camera text-xs"></i>
                        </label>
                        <input type="file" id="image_input" class="hidden" accept="image/*">
                    </div>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-2">{{ $cardTitle }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 max-w-xs mx-auto">{{ $cardDesc }}</p>
                </div>

                <!-- Form -->
                <form action="{{ $actionUrl }}" method="POST" class="space-y-6" id="teacherForm">
                    @csrf
                    @if($isEdit)
                        @method('PUT')
                    @endif

                    <input type="hidden" name="image_base64" id="image_base64">
                    <input type="hidden" name="unavailable_periods" id="unavailablePeriodsInput">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Name -->
                        <div class="space-y-2">
                            <label for="name" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">Full Name</label>
                            <div class="group relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 {{ $focusText }} transition-colors">
                                    <i class="fas fa-user text-sm"></i>
                                </div>
                                <input type="text" id="name" name="name"
                                    class="block w-full pl-10 pr-4 py-4 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-0 {{ $focusRing }} focus:bg-white dark:focus:bg-[#3a3b3c] transition-all duration-200 @error('name') border-rose-300 bg-rose-50 dark:bg-rose-900/20 @enderror"
                                    placeholder="Enter full name"
                                    value="{{ old('name', $isEdit ? $teacher->name : '') }}"
                                    required />
                            </div>
                            @error('name')
                                <p class="text-[10px] font-bold text-rose-500 mt-1 px-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="space-y-2">
                            <label for="email" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">Email Address</label>
                            <div class="group relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 {{ $focusText }} transition-colors">
                                    <i class="fas fa-envelope text-sm"></i>
                                </div>
                                <input type="email" id="email" name="email"
                                    class="block w-full pl-10 pr-4 py-4 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-0 {{ $focusRing }} focus:bg-white dark:focus:bg-[#3a3b3c] transition-all duration-200 @error('email') border-rose-300 bg-rose-50 dark:bg-rose-900/20 @enderror"
                                    placeholder="email@example.com"
                                    value="{{ old('email', $isEdit ? $teacher->email : '') }}"
                                    required />
                            </div>
                            @error('email')
                                <p class="text-[10px] font-bold text-rose-500 mt-1 px-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Password -->
                        <div class="space-y-2">
                            <label for="password" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">
                                {{ $isEdit ? 'Change Password' : 'Password' }}
                            </label>
                            <div class="group relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 {{ $focusText }} transition-colors">
                                    <i class="fas fa-lock text-sm"></i>
                                </div>
                                <input type="password" id="password" name="password"
                                    class="block w-full pl-10 pr-4 py-4 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-0 {{ $focusRing }} focus:bg-white dark:focus:bg-[#3a3b3c] transition-all duration-200 @error('password') border-rose-300 bg-rose-50 dark:bg-rose-900/20 @enderror"
                                    placeholder="{{ $isEdit ? 'Leave blank to keep current' : 'Enter secure password' }}"
                                    {{ $isEdit ? '' : 'required' }} />
                            </div>
                            @error('password')
                                <p class="text-[10px] font-bold text-rose-500 mt-1 px-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div class="space-y-2">
                            <label for="password_confirmation" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">
                                Confirm Password
                            </label>
                            <div class="group relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 {{ $focusText }} transition-colors">
                                    <i class="fas fa-shield-alt text-sm"></i>
                                </div>
                                <input type="password" id="password_confirmation" name="password_confirmation"
                                    class="block w-full pl-10 pr-4 py-4 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-0 {{ $focusRing }} focus:bg-white dark:focus:bg-[#3a3b3c] transition-all duration-200"
                                    placeholder="Confirm password"
                                    {{ $isEdit ? '' : 'required' }} />
                            </div>
                        </div>
                    </div>

                    <!-- Phone -->
                    <div class="space-y-2">
                        <label for="phone" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">Phone Number</label>
                        <div class="group relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 {{ $focusText }} transition-colors">
                                <i class="fas fa-phone text-sm"></i>
                            </div>
                            <input type="text" id="phone" name="phone"
                                class="block w-full pl-10 pr-4 py-4 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-0 {{ $focusRing }} focus:bg-white dark:focus:bg-[#3a3b3c] transition-all duration-200 @error('phone') border-rose-300 bg-rose-50 dark:bg-rose-900/20 @enderror"
                                placeholder="Phone number (optional)"
                                value="{{ old('phone', $isEdit ? $teacher->phone : '') }}" />
                        </div>
                        @error('phone')
                            <p class="text-[10px] font-bold text-rose-500 mt-1 px-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Assigned Courses -->
                    <div class="space-y-3">
                        <label class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">Assigned Courses</label>
                        <div id="selectedCoursesContainer" class="flex flex-wrap gap-2 min-h-[40px] p-3 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl transition-all">
                            <!-- Selected courses will appear here -->
                        </div>
                        <button type="button" onclick="openCourseModal()"
                            class="inline-flex items-center px-4 py-2.5 bg-white dark:bg-[#242526] border-2 border-gray-100 dark:border-[#3a3b3c] rounded-xl text-sm font-bold text-gray-600 dark:text-gray-400 hover:border-indigo-500 hover:text-indigo-600 transition-all duration-200">
                            <i class="fas fa-plus-circle mr-2 text-xs"></i>
                            Select Courses
                        </button>
                    </div>

                    <!-- Unavailable Periods Schedule -->
                    <div id="scheduleSection" class="space-y-3" style="display:none">
                        <label class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">
                            <i class="fas fa-calendar-times mr-1 text-rose-400"></i> คาบที่ไม่ต้องการสอน
                        </label>
                        <p class="text-[10px] text-gray-400 dark:text-gray-500 font-medium px-1">คลิกเลือกคาบที่ไม่ต้องการสอน (แสดงตาม Education Level ของรายวิชาที่เลือก)</p>
                        <div id="scheduleGridContainer">
                            <!-- Schedule grids will be rendered here per education level -->
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="space-y-3">
                        <label class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">Account Status</label>
                        <div class="flex flex-wrap gap-2">
                            <label class="relative group cursor-pointer">
                                <input type="radio" name="status" value="1" class="peer hidden" {{ old('status', $isEdit ? $teacher->status : 1) == 1 ? 'checked' : '' }}>
                                <div class="px-4 py-2.5 rounded-xl border-2 border-gray-100 dark:border-[#3a3b3c] bg-white dark:bg-[#242526] text-sm font-bold text-gray-500 dark:text-gray-400 transition-all duration-200 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 dark:peer-checked:bg-emerald-900/30 peer-checked:text-emerald-600 dark:peer-checked:text-emerald-400 group-hover:border-gray-200 dark:group-hover:border-[#4a4b4c]">
                                    <div class="flex items-center">
                                        <i class="fas fa-check-circle mr-2 text-[10px] opacity-50"></i>
                                        Active
                                    </div>
                                </div>
                                <div class="absolute -top-1 -right-1 w-4 h-4 bg-emerald-500 rounded-full flex items-center justify-center text-[8px] text-white opacity-0 peer-checked:opacity-100 transition-all scale-50 peer-checked:scale-100">
                                    <i class="fas fa-check"></i>
                                </div>
                            </label>

                            <label class="relative group cursor-pointer">
                                <input type="radio" name="status" value="2" class="peer hidden" {{ old('status', $isEdit ? $teacher->status : 1) == 2 ? 'checked' : '' }}>
                                <div class="px-4 py-2.5 rounded-xl border-2 border-gray-100 dark:border-[#3a3b3c] bg-white dark:bg-[#242526] text-sm font-bold text-gray-500 dark:text-gray-400 transition-all duration-200 peer-checked:border-rose-500 peer-checked:bg-rose-50 dark:peer-checked:bg-rose-900/30 peer-checked:text-rose-600 dark:peer-checked:text-rose-400 group-hover:border-gray-200 dark:group-hover:border-[#4a4b4c]">
                                    <div class="flex items-center">
                                        <i class="fas fa-times-circle mr-2 text-[10px] opacity-50"></i>
                                        Not Active
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
                        <button type="button" onclick="handleSubmit()"
                            class="flex-1 group relative flex items-center justify-center px-8 py-4 {{ $btnClass }} font-bold rounded-2xl active:scale-95 transition-all duration-200 shadow-lg overflow-hidden">
                            <span class="relative z-10 flex items-center">
                                <i class="fas {{ $btnIcon }} mr-2 opacity-50 group-hover:opacity-100 transition-opacity"></i>
                                {{ $btnText }}
                            </span>
                            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000 ease-in-out"></div>
                        </button>

                        <a href="{{ route('admin.teachers.index') }}"
                           class="flex-1 flex items-center justify-center px-8 py-4 bg-white dark:bg-[#242526] text-gray-700 dark:text-gray-300 font-bold rounded-2xl border-2 border-gray-100 dark:border-[#3a3b3c] hover:border-gray-200 dark:hover:border-[#4a4b4c] hover:bg-gray-50 dark:hover:bg-[#3a3b3c] active:scale-95 transition-all duration-200">
                            {{ $isEdit ? 'Cancel' : 'Back to List' }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Course Selection Modal -->
<div id="courseModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
        <div class="fixed inset-0 bg-gray-500/75 dark:bg-black/75 backdrop-blur-sm transition-opacity" onclick="closeCourseModal()"></div>
        <div class="inline-block align-bottom bg-white dark:bg-[#242526] rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-100 dark:border-[#3a3b3c]">
            <!-- Modal Header -->
            <div class="px-6 pt-6 pb-4 border-b border-gray-100 dark:border-[#3a3b3c]">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-extrabold text-gray-900 dark:text-white tracking-tight">
                        <i class="fas fa-book mr-2 text-indigo-500"></i>Select Courses
                    </h3>
                    <button onclick="closeCourseModal()" class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-[#3a3b3c] flex items-center justify-center text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                <!-- Filters -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="relative">
                        <select id="modalEducationLevelFilter" class="appearance-none block w-full pl-4 pr-10 py-2.5 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-gray-100 dark:border-[#4a4b4c] rounded-xl text-xs font-bold text-gray-600 dark:text-gray-400 focus:outline-none focus:border-indigo-500 transition-all cursor-pointer">
                            <option value="">All Education Levels</option>
                            @foreach($educationLevels as $el)
                                <option value="{{ $el->id }}">{{ $el->name_th }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-chevron-down text-[10px]"></i>
                        </div>
                    </div>
                    <div class="relative">
                        <select id="modalSubjectGroupFilter" class="appearance-none block w-full pl-4 pr-10 py-2.5 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-gray-100 dark:border-[#4a4b4c] rounded-xl text-xs font-bold text-gray-600 dark:text-gray-400 focus:outline-none focus:border-indigo-500 transition-all cursor-pointer">
                            <option value="">All Subject Groups</option>
                            @foreach($subjectGroups as $sg)
                                <option value="{{ $sg->id }}">{{ $sg->name_th }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-chevron-down text-[10px]"></i>
                        </div>
                    </div>
                    <div class="relative">
                        <select id="modalSemesterFilter" class="appearance-none block w-full pl-4 pr-10 py-2.5 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-gray-100 dark:border-[#4a4b4c] rounded-xl text-xs font-bold text-gray-600 dark:text-gray-400 focus:outline-none focus:border-indigo-500 transition-all cursor-pointer">
                            <option value="">All Semesters</option>
                            @foreach($semesters as $sem)
                                <option value="{{ $sem->id }}">Semester {{ $sem->semester_number }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-chevron-down text-[10px]"></i>
                        </div>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-search text-xs"></i>
                        </div>
                        <input type="text" id="modalSearchInput" placeholder="Search course name..."
                            class="block w-full pl-9 pr-4 py-2.5 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-gray-100 dark:border-[#4a4b4c] rounded-xl text-xs font-bold text-gray-600 dark:text-gray-400 placeholder-gray-400 focus:outline-none focus:border-indigo-500 transition-all">
                    </div>
                </div>
            </div>

            <!-- Course List -->
            <div class="px-6 py-4 max-h-[400px] overflow-y-auto" id="courseListContainer">
                <div class="text-center py-8 text-gray-400">
                    <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                    <p class="text-xs font-bold">Loading courses...</p>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 border-t border-gray-100 dark:border-[#3a3b3c] bg-gray-50/50 dark:bg-[#18191a]/30 flex items-center justify-between">
                <span class="text-xs font-bold text-gray-400"><span id="selectedCount">0</span> courses selected</span>
                <button onclick="closeCourseModal()" class="px-6 py-2.5 bg-indigo-600 text-white font-bold rounded-xl text-xs hover:bg-indigo-700 transition-all active:scale-95 uppercase tracking-wider">
                    Done
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Image Processing Canvas -->
<canvas id="canvas" class="hidden"></canvas>

@push('scripts')
<script shadow>
    // Image upload handling
    const imageInput = document.getElementById('image_input');
    const imagePreview = document.getElementById('preview-img');
    const container = document.getElementById('image-preview-container');
    const base64Input = document.getElementById('image_base64');
    const canvas = document.getElementById('canvas');
    const ctx = canvas.getContext('2d');

    imageInput.addEventListener('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const img = new Image();
                img.onload = function() {
                    canvas.width = 400;
                    canvas.height = 400;
                    let sourceX = 0, sourceY = 0, sourceWidth = img.width, sourceHeight = img.height;
                    if (img.width > img.height) {
                        sourceWidth = img.height;
                        sourceX = (img.width - img.height) / 2;
                    } else {
                        sourceHeight = img.width;
                        sourceY = (img.height - img.width) / 2;
                    }
                    ctx.drawImage(img, sourceX, sourceY, sourceWidth, sourceHeight, 0, 0, 400, 400);
                    const base64 = canvas.toDataURL('image/jpeg', 0.85);
                    base64Input.value = base64;
                    if (imagePreview) {
                        imagePreview.src = base64;
                    } else {
                        container.innerHTML = '<img src="' + base64 + '" id="preview-img" class="w-full h-full object-cover">';
                    }
                };
                img.src = event.target.result;
            };
            reader.readAsDataURL(e.target.files[0]);
        }
    });

    // Selected courses state
    let selectedCourses = {};
    // Schedule state: { education_level_id: { day: [period, ...] } }
    let unavailablePeriods = {};
    // Cached schedule configs from server
    let scheduleConfigs = {};

    const DAY_META = {
        1: {th: 'จันทร์', en: 'Mon'},
        2: {th: 'อังคาร', en: 'Tue'},
        3: {th: 'พุธ', en: 'Wed'},
        4: {th: 'พฤหัส', en: 'Thu'},
        5: {th: 'ศุกร์', en: 'Fri'},
        6: {th: 'เสาร์', en: 'Sat'},
        7: {th: 'อาทิตย์', en: 'Sun'},
    };

    @if($isEdit && isset($teacherCourseIds))
        @foreach($teacher->courses as $course)
            selectedCourses[{{ $course->id }}] = {
                id: {{ $course->id }},
                name: @json($course->name),
                grade: @json($course->grade ? ($course->grade->name_th . ' / ' . $course->grade->name_en) : '-'),
                semester: @json($course->semester ? $course->semester->semester_number : '-'),
                subject_group: @json($course->subjectGroup ? $course->subjectGroup->name_th : '-'),
                education_level_id: @json($course->grade?->education_level_id),
                education_level_name: @json($course->grade?->educationLevel?->name_th)
            };
        @endforeach
        @if($teacher->unavailable_periods)
            unavailablePeriods = @json($teacher->unavailable_periods);
        @endif
    @endif

    function renderSelectedCourses() {
        const container = document.getElementById('selectedCoursesContainer');
        const ids = Object.keys(selectedCourses);

        if (ids.length === 0) {
            container.innerHTML = '<span class="text-gray-400 text-xs italic">No courses selected. Click "Select Courses" to add.</span>';
            return;
        }

        let html = '';
        ids.forEach(function(id) {
            const course = selectedCourses[id];
            html += '<div class="inline-flex items-center px-3 py-1.5 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-100 dark:border-indigo-800 text-indigo-700 dark:text-indigo-300 text-xs font-bold gap-2">';
            html += '<span>' + course.name + '</span>';
            html += '<button type="button" onclick="removeCourse(' + id + ')" class="w-4 h-4 rounded-full bg-indigo-200 dark:bg-indigo-700 flex items-center justify-center text-indigo-600 dark:text-indigo-300 hover:bg-rose-300 hover:text-rose-700 transition-colors">';
            html += '<i class="fas fa-times" style="font-size:8px"></i>';
            html += '</button>';
            html += '<input type="hidden" name="courses[]" value="' + id + '">';
            html += '</div>';
        });

        container.innerHTML = html;
        updateSelectedCount();
    }

    function removeCourse(id) {
        delete selectedCourses[id];
        renderSelectedCourses();
        const cb = document.getElementById('course-cb-' + id);
        if (cb) cb.checked = false;
        loadScheduleData();
    }

    function updateSelectedCount() {
        const countEl = document.getElementById('selectedCount');
        if (countEl) countEl.textContent = Object.keys(selectedCourses).length;
    }

    // Course modal
    let searchTimer = null;

    function openCourseModal() {
        document.getElementById('courseModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        fetchCourses();
    }

    function closeCourseModal() {
        document.getElementById('courseModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
        renderSelectedCourses();
        loadScheduleData();
    }

    function fetchCourses() {
        const educationLevelId = document.getElementById('modalEducationLevelFilter').value;
        const subjectGroupId = document.getElementById('modalSubjectGroupFilter').value;
        const semesterId = document.getElementById('modalSemesterFilter').value;
        const search = document.getElementById('modalSearchInput').value;

        const params = new URLSearchParams();
        if (educationLevelId) params.append('education_level_id', educationLevelId);
        if (subjectGroupId) params.append('subject_group_id', subjectGroupId);
        if (semesterId) params.append('semester_id', semesterId);
        if (search) params.append('search', search);

        const container = document.getElementById('courseListContainer');
        container.innerHTML = '<div class="text-center py-8 text-gray-400"><i class="fas fa-spinner fa-spin text-2xl mb-2"></i><p class="text-xs font-bold">Loading...</p></div>';

        fetch("{{ route('admin.teachers.search-courses') }}?" + params.toString())
            .then(function(r) { return r.json(); })
            .then(function(courses) {
                if (courses.length === 0) {
                    container.innerHTML = '<div class="text-center py-8 text-gray-400"><i class="fas fa-inbox text-2xl mb-2"></i><p class="text-xs font-bold">No courses found</p></div>';
                    return;
                }

                let html = '<div class="space-y-2">';
                courses.forEach(function(course) {
                    const isChecked = selectedCourses[course.id] ? 'checked' : '';
                    html += '<label class="flex items-center p-3 rounded-xl border-2 border-gray-100 dark:border-[#3a3b3c] hover:border-indigo-300 dark:hover:border-indigo-700 transition-all cursor-pointer group ' + (isChecked ? 'bg-indigo-50/50 dark:bg-indigo-900/20 border-indigo-200 dark:border-indigo-800' : '') + '">';
                    html += '<input type="checkbox" id="course-cb-' + course.id + '" class="hidden course-checkbox" value="' + course.id + '" data-name="' + course.name.replace(/"/g, '&quot;') + '" data-grade="' + course.grade.replace(/"/g, '&quot;') + '" data-semester="' + course.semester + '" data-subject-group="' + course.subject_group.replace(/"/g, '&quot;') + '" data-education-level-id="' + (course.education_level_id || '') + '" data-education-level-name="' + (course.education_level_name || '').replace(/"/g, '&quot;') + '" onchange="toggleCourse(this)" ' + isChecked + '>';
                    html += '<div class="w-5 h-5 rounded-lg border-2 border-gray-200 dark:border-[#4a4b4c] flex items-center justify-center mr-3 transition-all ' + (isChecked ? 'bg-indigo-500 border-indigo-500' : 'group-hover:border-indigo-400') + '">';
                    html += isChecked ? '<i class="fas fa-check text-white text-[10px]"></i>' : '';
                    html += '</div>';
                    html += '<div class="flex-1 min-w-0">';
                    html += '<div class="text-sm font-bold text-gray-800 dark:text-gray-200 truncate">' + course.name + '</div>';
                    html += '<div class="flex flex-wrap gap-2 mt-1">';
                    html += '<span class="text-[10px] font-bold text-gray-400"><i class="fas fa-layer-group mr-1"></i>' + course.grade + '</span>';
                    html += '<span class="text-[10px] font-bold text-gray-400"><i class="fas fa-calendar mr-1"></i>Sem ' + course.semester + '</span>';
                    if (course.subject_group !== '-') {
                        html += '<span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-indigo-50 dark:bg-indigo-900/30 text-indigo-500">' + course.subject_group + '</span>';
                    }
                    html += '</div>';
                    html += '</div>';
                    html += '</label>';
                });
                html += '</div>';
                container.innerHTML = html;
                updateSelectedCount();
            });
    }

    function toggleCourse(checkbox) {
        const id = parseInt(checkbox.value);
        const label = checkbox.closest('label');
        const checkIcon = checkbox.nextElementSibling;

        if (checkbox.checked) {
            selectedCourses[id] = {
                id: id,
                name: checkbox.dataset.name,
                grade: checkbox.dataset.grade,
                semester: checkbox.dataset.semester,
                subject_group: checkbox.dataset.subjectGroup,
                education_level_id: checkbox.dataset.educationLevelId || null,
                education_level_name: checkbox.dataset.educationLevelName || null
            };
            label.classList.add('bg-indigo-50/50', 'dark:bg-indigo-900/20', 'border-indigo-200', 'dark:border-indigo-800');
            checkIcon.classList.add('bg-indigo-500', 'border-indigo-500');
            checkIcon.innerHTML = '<i class="fas fa-check text-white text-[10px]"></i>';
        } else {
            delete selectedCourses[id];
            label.classList.remove('bg-indigo-50/50', 'dark:bg-indigo-900/20', 'border-indigo-200', 'dark:border-indigo-800');
            checkIcon.classList.remove('bg-indigo-500', 'border-indigo-500');
            checkIcon.innerHTML = '';
        }
        updateSelectedCount();
    }

    // Filter events
    document.getElementById('modalEducationLevelFilter').addEventListener('change', fetchCourses);
    document.getElementById('modalSubjectGroupFilter').addEventListener('change', fetchCourses);
    document.getElementById('modalSemesterFilter').addEventListener('change', fetchCourses);
    document.getElementById('modalSearchInput').addEventListener('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(fetchCourses, 300);
    });

    function handleSubmit() {
        document.getElementById('unavailablePeriodsInput').value = JSON.stringify(unavailablePeriods);
        document.getElementById('teacherForm').submit();
    }

    // ── Schedule Grid Logic ──────────────────────────────────────────────────

    function getEducationLevelIds() {
        const ids = new Set();
        Object.values(selectedCourses).forEach(function(c) {
            if (c.education_level_id) ids.add(String(c.education_level_id));
        });
        return Array.from(ids);
    }

    function loadScheduleData() {
        const elIds = getEducationLevelIds();
        const section = document.getElementById('scheduleSection');
        const container = document.getElementById('scheduleGridContainer');

        if (elIds.length === 0) {
            section.style.display = 'none';
            container.innerHTML = '';
            return;
        }

        section.style.display = '';

        // Check if we already have all configs cached
        const missing = elIds.filter(function(id) { return !scheduleConfigs[id]; });
        if (missing.length === 0) {
            renderAllScheduleGrids();
            return;
        }

        container.innerHTML = '<div class="text-center py-6 text-gray-400"><i class="fas fa-spinner fa-spin text-xl mb-2"></i><p class="text-xs font-bold">Loading schedule...</p></div>';

        const params = new URLSearchParams();
        elIds.forEach(function(id) { params.append('education_level_ids[]', id); });

        fetch("{{ route('admin.teachers.schedule-data') }}?" + params.toString())
            .then(function(r) { return r.json(); })
            .then(function(data) {
                data.forEach(function(s) {
                    scheduleConfigs[String(s.education_level_id)] = s;
                });
                renderAllScheduleGrids();
            });
    }

    function renderAllScheduleGrids() {
        const elIds = getEducationLevelIds();
        const container = document.getElementById('scheduleGridContainer');

        if (elIds.length === 0) {
            container.innerHTML = '';
            document.getElementById('scheduleSection').style.display = 'none';
            return;
        }

        let html = '';
        elIds.forEach(function(elId) {
            const config = scheduleConfigs[elId];
            if (!config) return;
            html += renderScheduleGrid(elId, config);
        });

        container.innerHTML = html;
    }

    function fmtTime(min) {
        const h = Math.floor(min / 60) % 24, m = min % 60;
        return String(h).padStart(2,'0') + ':' + String(m).padStart(2,'0');
    }

    function calcPeriodStart(cfg, dayNum, p) {
        const dayCfg = cfg.day_configs[String(dayNum)] || {};
        const timeStr = dayCfg.start_time || cfg.start_time || '08:00';
        const parts = timeStr.split(':').map(Number);
        const dur = cfg.period_duration || 50;
        let t = parts[0] * 60 + parts[1];
        for (let i = 1; i < p; i++) {
            t += dur;
            const b = (dayCfg.breaks || {})[String(i)];
            if (b) t += b;
        }
        return t;
    }

    function isDark() {
        return document.documentElement.classList.contains('dark');
    }

    function isUnavailable(elId, day, period) {
        const el = unavailablePeriods[String(elId)];
        if (!el) return false;
        const dayArr = el[String(day)];
        if (!dayArr) return false;
        return dayArr.indexOf(period) !== -1;
    }

    function toggleUnavailable(elId, day, period) {
        const elKey = String(elId);
        const dayKey = String(day);
        if (!unavailablePeriods[elKey]) unavailablePeriods[elKey] = {};
        if (!unavailablePeriods[elKey][dayKey]) unavailablePeriods[elKey][dayKey] = [];

        const arr = unavailablePeriods[elKey][dayKey];
        const idx = arr.indexOf(period);
        if (idx === -1) {
            arr.push(period);
        } else {
            arr.splice(idx, 1);
        }

        // Clean up empty
        if (arr.length === 0) delete unavailablePeriods[elKey][dayKey];
        if (Object.keys(unavailablePeriods[elKey]).length === 0) delete unavailablePeriods[elKey];

        renderAllScheduleGrids();
    }

    function renderScheduleGrid(elId, config) {
        const dark = isDark();
        const C = {
            bg:      dark ? '#242526' : '#ffffff',
            bgAlt:   dark ? '#18191a' : '#f9fafb',
            border:  dark ? '#3a3b3c' : '#f0f0f0',
            text:    dark ? '#e4e6eb' : '#1f2937',
            muted:   dark ? '#6b7280' : '#9ca3af',
            indigo:  '#6366f1',
            rose:    '#f43f5e',
            roseBg:  dark ? 'rgba(244,63,94,0.15)' : 'rgba(255,228,230,0.8)',
            roseBdr: '#f43f5e',
        };

        const dayConfigs = config.day_configs || {};
        const dur = config.period_duration || 50;

        // Get active days (with periods > 0)
        const activeDays = [];
        for (let d = 1; d <= 7; d++) {
            const dc = dayConfigs[String(d)];
            if (dc && dc.periods > 0) activeDays.push(d);
        }

        if (activeDays.length === 0) {
            return '<div class="p-4 mb-3 bg-gray-50 dark:bg-[#18191a]/30 rounded-2xl border border-gray-100 dark:border-[#3a3b3c]/50">' +
                '<div class="text-xs font-bold text-gray-400">' + config.education_level_name + ' — ยังไม่ได้ตั้งค่าตารางสอน</div></div>';
        }

        const maxP = activeDays.reduce(function(m, d) {
            return Math.max(m, (dayConfigs[String(d)] || {}).periods || 0);
        }, 0);

        let html = '<div class="mb-4 bg-white dark:bg-[#242526] rounded-2xl border border-gray-100 dark:border-[#3a3b3c] overflow-hidden">';
        html += '<div class="px-4 py-3 border-b border-gray-100 dark:border-[#3a3b3c] flex items-center justify-between">';
        html += '<span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider"><i class="fas fa-school mr-1"></i> ' + config.education_level_name + '</span>';
        html += '<span class="text-[10px] text-rose-400 font-bold">คลิกคาบที่ไม่ต้องการสอน</span>';
        html += '</div>';
        html += '<div class="overflow-x-auto p-3">';
        html += '<table style="border-collapse:separate;border-spacing:0;width:100%">';

        // Header row
        html += '<tr>';
        html += '<td style="padding:8px;border-right:2px solid ' + C.border + ';min-width:56px;width:56px"></td>';
        activeDays.forEach(function(d) {
            const meta = DAY_META[d];
            const dc = dayConfigs[String(d)] || {};
            html += '<td style="padding:8px 6px;text-align:center;background:' + C.bgAlt + ';border:1px solid ' + C.border + ';border-bottom:2px solid ' + C.indigo + ';min-width:90px">';
            html += '<div style="font-size:12px;font-weight:800;color:' + C.indigo + '">' + meta.th + '</div>';
            html += '<div style="font-size:9px;color:' + C.muted + ';text-transform:uppercase">' + meta.en + '</div>';
            html += '<div style="font-size:9px;color:' + C.muted + ';margin-top:2px">' + dc.periods + ' คาบ</div>';
            html += '</td>';
        });
        html += '</tr>';

        // Period rows
        for (let p = 1; p <= maxP; p++) {
            html += '<tr>';
            html += '<td style="padding:6px 8px;border-right:2px solid ' + C.border + ';white-space:nowrap">';
            html += '<span style="font-size:11px;font-weight:800;color:' + C.indigo + '">คาบ ' + p + '</span>';
            html += '</td>';

            activeDays.forEach(function(d) {
                const dc = dayConfigs[String(d)] || {};
                if (p <= (dc.periods || 0)) {
                    const s = calcPeriodStart(config, d, p);
                    const checked = isUnavailable(elId, d, p);
                    const cellBg = checked ? C.roseBg : C.bg;
                    const cellBorder = checked ? C.roseBdr : C.border;
                    const cursor = 'cursor:pointer';

                    html += '<td style="padding:5px 4px;border:1.5px solid ' + cellBorder + ';background:' + cellBg + ';text-align:center;' + cursor + ';transition:all .15s;user-select:none" ';
                    html += 'onclick="toggleUnavailable(' + elId + ',' + d + ',' + p + ')" ';
                    html += 'title="' + (checked ? 'คลิกเพื่อยกเลิก' : 'คลิกเพื่อเลือกว่าไม่สอน') + '">';

                    if (checked) {
                        html += '<div style="font-size:11px;font-weight:800;color:' + C.rose + '"><i class="fas fa-times-circle"></i></div>';
                        html += '<div style="font-size:9px;color:' + C.rose + ';font-weight:700">ไม่สอน</div>';
                    } else {
                        html += '<div style="font-size:11px;font-weight:700;color:' + C.text + '">' + fmtTime(s) + '</div>';
                        html += '<div style="font-size:9px;color:' + C.muted + '">– ' + fmtTime(s + dur) + '</div>';
                    }
                    html += '</td>';
                } else {
                    html += '<td style="padding:5px 4px;border:1px solid ' + C.border + ';background:' + C.bgAlt + ';opacity:.3;text-align:center">';
                    html += '<span style="font-size:11px;color:' + C.muted + '">—</span>';
                    html += '</td>';
                }
            });
            html += '</tr>';

            // Break rows
            if (p < maxP) {
                let hasAnyBreak = false;
                activeDays.forEach(function(d) {
                    const dc = dayConfigs[String(d)] || {};
                    if (p < (dc.periods || 0) && dc.breaks && dc.breaks[String(p)]) hasAnyBreak = true;
                });

                if (hasAnyBreak) {
                    html += '<tr>';
                    html += '<td style="padding:2px 8px;border-right:2px solid ' + C.border + '">';
                    html += '<span style="font-size:9px;color:#f59e0b;font-weight:700">☕ พัก</span>';
                    html += '</td>';
                    activeDays.forEach(function(d) {
                        const dc = dayConfigs[String(d)] || {};
                        const bDur = (dc.breaks || {})[String(p)];
                        if (bDur && p < (dc.periods || 0)) {
                            html += '<td style="padding:2px 4px;border:1px solid #f59e0b;background:' + (dark ? 'rgba(245,158,11,0.12)' : 'rgba(254,243,199,0.8)') + ';text-align:center">';
                            html += '<span style="font-size:10px;color:#d97706;font-weight:700">☕ ' + bDur + ' น.</span>';
                            html += '</td>';
                        } else {
                            html += '<td style="padding:2px 4px;border:1px solid ' + C.border + ';background:' + C.bgAlt + ';opacity:.2"></td>';
                        }
                    });
                    html += '</tr>';
                }
            }
        }

        html += '</table></div></div>';
        return html;
    }

    // Dark mode observer for schedule re-render
    new MutationObserver(function() { renderAllScheduleGrids(); }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

    // Initial render
    renderSelectedCourses();
    loadScheduleData();
</script>
@endpush
@endsection
