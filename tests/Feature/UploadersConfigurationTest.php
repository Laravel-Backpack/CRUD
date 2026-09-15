<?php

namespace Backpack\CRUD\Tests\Feature;

use Backpack\CRUD\Tests\config\CrudPanel\BaseDBCrudPanel;
use Backpack\CRUD\Tests\config\Http\Controllers\UploaderConfigurationCrudController;
use Backpack\CRUD\Tests\config\Models\Uploader;
use Backpack\CRUD\Tests\config\Models\User;
use Backpack\CRUD\Tests\config\Uploads\HasUploadedFiles;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @covers Backpack\CRUD\app\Library\Uploaders\Uploader
 * @covers Backpack\CRUD\app\Library\Uploaders\SingleFile
 * @covers Backpack\CRUD\app\Library\Uploaders\MultipleFiles
 * @covers Backpack\CRUD\app\Library\Uploaders\Support\RegisterUploadEvents
 * @covers Backpack\CRUD\app\Library\Uploaders\Support\UploadersRepository
 * @covers Backpack\CRUD\app\Library\Uploaders\Support\FileNameGenerator
 */
class UploadersConfigurationTest extends BaseDBCrudPanel
{
    use HasUploadedFiles;

    protected string $testBaseUrl;

    protected function defineRoutes($router)
    {
        $router->crud(config('backpack.base.route_prefix').'/uploader-configuration', UploaderConfigurationCrudController::class);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->testBaseUrl = config('backpack.base.route_prefix').'/uploader-configuration';
        Storage::fake('uploaders');
        $this->actingAs(User::find(1));
    }

    public function test_it_can_access_the_uploaders_create_page()
    {
        $response = $this->get($this->testBaseUrl.'/create');
        $response->assertStatus(200);
    }

    public function test_it_can_store_uploaded_files_using_our_file_name_generator()
    {
        $response = $this->post($this->testBaseUrl, [
            'upload' => $this->getUploadedFile('avatar1.jpg'),
            'upload_multiple' => $this->getUploadedFiles(['avatar2.jpg', 'avatar3.jpg']),
        ]);

        $response->assertStatus(302);

        $response->assertRedirect($this->testBaseUrl);

        $this->assertDatabaseCount('uploaders', 1);

        $files = Storage::disk('uploaders')->allFiles();

        $this->assertEquals(3, count($files));

        foreach ($files as $file) {
            $this->assertMatchesRegularExpression('/avatar\d{1}-[a-zA-Z0-9]{4}\.jpg/', $file);
        }

        // get the entry from database and also make sure the file names are stored correctly
        $entry = Uploader::first();
        $this->assertNotNull($entry);
        $this->assertMatchesRegularExpression('/avatar\d{1}-[a-zA-Z0-9]{4}\.jpg/', $entry->upload);
        $this->assertMatchesRegularExpression('/avatar\d{1}-[a-zA-Z0-9]{4}\.jpg/', $entry->upload_multiple[0]);
        $this->assertMatchesRegularExpression('/avatar\d{1}-[a-zA-Z0-9]{4}\.jpg/', $entry->upload_multiple[1]);
    }

    public function test_it_validates_the_file_namer_invalid_string()
    {
        $this->expectException(\Exception::class);

        $response = $this->get($this->testBaseUrl.'/invalid-file-namer');

        $response->assertStatus(500);

        throw $response->exception;
    }

    public function test_it_validates_the_file_namer_invalid_class()
    {
        $this->expectException(\Exception::class);

        $response = $this->get($this->testBaseUrl.'/invalid-file-namer-class');

        $response->assertStatus(500);

        throw $response->exception;
    }

    public function test_it_can_use_a_custom_uploader()
    {
        $response = $this->post($this->testBaseUrl.'/custom-uploader', [
            'upload' => $this->getUploadedFile('avatar1.jpg'),
        ]);

        $response->assertStatus(302);

        $response->assertRedirect($this->testBaseUrl);

        $this->assertDatabaseCount('uploaders', 1);

        $files = Storage::disk('uploaders')->allFiles();

        $this->assertEquals(1, count($files));
    }

    public static function activeContentFiles(): array
    {
        return [
            'svg' => ['<svg xmlns="http://www.w3.org/2000/svg"><script>alert(document.domain)</script></svg>'],
            'html' => ['<!DOCTYPE html><html><body><script>alert(document.domain)</script></body></html>'],
            'xml' => ['<?xml version="1.0"?><html xmlns="http://www.w3.org/1999/xhtml"><script>alert(document.domain)</script></html>'],
        ];
    }

    #[DataProvider('activeContentFiles')]
    public function test_it_does_not_store_active_content_uploads(string $content)
    {
        $response = $this->post($this->testBaseUrl, [
            'upload' => $this->getFileWithContent('avatar.png', $content),
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('upload');

        $this->assertDatabaseCount('uploaders', 0);
        $this->assertEmpty(Storage::disk('uploaders')->allFiles());
    }

    public function test_it_does_not_store_any_file_when_one_of_multiple_files_is_not_allowed()
    {
        $response = $this->post($this->testBaseUrl, [
            'upload_multiple' => [
                $this->getUploadedFile('avatar2.jpg'),
                $this->getFileWithContent('avatar.png', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'),
            ],
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('upload_multiple');

        $this->assertDatabaseCount('uploaders', 0);
        $this->assertEmpty(Storage::disk('uploaders')->allFiles());
    }

    public function test_it_keeps_previous_files_when_the_new_file_is_not_allowed()
    {
        Storage::disk('uploaders')->put('test/avatar1.jpg', 'previous');
        Storage::disk('uploaders')->put('avatar2.jpg', 'previous');

        Uploader::create(['upload' => 'test/avatar1.jpg', 'upload_multiple' => ['avatar2.jpg']]);

        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>';

        $response = $this->put($this->testBaseUrl.'/1', [
            'id' => 1,
            'upload' => $this->getFileWithContent('avatar.png', $svg),
        ]);

        $response->assertSessionHasErrors('upload');

        $response = $this->put($this->testBaseUrl.'/1', [
            'id' => 1,
            'upload_multiple' => [$this->getFileWithContent('avatar.png', $svg)],
            'clear_upload_multiple' => ['avatar2.jpg'],
        ]);

        $response->assertSessionHasErrors('upload_multiple');

        $this->assertDatabaseHas('uploaders', ['id' => 1, 'upload' => 'test/avatar1.jpg']);
        $this->assertSame(['avatar2.jpg'], json_decode(Uploader::query()->toBase()->find(1)->upload_multiple, true));
        Storage::disk('uploaders')->assertExists('test/avatar1.jpg');
        Storage::disk('uploaders')->assertExists('avatar2.jpg');
        $this->assertCount(2, Storage::disk('uploaders')->allFiles());
    }

    public function test_it_does_not_change_the_files_of_other_fields_when_a_file_is_not_allowed()
    {
        Storage::disk('uploaders')->put('test/avatar1.jpg', 'previous');

        Uploader::create(['upload' => 'test/avatar1.jpg']);

        // `upload` is processed before `upload_multiple`, so its previous file would be replaced before the svg is rejected
        $response = $this->put($this->testBaseUrl.'/1', [
            'id' => 1,
            'upload' => $this->getUploadedFile('avatar2.jpg'),
            'upload_multiple' => [$this->getFileWithContent('avatar.png', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>')],
        ]);

        $response->assertSessionHasErrors('upload_multiple');

        $this->assertDatabaseHas('uploaders', ['id' => 1, 'upload' => 'test/avatar1.jpg']);
        Storage::disk('uploaders')->assertExists('test/avatar1.jpg');
        $this->assertCount(1, Storage::disk('uploaders')->allFiles());
    }

    public function test_it_can_allow_extensions_per_field()
    {
        $response = $this->post($this->testBaseUrl.'/allowed-extensions', [
            'upload' => $this->getFileWithContent('logo.png', '<svg xmlns="http://www.w3.org/2000/svg"><circle r="10"/></svg>'),
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();

        $this->assertStringEndsWith('.svg', Uploader::first()->upload);
    }

    public function test_it_checks_names_given_by_custom_file_namers()
    {
        $response = $this->post($this->testBaseUrl.'/client-name-file-namer', [
            'upload' => $this->getFileWithContent('payload.html', 'just some text'),
        ]);

        $response->assertSessionHasErrors('upload');

        $response = $this->post($this->testBaseUrl.'/client-name-file-namer', [
            'upload' => $this->getFileWithContent('shell.php.jpg', 'just some text'),
        ]);

        $response->assertSessionHasErrors('upload');

        $this->assertDatabaseCount('uploaders', 0);
        $this->assertEmpty(Storage::disk('uploaders')->allFiles());
    }

    private function getFileWithContent(string $clientName, string $content): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'backpack-upload');
        file_put_contents($path, $content);

        return new UploadedFile($path, $clientName, null, null, true);
    }
}
