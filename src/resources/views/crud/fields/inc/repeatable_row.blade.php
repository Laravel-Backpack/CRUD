@if ($hidden ?? false)
<div class="d-none">
@endif

<div class="col-md-12 well repeatable-element row m-1 p-2" data-repeatable-identifier="{{ $field['name'] }}">
    @if (isset($field['subfields']) && is_array($field['subfields']) && count($field['subfields']))
    <div class="controls">
        <button type="button" class="close delete-element"><span aria-hidden="true">×</span></button>
        @if ($field['reorder'])
        <button type="button" class="close move-element-up">
            <svg viewBox="0 0 64 80"><path d="M46.8,36.7c-4.3-4.3-8.7-8.7-13-13c-1-1-2.6-1-3.5,0c-4.3,4.3-8.7,8.7-13,13c-2.3,2.3,1.3,5.8,3.5,3.5c4.3-4.3,8.7-8.7,13-13c-1.2,0-2.4,0-3.5,0c4.3,4.3,8.7,8.7,13,13C45.5,42.5,49,39,46.8,36.7L46.8,36.7z"/></svg>
        </button>
        <button type="button" class="close move-element-down">
            <svg viewBox="0 0 64 80"><path d="M17.2,30.3c4.3,4.3,8.7,8.7,13,13c1,1,2.6,1,3.5,0c4.3-4.3,8.7-8.7,13-13c2.3-2.3-1.3-5.8-3.5-3.5c-4.3,4.3-8.7,8.7-13,13c1.2,0,2.4,0,3.5,0c-4.3-4.3-8.7-8.7-13-13C18.5,24.5,15,28,17.2,30.3L17.2,30.3z"/></svg>
        </button>
        @endif
    </div>
    @foreach($field['subfields'] as $subfield)
        @php
            $fieldViewNamespace = $subfield['view_namespace'] ?? 'crud::fields';
            $fieldViewPath = $fieldViewNamespace.'.'.$subfield['type'];

            if(isset($row)) {
                if(!is_array($subfield['name'])) {
                    if(!Str::contains($subfield['name'], '.')) {
                        // this is a fix for 4.1 repeatable names that when the field was multiple, saved the keys with `[]` in the end. Eg: `tags[]` instead of `tags`
                        if(isset($row[$subfield['name']]) || isset($row[$subfield['name'].'[]'])) {
                            $subfield['value'] = $row[$subfield['name']] ?? $row[$subfield['name'].'[]'];
                        }
                        // since repeatable changes names both in JS and PHP we need to make sure we have a name
                        // that corresponds to the full relation that we can later convert into brakets to use in repeatable
                        // and also use it as key in repeatable if defined (repeatable over repeatable)
                        $subfield['real_name'] = $field['name'].'.'.$subfield['name'];
                        $subfield['name'] = $field['name'].'['.$repeatable_row_key.']['.$subfield['name'].']';
                        
                    }else{
                        $subfield['value'] = \Arr::get($row, $subfield['name']);

                        $subfield['name'] = $field['name'].'['.$repeatable_row_key.']['.Str::replace('.', '][', $subfield['name']).']';
                    }
                    $subfield['name'] = $field['name'].'['.$repeatable_row_key.']['.$subfield['name'].']';
                }else{
                    foreach ($subfield['name'] as $k => $item) {
                        $subfield['name'][$k] = $field['name'].'['.$repeatable_row_key.']['.$item.']';
                        $subfield['value'][$subfield['name'][$k]] = \Arr::get($row, $item);
                    }
                }
            }
        @endphp

        @include($fieldViewPath, ['field' => $subfield])
    @endforeach

    @endif
</div>


@if ($hidden ?? false)
</div>
@endif
