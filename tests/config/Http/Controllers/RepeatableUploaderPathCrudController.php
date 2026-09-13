<?php

namespace Backpack\CRUD\Tests\config\Http\Controllers;

class RepeatableUploaderPathCrudController extends RepeatableUploaderCrudController
{
    protected string $uploadPath = 'uploads';

    protected string $routeSegment = 'repeatable-uploader-path';
}
