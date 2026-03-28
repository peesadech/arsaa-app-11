@extends('layouts.app')

@php
    $isEdit = isset($room);
    $actionUrl = $isEdit ? route('admin.rooms.update', $room->id) : route('admin.rooms.store');

    $title = $isEdit ? __('Edit Room') : __('Create New Room');
    $subtitle = $isEdit ? __('Update room details') : __('Room Registration');

    // Theme Configuration
    $gradientClass = $isEdit ? 'bg-gradient-to-r from-amber-500 via-orange-500 to-rose-500' : 'bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500';
    $blurClass = $isEdit ? 'bg-amber-500/20' : 'bg-indigo-500/20';
    $iconBgClass = $isEdit ? 'bg-amber-50 border-amber-100' : 'bg-indigo-50 border-indigo-100 shadow-inner';
    $iconClass = $isEdit ? 'fa-edit text-amber-600 rotate-3' : 'fa-plus text-indigo-600 -rotate-3';
    $cardTitle = $isEdit ? __('Modify Room') : __('Room Details');
    $cardDesc = $isEdit
        ? __('You are updating room #:id. Ensure all details are correct.', ['id' => $room->id])
        : __('Define a new room number and assign courses.');

    $focusRing = $isEdit ? 'focus:border-amber-400' : 'focus:border-indigo-500';
    $focusText = $isEdit ? 'group-focus-within:text-amber-500' : 'group-focus-within:text-indigo-500';

    $btnClass = $isEdit
        ? 'bg-amber-500 text-white hover:bg-amber-600 shadow-amber-200'
        : 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-indigo-200';

    $btnText = $isEdit ? __('Save Changes') : __('Create Room');
    $btnIcon = $isEdit ? 'fa-save' : 'fa-check-circle';

    $selectedCourseIds = old('course_ids', $isEdit ? $room->courses->pluck('id')->toArray() : []);
@endphp

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-[#18191a] py-12 px-4 sm:px-6 lg:px-8 transition-colors duration-300">
    <div class="max-w-2xl mx-auto">
        <!-- Breadcrumb / Header -->
        <div class="flex items-center space-x-4 mb-8">
            <a href="{{ route('admin.rooms.index') }}"
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
                <form action="{{ $actionUrl }}" method="POST" class="space-y-6" id="roomForm">
                    @csrf
                    @if($isEdit)
                        @method('PUT')
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Room Number -->
                        <div class="space-y-2">
                            <label for="room_number" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">
                                {{ __('Room Number') }}
                            </label>
                            <div class="group relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 {{ $focusText }} transition-colors">
                                    <i class="fas fa-door-open text-sm"></i>
                                </div>
                                <input
                                    type="text"
                                    id="room_number"
                                    name="room_number"
                                    class="block w-full pl-10 pr-4 py-4 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-0 {{ $focusRing }} focus:bg-white dark:focus:bg-[#3a3b3c] transition-all duration-200 @error('room_number') border-rose-300 bg-rose-50 dark:bg-rose-900/20 @enderror"
                                    placeholder="{{ __('e.g. 101, A-201') }}"
                                    value="{{ old('room_number', $isEdit ? $room->room_number : '') }}"
                                    required
                                />
                            </div>
                            @error('room_number')
                                <p class="text-[10px] font-bold text-rose-500 mt-1 px-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Building -->
                        <div class="space-y-2">
                            <label for="building_id" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">
                                {{ __('Building') }}
                            </label>
                            <div class="group relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 {{ $focusText }} transition-colors">
                                    <i class="fas fa-building text-sm"></i>
                                </div>
                                <select
                                    id="building_id"
                                    name="building_id"
                                    class="block w-full pl-10 pr-10 py-4 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-gray-900 dark:text-white appearance-none focus:outline-none focus:ring-0 {{ $focusRing }} focus:bg-white dark:focus:bg-[#3a3b3c] transition-all duration-200 @error('building_id') border-rose-300 bg-rose-50 dark:bg-rose-900/20 @enderror"
                                    required
                                >
                                    <option value="" disabled {{ !old('building_id', $isEdit ? $room->building_id : '') ? 'selected' : '' }}>{{ __('-- Please Select --') }}</option>
                                    @foreach($buildings as $building)
                                        <option value="{{ $building->id }}" {{ old('building_id', $isEdit ? $room->building_id : '') == $building->id ? 'selected' : '' }}>
                                            {{ $building->name_th }} / {{ $building->name_en }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-400">
                                    <i class="fas fa-chevron-down text-sm"></i>
                                </div>
                            </div>
                            @error('building_id')
                                <p class="text-[10px] font-bold text-rose-500 mt-1 px-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Courses (Multi-select via Modal) -->
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">
                            {{ __('Assigned Courses') }}
                        </label>
                        <div class="group relative">
                            <button
                                type="button"
                                onclick="openCourseModal()"
                                class="w-full flex items-center justify-between px-4 py-4 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-gray-900 dark:text-white focus:outline-none focus:ring-0 {{ $focusRing }} focus:bg-white dark:focus:bg-[#3a3b3c] transition-all duration-200 hover:border-gray-200 dark:hover:border-[#4a4b4c]"
                            >
                                <div class="flex items-center space-x-3">
                                    <i class="fas fa-book text-gray-400 text-sm"></i>
                                    <span id="courseSelectionText" class="text-sm font-medium text-gray-400">{{ __('Click to select courses...') }}</span>
                                </div>
                                <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
                            </button>
                        </div>

                        <!-- Selected Courses Display -->
                        <div id="selectedCoursesContainer" class="flex flex-wrap gap-2 mt-2">
                            <!-- Filled by JS -->
                        </div>

                        <!-- Hidden inputs for course_ids -->
                        <div id="courseHiddenInputs">
                            <!-- Filled by JS -->
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="space-y-2">
                        <label for="description" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">
                            {{ __('Description') }}
                        </label>
                        <div class="group relative">
                            <div class="absolute top-4 left-4 flex items-start pointer-events-none text-gray-400 {{ $focusText }} transition-colors">
                                <i class="fas fa-align-left text-sm"></i>
                            </div>
                            <textarea
                                id="description"
                                name="description"
                                rows="4"
                                class="block w-full pl-10 pr-4 py-4 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-0 {{ $focusRing }} focus:bg-white dark:focus:bg-[#3a3b3c] transition-all duration-200 @error('description') border-rose-300 bg-rose-50 dark:bg-rose-900/20 @enderror"
                                placeholder="{{ __('Additional details...') }}"
                            >{{ old('description', $isEdit ? $room->description : '') }}</textarea>
                        </div>
                        @error('description')
                            <p class="text-[10px] font-bold text-rose-500 mt-1 px-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div class="space-y-3">
                        <label class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">
                            {{ __('Status') }}
                        </label>
                        <div class="flex flex-wrap gap-2">
                            <label class="relative group cursor-pointer">
                                <input type="radio" name="status" value="1" class="peer hidden" {{ old('status', $isEdit ? $room->status : 1) == 1 ? 'checked' : '' }}>
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
                                <input type="radio" name="status" value="2" class="peer hidden" {{ old('status', $isEdit ? $room->status : 1) == 2 ? 'checked' : '' }}>
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

                        <a href="{{ route('admin.rooms.index') }}"
                           class="flex-1 flex items-center justify-center px-8 py-4 bg-white dark:bg-[#242526] text-gray-700 dark:text-gray-300 font-bold rounded-2xl border-2 border-gray-100 dark:border-[#3a3b3c] hover:border-gray-200 dark:hover:border-[#4a4b4c] hover:bg-gray-50 dark:hover:bg-[#3a3b3c] active:scale-95 transition-all duration-200">
                            {{ $isEdit ? __('Cancel') : __('Back to List') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Course Selection Modal -->
<div id="courseModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="course-modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500/75 dark:bg-black/75 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeCourseModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white dark:bg-[#242526] rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-100 dark:border-[#3a3b3c]">
            <div class="bg-white dark:bg-[#242526] px-6 pt-6 pb-4">
                <!-- Modal Header -->
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center border border-indigo-100 dark:border-indigo-900/50">
                            <i class="fas fa-book text-indigo-600 dark:text-indigo-400"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-extrabold text-gray-900 dark:text-white tracking-tight" id="course-modal-title">{{ __('Select Courses') }}</h3>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-tight">{{ __('Choose one or more courses') }}</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeCourseModal()" class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-[#3a3b3c] flex items-center justify-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                </div>

                <!-- Search -->
                <div class="mb-4">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-search text-sm"></i>
                        </div>
                        <input
                            type="text"
                            id="courseSearchInput"
                            class="block w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-0 focus:border-indigo-500 transition-all duration-200 text-sm"
                            placeholder="{{ __('Search courses...') }}"
                            oninput="filterCourses()"
                        />
                    </div>
                </div>

                <!-- Select All -->
                <div class="flex items-center justify-between mb-3 px-1">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" id="selectAllCourses" class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" onchange="toggleSelectAll()">
                        <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Select All') }}</span>
                    </label>
                    <span id="courseCount" class="text-xs font-bold text-indigo-500">0 {{ __('selected') }}</span>
                </div>

                <!-- Course List -->
                <div class="max-h-80 overflow-y-auto rounded-2xl border border-gray-100 dark:border-[#3a3b3c]/50">
                    @foreach($courses as $course)
                    <label class="course-item flex items-center justify-between px-4 py-3 hover:bg-gray-50 dark:hover:bg-[#3a3b3c] cursor-pointer transition-colors border-b border-gray-50 dark:border-[#3a3b3c]/30 last:border-b-0" data-name="{{ strtolower($course->name) }}">
                        <div class="flex items-center space-x-3">
                            <input
                                type="checkbox"
                                class="course-checkbox w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                value="{{ $course->id }}"
                                data-name="{{ $course->name }}"
                                {{ in_array($course->id, $selectedCourseIds) ? 'checked' : '' }}
                                onchange="updateCourseSelection()"
                            >
                            <div>
                                <span class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ $course->name }}</span>
                                @if($course->subjectGroup)
                                    <span class="ml-2 px-2 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 text-[10px] font-bold">{{ $course->subjectGroup->name_th }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center space-x-2 text-[10px] text-gray-400">
                            @if($course->grade)
                                <span>{{ $course->grade->name_th }}</span>
                            @endif
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="bg-gray-50/50 dark:bg-[#18191a]/30 px-6 py-4 flex justify-end space-x-3 border-t border-gray-100 dark:border-[#3a3b3c]/50">
                <button type="button" onclick="closeCourseModal()" class="px-6 py-2.5 rounded-xl border-2 border-gray-100 dark:border-[#3a3b3c] bg-white dark:bg-[#242526] text-gray-700 dark:text-gray-300 font-bold text-sm hover:bg-gray-50 dark:hover:bg-[#3a3b3c] transition-all active:scale-95">
                    {{ __('Cancel') }}
                </button>
                <button type="button" onclick="confirmCourseSelection()" class="px-6 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200/50 dark:shadow-none active:scale-95">
                    <i class="fas fa-check mr-1.5"></i> {{ __('Confirm') }}
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Courses data from server
    let selectedCourses = @json(
        $isEdit
            ? $room->courses->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->values()
            : collect($selectedCourseIds)->map(fn($id) => ['id' => $id, 'name' => $courses->firstWhere('id', $id)?->name ?? ''])->values()
    );

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        renderSelectedCourses();
    });

    function openCourseModal() {
        // Sync checkboxes with current selection
        document.querySelectorAll('.course-checkbox').forEach(cb => {
            cb.checked = selectedCourses.some(c => c.id == cb.value);
        });
        updateCourseCount();
        document.getElementById('courseModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeCourseModal() {
        document.getElementById('courseModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function filterCourses() {
        const search = document.getElementById('courseSearchInput').value.toLowerCase();
        document.querySelectorAll('.course-item').forEach(item => {
            const name = item.getAttribute('data-name');
            item.style.display = name.includes(search) ? '' : 'none';
        });
    }

    function toggleSelectAll() {
        const checked = document.getElementById('selectAllCourses').checked;
        document.querySelectorAll('.course-item').forEach(item => {
            if (item.style.display !== 'none') {
                item.querySelector('.course-checkbox').checked = checked;
            }
        });
        updateCourseCount();
    }

    function updateCourseSelection() {
        updateCourseCount();
    }

    function updateCourseCount() {
        const count = document.querySelectorAll('.course-checkbox:checked').length;
        document.getElementById('courseCount').textContent = count + ' {{ __("selected") }}';

        const allVisible = document.querySelectorAll('.course-item:not([style*="display: none"]) .course-checkbox');
        const allChecked = Array.from(allVisible).every(cb => cb.checked);
        document.getElementById('selectAllCourses').checked = allVisible.length > 0 && allChecked;
    }

    function confirmCourseSelection() {
        selectedCourses = [];
        document.querySelectorAll('.course-checkbox:checked').forEach(cb => {
            selectedCourses.push({ id: parseInt(cb.value), name: cb.getAttribute('data-name') });
        });
        renderSelectedCourses();
        closeCourseModal();
    }

    function renderSelectedCourses() {
        const container = document.getElementById('selectedCoursesContainer');
        const hiddenContainer = document.getElementById('courseHiddenInputs');
        const textEl = document.getElementById('courseSelectionText');

        container.innerHTML = '';
        hiddenContainer.innerHTML = '';

        if (selectedCourses.length === 0) {
            textEl.textContent = '{{ __("Click to select courses...") }}';
            textEl.classList.add('text-gray-400');
            textEl.classList.remove('text-gray-800', 'dark:text-white');
            return;
        }

        textEl.textContent = selectedCourses.length + ' {{ __("course(s) selected") }}';
        textEl.classList.remove('text-gray-400');
        textEl.classList.add('text-gray-800', 'dark:text-white');

        selectedCourses.forEach(course => {
            // Badge
            const badge = document.createElement('span');
            badge.className = 'inline-flex items-center px-3 py-1.5 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 text-xs font-bold';
            badge.innerHTML = '<i class="fas fa-book mr-1.5 text-[10px] opacity-50"></i>' + course.name +
                '<button type="button" onclick="removeCourse(' + course.id + ')" class="ml-2 text-indigo-400 hover:text-indigo-600 transition-colors"><i class="fas fa-times text-[10px]"></i></button>';
            container.appendChild(badge);

            // Hidden input
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'course_ids[]';
            input.value = course.id;
            hiddenContainer.appendChild(input);
        });
    }

    function removeCourse(id) {
        selectedCourses = selectedCourses.filter(c => c.id !== id);
        renderSelectedCourses();
    }
</script>
@endpush
@endsection
