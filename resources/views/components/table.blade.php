@props(['columns' => [], 'empty' => 'No records found.'])

<div class="table-wrap">
    <div class="overflow-x-auto">
        <table>
            @if (!empty($columns))
                <thead>
                    <tr>
                        @foreach ($columns as $col)
                            <th>{{ is_array($col) ? ($col['label'] ?? '') : $col }}</th>
                        @endforeach
                    </tr>
                </thead>
            @endif
            <tbody>
                {{ $slot }}
            </tbody>
        </table>
    </div>
    @isset($footer)
        <div class="px-5 py-3 border-t border-slate-100 bg-slate-50">{{ $footer }}</div>
    @endisset
</div>
