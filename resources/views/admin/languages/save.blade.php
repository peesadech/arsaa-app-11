{{-- Controller create()/edit() render this view; it delegates to the new-design create/edit views. --}}
@if (isset($language))
    @include('admin.languages.edit', ['language' => $language])
@else
    @include('admin.languages.create')
@endif
