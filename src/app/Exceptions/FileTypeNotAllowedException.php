<?php

namespace Backpack\CRUD\app\Exceptions;

class FileTypeNotAllowedException extends \InvalidArgumentException
{
    public function __construct(public readonly string $extension)
    {
        parent::__construct("File type '.$extension' is not allowed.");
    }
}
