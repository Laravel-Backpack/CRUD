<!-- path: resources/views/vendor/backpack/crud/blocks/statistic.blade.php -->

<?php

// Title Block

$blocks['statistic'] = [
	'type' => 'statistic',
	'label' => 'Statistic',
	'icon'  => 'fa fa-line-chart',
	'fields' => [
		[
			'name' => 'number',
			'label' => 'Number',
			'type' => 'text',
		],
		[
			'name' => 'label',
			'label' => 'Label',
			'type' => 'text',
		],
		[
			'name' => 'unity',
			'label' => 'Unity',
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