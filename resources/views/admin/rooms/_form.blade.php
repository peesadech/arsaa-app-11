@php
    $r = $room ?? null;
    $buildingOptions = $buildings->pluck('name_th', 'id')->toArray();

    // Floors are not provided by the controller (originally loaded via AJAX).
    // Populate a plain static select of all active floors, labelled by building
    // so the choice is unambiguous. No JS / AJAX required.
    $floorOptions = \App\Models\Floor::with('building')
        ->where('status', 1)
        ->orderBy('building_id')
        ->orderBy('name_th')
        ->get()
        ->mapWithKeys(fn ($fl) => [
            $fl->id => ($fl->building?->name_th ? $fl->building->name_th . ' - ' : '') . $fl->name_th,
        ])
        ->toArray();

    $courseOptions = $courses->pluck('name', 'id')->toArray();
    $selectedCourseIds = old('course_ids', $r ? $r->courses->pluck('id')->toArray() : []);
    $unavailableValue = old('unavailable_periods', $r ? json_encode($r->unavailable_periods) : '');
@endphp
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <x-card :title="__('Basic info')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input :label="__('Room Number')" name="room_number" :value="$r->room_number ?? null" required />
                <x-form.select
                    :label="__('Building')"
                    name="building_id"
                    :options="$buildingOptions"
                    :selected="$r->building_id ?? null"
                    :placeholder="__('Select Building')"
                    required />
                <x-form.select
                    :label="__('Floor')"
                    name="floor_id"
                    :options="$floorOptions"
                    :selected="$r->floor_id ?? null"
                    :placeholder="__('Select Floor')" />
                <div class="md:col-span-2">
                    <x-form.textarea :label="__('Description')" name="description" rows="3" :value="$r->description ?? null" />
                </div>
            </div>
        </x-card>

        <x-card :title="__('Assigned Courses')">
            <label for="course_ids" class="form-label">{{ __('Courses') }}</label>
            <select name="course_ids[]" id="course_ids" multiple size="8" class="form-select">
                @foreach ($courseOptions as $courseId => $courseName)
                    <option value="{{ $courseId }}" @selected(in_array($courseId, $selectedCourseIds))>{{ $courseName }}</option>
                @endforeach
            </select>
            <p class="form-help mt-2">{{ __('Hold Ctrl (Cmd on Mac) to select multiple courses.') }}</p>
        </x-card>
    </div>

    <div class="space-y-6">
        <x-card :title="__('Status')">
            <x-form.select
                name="status"
                :options="[1 => __('Active'), 2 => __('Not Active')]"
                :selected="$r->status ?? 1" />
        </x-card>
    </div>
</div>

{{-- Preserve existing unavailable periods (no editor UI in this view) --}}
<input type="hidden" name="unavailable_periods" value="{{ $unavailableValue }}">
