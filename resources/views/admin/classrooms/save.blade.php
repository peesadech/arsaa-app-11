{{-- Controller create()/edit() render this view; it delegates to the new-design create/edit views. --}}
@if (isset($classroom))
    @include('admin.classrooms.edit', ['classroom' => $classroom])
@else
    @include('admin.classrooms.create')
@endif
