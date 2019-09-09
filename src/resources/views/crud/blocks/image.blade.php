<!-- path: resources/views/vendor/backpack/crud/blocks/image.blade.php -->

<?php

// Title Block

$blocks['image'] = [
	'type' => 'image',
	'label' => 'Image',
	'icon'  => 'fa fa-picture-o',
	'fields' => [
		[
			'name' => 'content',
			'label' => 'Content',
			'type' => 'browse',
		],
		[
			'name' => 'text_block_separator',
			'type' => 'custom_html',
			'value' => '<hr>',
        ],
		[
			'name' => 'alt_text',
			'label' => 'Alt text',
			'type' => 'text',
		],
		[
			'name' => 'image_alignment',
			'label' => "Align",
			'type' => 'select_from_array',
			'options' => ['left' => 'left', 'center' => 'center', 'right' => 'right'],
			'allows_null' => false,
			'default' => 'left',
			'wrapperAttributes' => [
               'class' => 'form-group col-md-6'
             ],
		],
		[
			'name' => 'class',
			'label' => 'Class',
			'type' => 'text',
			'wrapperAttributes' => [
               'class' => 'form-group col-md-6'
             ],
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