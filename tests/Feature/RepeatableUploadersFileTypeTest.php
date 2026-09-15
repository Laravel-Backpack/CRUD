<?php

namespace Backpack\CRUD\Tests\Feature;

use Backpack\CRUD\Tests\config\CrudPanel\BaseDBCrudPanel;
use Backpack\CRUD\Tests\config\Http\Controllers\RepeatableUploaderCrudController;
use Backpack\CRUD\Tests\config\Models\User;
use Backpack\CRUD\Tests\config\Uploads\HasUploadedFiles;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Uploaders inside repeatable fields must reject files with extensions that are not allowed,
 * before any uploader in the form stores or deletes files.
 *
 * @covers Backpack\CRUD\app\Library\Uploaders\Uploader
 * @covers Backpack\CRUD\app\Library\Uploaders\Support\UploadersRepository
 */
class RepeatableUploadersFileTypeTest extends BaseDBCrudPanel
{
    use HasUploadedFiles;

    private const SVG = '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(document.domain)</script></svg>';

    protected string $testBaseUrl;

    protected function defineRoutes($router)
    {
        $router->crud(config('backpack.base.route_prefix').'/repeatable-uploader', RepeatableUploaderCrudController::class);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->testBaseUrl = config('backpack.base.route_prefix').'/repeatable-uploader';

        Storage::fake('uploaders');
        $this->actingAs(User::find(1));
    }

    public function test_repeatable_stores_allowed_files()
    {
        $response = $this->post($this->testBaseUrl, [
            'repeatable' => [
                [
                    'upload'          => $this->getUploadedFile('avatar1.jpg'),
                    'upload_multiple' => $this->getUploadedFiles(['avatar2.jpg', 'avatar3.jpg']),
                ],
            ],
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseCount('uploaders', 1);
        $this->assertEqualsCanonicalizing(['avatar1.jpg', 'avatar2.jpg', 'avatar3.jpg'], Storage::disk('uploaders')->allFiles());
    }

    public function test_repeatable_rejects_files_that_are_not_allowed_in_any_row()
    {
        $response = $this->post($this->testBaseUrl, [
            'repeatable' => [
                [
                    'upload'          => $this->getUploadedFile('avatar1.jpg'),
                    'upload_multiple' => $this->getUploadedFiles(['avatar2.jpg']),
                ],
                [
                    'upload_multiple' => [$this->getUploadedFile('avatar3.jpg'), $this->getFileWithContent('payload.html', 'just some text')],
                ],
            ],
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('repeatable.1.upload_multiple');

        $this->assertDatabaseCount('uploaders', 0);
        $this->assertEmpty(Storage::disk('uploaders')->allFiles());
    }

    public function test_repeatable_uses_the_allowed_extensions_config()
    {
        config(['backpack.crud.allowed_upload_extensions' => ['pdf']]);

        $response = $this->post($this->testBaseUrl, [
            'repeatable' => [
                ['upload' => $this->getUploadedFile('avatar1.jpg')],
            ],
        ]);

        $response->assertSessionHasErrors('repeatable.0.upload');

        $this->assertDatabaseCount('uploaders', 0);
        $this->assertEmpty(Storage::disk('uploaders')->allFiles());
    }

    public function test_repeatable_keeps_all_files_when_one_subfield_file_is_not_allowed()
    {
        foreach (['avatar1.jpg', 'avatar2.jpg', 'avatar3.jpg'] as $file) {
            UploadedFile::fake()->image($file)->storeAs('', $file, ['disk' => 'uploaders']);
        }

        $repeatable = json_encode([
            [
                'upload'          => 'avatar1.jpg',
                'upload_multiple' => ['avatar2.jpg', 'avatar3.jpg'],
                'image'           => null,
            ],
        ]);

        DB::table('uploaders')->insert(['id' => 1, 'repeatable' => $repeatable]);

        // `upload` is processed before `upload_multiple`, so it would replace its file before the svg is rejected
        $response = $this->put($this->testBaseUrl.'/1', [
            'id'         => 1,
            'repeatable' => [
                [
                    'upload'          => $this->getUploadedFile('pic1.jpg'),
                    'upload_multiple' => [$this->getFileWithContent('payload.svg', self::SVG)],
                ],
            ],
        ]);

        $response->assertSessionHasErrors('repeatable.0.upload_multiple');

        $this->assertSame($repeatable, DB::table('uploaders')->where('id', 1)->value('repeatable'));

        $this->assertEqualsCanonicalizing(['avatar1.jpg', 'avatar2.jpg', 'avatar3.jpg'], Storage::disk('uploaders')->allFiles());
    }

    private function getFileWithContent(string $clientName, string $content): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'backpack-upload');
        file_put_contents($path, $content);

        return new UploadedFile($path, $clientName, null, null, true);
    }
}
