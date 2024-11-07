<?php

namespace Backpack\CRUD\Tests\config\Uploads;

use Illuminate\Http\UploadedFile;

trait HasUploadedFiles
{
    protected function getUploadedFile(string $fileName, string $mime = 'image/jpg')
    {
        return new UploadedFile(__DIR__.'/assets/'.$fileName, $fileName, $mime, null, true);
    }

    protected function getUploadedFiles(array $fileNames, string $mime = 'image/jpg')
    {
        return array_map(function ($fileName) use ($mime) {
            return new UploadedFile(__DIR__.'/assets/'.$fileName, $fileName, $mime, null, true);
        }, $fileNames);
    }

    protected function getBase64Image()
    {
        return 'data:image/jpg;base64,'.base64_encode(file_get_contents(__DIR__.'/assets/avatar1.jpg'));
    }
}
