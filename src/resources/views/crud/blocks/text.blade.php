<!-- path: resources/views/vendor/backpack/crud/blocks/text.blade.php -->

<?php

// Text Block

$blocks['text'] = [
	'type' => 'text',
	'label' => 'Text',
	'icon'  => 'fa fa-align-left',
	'fields' => [
		[
			'name' => 'content',
			'label' => 'Content',
			'type' => 'ckeditor',
		],
		[
			'name' => 'text_block_separator',
			'type' => 'custom_html',
			'value' => '<hr>',
		],
		[ // title size
			'name' => 'title_size',
			'label' => "Title size",
			'type' => 'select_from_array',
			'options' => ['h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4', 'h5' => 'H5'],
			'allows_null' => false,
			'default' => 'h1',
			'wrapperAttributes' => [
               'class' => 'form-group col-md-3'
             ],
		],
		[ // title size
			'name' => 'title_alignment',
			'label' => "Align",
			'type' => 'select_from_array',
			'options' => ['left' => 'left', 'center' => 'center', 'right' => 'right', 'justify' => 'justify'],
			'allows_null' => false,
			'default' => 'left',
			'wrapperAttributes' => [
               'class' => 'form-group col-md-3'
             ],
		],
		[   // color_picker
			'label' => 'Text Color',
			'name' => 'title_text_color',
			'type' => 'color_picker',
			'wrapperAttributes' => [
               'class' => 'form-group col-md-3'
             ],
		],
		[
			'name' => 'id',
			'label' => 'ID',
			'type' => 'text',
			'wrapperAttributes' => [
               'class' => 'form-group col-md-3'
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