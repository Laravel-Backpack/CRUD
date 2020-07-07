@php
	$field['wrapper'] = $field['wrapper'] ?? $field['wrapperAttributes'] ?? [];

    // each wrapper attribute can be a callback or a string
    // for those that are callbacks, run the callbacks to get the final string to use
    foreach($field['wrapper'] as $attributeKey => $value) {
        $field['wrapper'][$attributeKey] = !is_string($value) && is_callable($value) ? $value($crud, $field, $entry ?? null) : $value ?? '';
    }
	// if the field is required in the FormRequest, it should have an asterisk
	$required = (isset($action) && $crud->isRequired($field['name'], $action)) ? ' required' : '';

	// if the developer has intentionally set the required attribute on the field
	// forget whatever is in the FormRequest, do what the developer wants
	$required = (isset($field['showAsterisk'])) ? ($field['showAsterisk'] ? ' required' : '') : $required;

	$field['wrapper']['class'] = $field['wrapper']['class'] ?? "form-group col-sm-12";
	$field['wrapper']['class'] = $field['wrapper']['class'].$required;
	$field['wrapper']['element'] = $field['wrapper']['element'] ?? 'div';
@endphp

<{{ $field['wrapper']['element'] }}
	@foreach($field['wrapper'] as $attribute => $value)
	    {{ $attribute }}="{{ $value }}"
	@endforeach
>