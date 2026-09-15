<?php

namespace Backpack\CRUD\Tests\Unit\Uploaders;

use Backpack\CRUD\app\Exceptions\FileTypeNotAllowedException;
use Backpack\CRUD\app\Library\Uploaders\Support\FileExtensions;
use Backpack\CRUD\app\Library\Uploaders\Support\FileNameGenerator;
use Backpack\CRUD\Tests\config\CrudPanel\BaseCrudPanel;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @covers Backpack\CRUD\app\Library\Uploaders\Support\FileExtensions
 * @covers Backpack\CRUD\app\Library\Uploaders\Support\FileNameGenerator
 */
class FileExtensionsTest extends BaseCrudPanel
{
    public static function allowedFileNames(): array
    {
        return [['avatar.jpg'], ['avatar.JPG'], ['folder/report.pdf'], ['folder\\sheet.xlsx'], ['data.bin'], ['some.name.png']];
    }

    public static function notAllowedFileNames(): array
    {
        return [
            ['payload.svg'], ['payload.svgz'], ['payload.html'], ['payload.HTM'], ['payload.xhtml'], ['payload.xml'],
            ['payload.js'], ['shell.php'], ['shell.php.jpg'], ['shell.PHAR.png'], ['shell.php .jpg'], ['.htaccess'],
            ['avatar.jpg '], ['no-extension'], ['trailing-dot.'],
        ];
    }

    #[DataProvider('allowedFileNames')]
    public function test_it_allows_default_extensions(string $fileName)
    {
        FileExtensions::ensureFileNameIsAllowed($fileName);

        $this->addToAssertionCount(1);
    }

    #[DataProvider('notAllowedFileNames')]
    public function test_it_rejects_extensions_that_are_not_allowed(string $fileName)
    {
        $this->expectException(FileTypeNotAllowedException::class);

        FileExtensions::ensureFileNameIsAllowed($fileName);
    }

    public function test_it_uses_the_allowed_extensions_from_config()
    {
        config(['backpack.crud.allowed_upload_extensions' => ['svg']]);

        FileExtensions::ensureFileNameIsAllowed('logo.svg');

        $this->expectException(FileTypeNotAllowedException::class);

        FileExtensions::ensureFileNameIsAllowed('avatar.jpg');
    }

    public function test_it_falls_back_to_the_default_extensions_when_config_is_missing()
    {
        config(['backpack.crud.allowed_upload_extensions' => null]);

        $this->assertSame(FileExtensions::DEFAULT_ALLOWED, FileExtensions::allowed());
    }

    public function test_it_uses_the_given_allowed_extensions_over_config()
    {
        FileExtensions::ensureFileNameIsAllowed('logo.svg', ['.SVG']);

        $this->expectException(FileTypeNotAllowedException::class);

        FileExtensions::ensureFileNameIsAllowed('avatar.jpg', ['svg']);
    }

    public function test_it_never_allows_server_executable_extensions()
    {
        $this->assertNotContains('php', FileExtensions::allowed(['php', 'jpg']));

        $this->expectException(FileTypeNotAllowedException::class);

        FileExtensions::ensureFileNameIsAllowed('shell.php', ['php']);
    }

    public function test_it_reports_the_extension_that_is_not_allowed()
    {
        try {
            FileExtensions::ensureFileNameIsAllowed('shell.php.jpg');
        } catch (FileTypeNotAllowedException $e) {
            $this->assertSame('php', $e->extension);
            $this->assertInstanceOf(\InvalidArgumentException::class, $e);

            return;
        }

        $this->fail('The file name should not be allowed.');
    }

    public function test_file_name_generator_uses_the_content_extension()
    {
        $name = (new FileNameGenerator())->getName($this->getFileWithContent('payload.png', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'));

        $this->assertStringEndsWith('.svg', $name);
    }

    public function test_file_name_generator_uses_a_fallback_extension_for_unknown_content()
    {
        $name = (new FileNameGenerator())->getName($this->getFileWithContent('document.doc', ''));

        $this->assertStringEndsWith('.'.FileExtensions::FALLBACK, $name);
    }

    public function test_file_name_generator_keeps_the_dangerous_extensions_list()
    {
        $this->assertSame(FileExtensions::DISALLOWED, FileNameGenerator::getDangerousExtensions());
    }

    private function getFileWithContent(string $clientName, string $content): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'backpack-upload');
        file_put_contents($path, $content);

        return new UploadedFile($path, $clientName, null, null, true);
    }
}
