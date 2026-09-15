<?php

namespace Backpack\CRUD\app\Library\Uploaders\Support;

use Backpack\CRUD\app\Exceptions\FileTypeNotAllowedException;

class FileExtensions
{
    /**
     * Extensions uploaded files can be stored with, when the developer does not configure them.
     *
     * Extensions that browsers render as active content (svg, html, xml, js, etc.) are left out
     * on purpose, because those files can run scripts when opened from the application domain.
     */
    public const DEFAULT_ALLOWED = [
        // images
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'bmp', 'tif', 'tiff', 'ico', 'heic', 'heif',
        // documents
        'pdf', 'txt', 'csv', 'rtf', 'json', 'epub',
        'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'odt', 'ods', 'odp',
        // archives
        'zip', 'rar', '7z', 'gz', 'tgz', 'tar', 'bz2',
        // audio
        'mp3', 'wav', 'ogg', 'oga', 'opus', 'm4a', 'aac', 'flac', 'weba',
        // video
        'mp4', 'm4v', 'webm', 'mov', 'avi', 'mkv', 'mpeg', 'mpg', 'ogv', '3gp',
        // content that could not be identified
        'bin',
    ];

    /**
     * Extensions that web servers may execute. They are never allowed, not even when the developer
     * adds them to the allowed extensions, and they are checked in every segment of the file name
     * so names like `shell.php.jpg` are rejected too.
     */
    public const DISALLOWED = [
        'php', 'php3', 'php4', 'php5', 'php6', 'php7', 'php8',
        'phtml', 'phtm', 'pht', 'phps', 'phar',
        'shtml', 'shtm', 'stm',
        'pl', 'py', 'rb', 'jsp', 'jspx', 'cgi',
        'asp', 'aspx',
        'sh', 'bash', 'bat', 'cmd', 'exe',
        'htaccess', 'htpasswd',
    ];

    /**
     * Extension used for uploaded files whose content type has no known extension.
     */
    public const FALLBACK = 'bin';

    /**
     * Get the allowed extensions, from the given list or from the `backpack.crud.allowed_upload_extensions` config.
     */
    public static function allowed(?array $extensions = null): array
    {
        $extensions ??= config('backpack.crud.allowed_upload_extensions') ?? self::DEFAULT_ALLOWED;

        return array_values(array_diff(self::normalize((array) $extensions), self::DISALLOWED));
    }

    public static function isDisallowed(string $extension): bool
    {
        return in_array(self::normalizeExtension($extension), self::DISALLOWED, true);
    }

    /**
     * Get the extension a web server would use to serve the file.
     */
    public static function fromFileName(string $fileName): string
    {
        $baseName = self::baseName($fileName);

        return str_contains($baseName, '.') ? strtolower(substr($baseName, strrpos($baseName, '.') + 1)) : '';
    }

    /**
     * @throws FileTypeNotAllowedException
     */
    public static function ensureFileNameIsAllowed(string $fileName, ?array $allowedExtensions = null): void
    {
        $segments = explode('.', self::baseName($fileName));
        array_shift($segments);

        foreach ($segments as $segment) {
            if (self::isDisallowed($segment)) {
                throw new FileTypeNotAllowedException(self::normalizeExtension($segment));
            }
        }

        $extension = self::fromFileName($fileName);

        if (! in_array($extension, self::allowed($allowedExtensions), true)) {
            throw new FileTypeNotAllowedException($extension);
        }
    }

    private static function baseName(string $fileName): string
    {
        $fileName = str_replace('\\', '/', $fileName);

        return str_contains($fileName, '/') ? substr($fileName, strrpos($fileName, '/') + 1) : $fileName;
    }

    private static function normalize(array $extensions): array
    {
        return array_map(fn ($extension) => self::normalizeExtension((string) $extension), $extensions);
    }

    private static function normalizeExtension(string $extension): string
    {
        return strtolower(ltrim(preg_replace('/[\x00-\x20]/', '', $extension), '.'));
    }
}
