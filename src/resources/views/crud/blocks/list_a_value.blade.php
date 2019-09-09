<!-- path: resources/views/vendor/backpack/crud/blocks/list_a_value.blade.php -->

<?php

// List a value Block

$blocks['list_a_value'] = [
	'type' => 'list_a_value',
	'label' => 'List a value',
	'icon'  => 'fa fa-list-ol',
	'fields' => [
		[
			'name' => 'entries',
			'label' => 'Entries',
			'type' => 'multiply',
			'fields' => [
				[
					'name' => 'number',
					'label' => 'Number',
					'type' => 'number',
					'wrapperAttributes' => [
		               'class' => 'form-group col-md-2'
		             ],
				],
				[
					'name' => 'title',
					'label' => 'Title',
					'type' => 'text',
					'wrapperAttributes' => [
		               'class' => 'form-group col-md-4'
		             ],
				],
				[
					'name' => 'description',
					'label' => 'Description',
					'type' => 'textarea',
					'wrapperAttributes' => [
		               'class' => 'form-group col-md-6'
		             ],
				],
			],
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