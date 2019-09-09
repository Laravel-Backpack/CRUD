<!-- path: resources/views/vendor/backpack/crud/blocks/timeline.blade.php -->

<?php

// timeline Block

$blocks['timeline'] = [
	'type' => 'timeline',
	'label' => 'Timeline',
	'icon'  => 'fa fa-calendar',
	'fields' => [
		[
			'name' => 'entries',
			'label' => 'Entries',
			'type' => 'multiply',
			'fields' => [
				[
					'name' => 'date',
					'label' => 'Date',
					'type' => 'date',
					'wrapperAttributes' => [
		               'class' => 'form-group col-md-3'
		             ],
				],
				[
					'name' => 'title',
					'label' => 'Title',
					'type' => 'text',
					'wrapperAttributes' => [
		               'class' => 'form-group col-md-3'
		             ],
				],
				[
					'name' => 'photo',
					'label' => 'Photo',
					'type' => 'browse',
					'wrapperAttributes' => [
		               'class' => 'form-group col-md-6'
		             ],
				],
				[
					'name' => 'description',
					'label' => 'Description',
					'type' => 'textarea',
					'wrapperAttributes' => [
		               'class' => 'form-group col-md-12'
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