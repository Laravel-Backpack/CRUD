{{-- @if ($crud->columnTypeNotLoaded($column)) --}}
<link rel="stylesheet" href="{{ asset('packages/json-viewer/json-viewer.css') }}">
{{-- @endif--}}

<div id="json-viewer-{{ $column['name'] }}"></div>
<!-- json viewer by Roman Makudera (LorDOniX) https://github.com/LorDOniX/json-viewer 
https://www.cssscript.com/minimal-json-data-formatter-jsonviewer/ -->
<script src="{{ asset('packages/json-viewer/json-viewer.js') }}"></script>
<script>
    var jsonViewer = new JSONViewer();
    document.querySelector("#json-viewer-{{ $column['name'] }}").appendChild(jsonViewer.getContainer());
    jsonViewer.showJSON(@json($entry->{$column['name']}), -1, -1);
</script>
