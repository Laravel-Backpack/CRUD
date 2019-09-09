<!-- path: resources/views/vendor/backpack/crud/blocks/faq.blade.php -->

<?php

// FAQ Block

$blocks['faq'] = [
	'type' => 'faq',
	'label' => 'FAQ',
	'icon'  => 'fa fa-comments-o',
	'fields' => [
		[
			'name' => 'entries',
			'label' => 'Entries',
			'type' => 'multiply',
			'fields' => [
				[
					'name' => 'question',
					'label' => 'Question',
					'type' => 'textarea',
					'wrapperAttributes' => [
		               'class' => 'form-group col-md-6'
		             ],
				],
				[
					'name' => 'answer',
					'label' => 'Answer',
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