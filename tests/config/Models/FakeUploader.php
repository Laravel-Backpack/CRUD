<?php

namespace Backpack\CRUD\Tests\config\Models;

class FakeUploader extends Uploader
{
    protected $table = 'uploaders';

    protected $casts = [
        'extras' => 'array',
    ];

    protected $fakeColumns = ['extras'];
}
