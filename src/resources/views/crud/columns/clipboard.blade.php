{{-- regular object attribute --}}
@php
    $column['value'] = $column['value'] ?? data_get($entry, $column['name']);
    $column['escaped'] = $column['escaped'] ?? true;
    $column['limit'] = $column['limit'] ?? 32;
    $column['prefix'] = $column['prefix'] ?? '';
    $column['suffix'] = $column['suffix'] ?? '';
    $column['text'] = $column['default'] ?? '-';

    if ($column['value'] instanceof \Closure) {
        $column['value'] = $column['value']($entry);
    }

    if (is_array($column['value'])) {
        $column['value'] = json_encode($column['value']);
    }

    if (!empty($column['value'])) {
        $column['text'] = $column['prefix'] . Str::limit($column['value'], $column['limit'], '…') . $column['suffix'];
    }
@endphp

<span onclick="copyToClipboard('{{ $column['text'] }}')" role="button">
    @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_start')
    @if ($column['escaped'])
        {{ $column['text'] }}
    @else
        {!! $column['text'] !!}
    @endif
    <i class="las la-copy"></i>

    @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_end')
</span>

@bassetBlock('backpack/crud/columns/clipboard.js')
    <script>
        const copyToClipboard = async (text) => {
            try {
                await navigator.clipboard.writeText(text);
                new Noty({
                    type: "success",
                    text: `{!! '<strong>' .
                        trans('backpack::base.success') .
                        '</strong><br>' .
                        trans('backpack::crud.clipboard.confirmation_message') !!}`
                }).show();
            } catch (error) {
                console.error("Failed to copy to clipboard:", error);
                new Noty({
                    type: "error",
                    text: `{!! '<strong>' .
                        trans('backpack::base.error') .
                        '</strong><br>' .
                        trans('backpack::crud.clipboard.error_message') !!}`
                }).show();
            }
        };
    </script>
@endBassetBlock
