<!-- path: resources/views/vendor/backpack/crud/blocks/call_to_action.blade.php -->

<?php

// Title Block

$blocks['call_to_action'] = [
	'type' => 'call_to_action',
	'label' => 'Call to action',
	'icon'  => 'fa fa-exclamation-circle',
	'fields' => [
		[
			'name' => 'title',
			'label' => 'Title',
			'type' => 'text',
		],
		[
			'name' => 'description',
			'label' => 'Description',
			'type' => 'textarea',
		],
		[
			'name' => 'link',
			'label' => 'Link',
			'type' => 'text',
		],
		[
			'name' => 'button_label',
			'label' => 'Button label',
			'type' => 'text',
		],
		[
			'name' => 'text_block_separator',
			'type' => 'custom_html',
			'value' => '<hr>',
		],
		[
			'label' => 'Background Color',
			'name' => 'background_color',
			'type' => 'color_picker',
			'wrapperAttributes' => [
               'class' => 'form-group col-md-6'
             ],
		],
		[
			'label' => 'Background image',
			'name' => 'background_image',
			'type' => 'browse',
			'wrapperAttributes' => [
               'class' => 'form-group col-md-6'
             ],
		],
	],
];

?>