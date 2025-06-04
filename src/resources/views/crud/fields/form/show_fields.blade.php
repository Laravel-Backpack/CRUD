{{-- Show the inputs --}}
@foreach ($fields as $field)
    @php
      $fieldView = $crud->getFirstFieldView($field['type'], $field['view_namespace'] ?? false);
    @endphp

    @include($fieldView, ['field' => $field, 'inlineCreate' => true])
@endforeach