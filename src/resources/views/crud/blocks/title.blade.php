<!-- path: resources/views/vendor/backpack/crud/blocks/title.blade.php -->

<?php

// Title Block

$blocks['title'] = [
	'type' => 'title',
	'label' => 'Title',
	'icon'  => 'fa fa-text-height',
	'fields' => [
		[
			'name' => 'content',
			'label' => 'Content',
			'type' => 'text',
		],
		[
			'name' => 'text_block_separator',
			'type' => 'custom_html',
			'value' => '<hr>',
		],
		// [
			// 'name' => 'start_well',
			// 'type' => 'start_div',
			// 'wrapperAttributes' => [
            //    'class' => 'well well-sm col-md-12 m-t-20 m-b-0'
            //  ],
		// ],
		[
			'name' => 'class',
			'label' => 'Class',
			'type' => 'text',
			'wrapperAttributes' => [
               'class' => 'form-group col-md-6'
             ],
		],
		[
			'name' => 'id',
			'label' => 'ID',
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
		// [
			// 'name' => 'end_well',
			// 'type' => 'end_div',
		// ],
	],
];

?>