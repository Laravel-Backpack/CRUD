<!-- path: resources/views/vendor/backpack/crud/blocks/press.blade.php -->

<?php

// Press Block

$blocks['press'] = [
	'type' => 'press',
	'label' => 'Press',
	'icon'  => 'fa fa-newspaper-o',
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
			'name' => 'thumbnail',
			'label' => 'Thumbnail',
			'type' => 'browse',
			'wrapperAttributes' => [
               'class' => 'form-group col-md-6'
             ],
		],
		[
			'name' => 'pdf',
			'label' => 'PDF file',
			'type' => 'browse',
			'wrapperAttributes' => [
               'class' => 'form-group col-md-6'
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