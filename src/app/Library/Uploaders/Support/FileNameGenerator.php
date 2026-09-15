<?php

namespace Backpack\CRUD\app\Library\Uploaders\Support;

use Backpack\CRUD\app\Exceptions\FileTypeNotAllowedException;
use Backpack\CRUD\app\Library\Uploaders\Support\Interfaces\FileNameGeneratorInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\File;

class FileNameGenerator implements FileNameGeneratorInterface
{
    public function getName(string|UploadedFile|File $file): string
    {
        if (is_object($file) && get_class($file) === File::class) {
            return $file->getFileName();
        }

        return $this->getFileName($file).'.'.$this->getExtensionFromFile($file);
    }

    /**
     * @deprecated use FileExtensions::DISALLOWED
     */
    public static function getDangerousExtensions(): array
    {
        return FileExtensions::DISALLOWED;
    }

    private function getExtensionFromFile(string|UploadedFile $file): string
    {
        if (is_a($file, UploadedFile::class, true)) {
            $ext = $file->extension();
        } elseif (Str::startsWith($file, 'data:')) {
            preg_match('#^data:([^;]+);#', $file, $m);
            $ext = Str::after($m[1] ?? '', '/');
        } else {
            $mime = mime_content_type($file);
            $ext = $mime !== false ? Str::after($mime, '/') : '';
        }

        $ext = strtolower((string) $ext);

        if (FileExtensions::isDisallowed($ext)) {
            throw new FileTypeNotAllowedException($ext);
        }

        return $ext === '' ? FileExtensions::FALLBACK : $ext;
    }

    private function getFileName(string|UploadedFile $file): string
    {
        if (is_file($file)) {
            return Str::of($file->getClientOriginalName())->beforeLast('.')->slug()->append('-'.Str::random(4));
        }

        return Str::random(40);
    }
}
