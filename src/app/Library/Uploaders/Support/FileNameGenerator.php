<?php

namespace Backpack\CRUD\app\Library\Uploaders\Support;

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

    public static function getDangerousExtensions(): array
    {
        return [
            'php', 'php3', 'php4', 'php5', 'php7', 'php8',
            'phtml', 'phar', 'phps', 'shtml',
            'pl', 'py', 'rb', 'jsp', 'cgi',
            'asp', 'aspx',
            'sh', 'bash', 'bat', 'cmd', 'exe',
            'htaccess',
        ];
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

        if (in_array(strtolower((string) $ext), self::getDangerousExtensions(), true)) {
            throw new \InvalidArgumentException("File type '.$ext' is not allowed.");
        }

        return (string) $ext;
    }

    private function getFileName(string|UploadedFile $file): string
    {
        if (is_file($file)) {
            return Str::of($file->getClientOriginalName())->beforeLast('.')->slug()->append('-'.Str::random(4));
        }

        return Str::random(40);
    }
}
