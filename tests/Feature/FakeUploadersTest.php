<?php

namespace Backpack\CRUD\Tests\Feature;

use Backpack\CRUD\Tests\config\CrudPanel\BaseDBCrudPanel;
use Backpack\CRUD\Tests\config\Http\Controllers\FakeUploaderCrudController;
use Backpack\CRUD\Tests\config\Models\FakeUploader;
use Backpack\CRUD\Tests\config\Models\User;
use Backpack\CRUD\Tests\config\Uploads\HasUploadedFiles;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * @covers Backpack\CRUD\app\Library\Uploaders\Uploader
 * @covers Backpack\CRUD\app\Library\Uploaders\SingleFile
 * @covers Backpack\CRUD\app\Library\Uploaders\MultipleFiles
 */
class FakeUploadersTest extends BaseDBCrudPanel
{
    use HasUploadedFiles;

    protected string $testBaseUrl;

    protected function defineRoutes($router)
    {
        $router->crud(config('backpack.base.route_prefix').'/fake-uploader', FakeUploaderCrudController::class);
    }

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('uploaders');
        $this->actingAs(User::find(1));
        $this->testBaseUrl = config('backpack.base.route_prefix').'/fake-uploader';
    }

    public function test_it_can_access_the_uploaders_create_page()
    {
        $response = $this->get($this->testBaseUrl.'/create');
        $response->assertStatus(200);
    }

    public function test_it_can_store_uploaded_files()
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

        $this->assertDatabaseHas('uploaders', [
            'id' => 1,
            'extras' => json_encode(['upload' => 'avatar1.jpg', 'upload_multiple' => ['avatar2.jpg', 'avatar3.jpg']]),
        ]);
    }

    public function test_it_display_the_edit_page_without_files()
    {
        self::initUploader();

        $response = $this->get($this->testBaseUrl.'/1/edit');
        $response->assertStatus(200);
    }

    public function test_it_display_the_upload_page_with_files()
    {
        self::initUploaderWithFiles();
        $response = $this->get($this->testBaseUrl.'/1/edit');

        $response->assertStatus(200);

        $response->assertSee('avatar1.jpg');
        $response->assertSee('avatar2.jpg');
        $response->assertSee('avatar3.jpg');
    }

    public function test_it_can_update_uploaded_files()
    {
        self::initUploaderWithFiles();

        $response = $this->put($this->testBaseUrl.'/1', [
            '_method' => 'PUT',
            'upload' => $this->getUploadedFile('avatar4.jpg'),
            'upload_multiple' => $this->getUploadedFiles(['avatar5.jpg', 'avatar6.jpg']),
            'clear_upload_multiple' => ['avatar2.jpg',  'avatar3.jpg'],
            'id' => 1,
        ]);

        $response->assertStatus(302);

        $response->assertRedirect($this->testBaseUrl);

        $this->assertDatabaseCount('uploaders', 1);

        $this->assertDatabaseHas('uploaders', [
            'extras' => json_encode(['upload' => 'avatar4.jpg', 'upload_multiple' => ['avatar5.jpg', 'avatar6.jpg']]),
        ]);

        $files = Storage::disk('uploaders')->allFiles();

        $this->assertEquals(3, count($files));

        $this->assertTrue(Storage::disk('uploaders')->exists('avatar4.jpg'));
        $this->assertTrue(Storage::disk('uploaders')->exists('avatar5.jpg'));
        $this->assertTrue(Storage::disk('uploaders')->exists('avatar6.jpg'));
    }

    public function test_single_upload_deletes_files_when_updated_without_values()
    {
        self::initUploaderWithFiles();

        $response = $this->put($this->testBaseUrl.'/1', [
            'upload' => null,
            'id' => 1,
        ]);

        $response->assertStatus(302);

        $response->assertRedirect($this->testBaseUrl);

        $this->assertDatabaseCount('uploaders', 1);

        $this->assertDatabaseHas('uploaders', [
            'extras' => json_encode(['upload' => null, 'upload_multiple' => ['avatar2.jpg', 'avatar3.jpg']]),
        ]);

        $files = Storage::disk('uploaders')->allFiles();

        $this->assertEquals(2, count($files));

        $this->assertFalse(Storage::disk('uploaders')->exists('avatar1.jpg'));
    }

    public function test_it_can_delete_uploaded_files()
    {
        self::initUploaderWithFiles();

        $response = $this->delete($this->testBaseUrl.'/1');

        $response->assertStatus(200);

        $this->assertDatabaseCount('uploaders', 0);

        $files = Storage::disk('uploaders')->allFiles();

        $this->assertEquals(0, count($files));
    }

    public function test_it_keeps_previous_values_unchaged_when_not_deleted()
    {
        self::initUploaderWithFiles();

        $response = $this->put($this->testBaseUrl.'/1', [
            'upload_multiple' => [null],
            'id' => 1,
        ]);

        $response->assertStatus(302);

        $response->assertRedirect($this->testBaseUrl);

        $this->assertDatabaseCount('uploaders', 1);

        $this->assertDatabaseHas('uploaders', [
            'extras' => json_encode(['upload_multiple' => ['avatar2.jpg',  'avatar3.jpg'], 'upload' => 'avatar1.jpg']),
        ]);

        $files = Storage::disk('uploaders')->allFiles();

        $this->assertEquals(3, count($files));

        $this->assertTrue(Storage::disk('uploaders')->exists('avatar1.jpg'));
        $this->assertTrue(Storage::disk('uploaders')->exists('avatar2.jpg'));
        $this->assertTrue(Storage::disk('uploaders')->exists('avatar3.jpg'));
    }

    public function test_upload_multiple_can_delete_uploaded_files_and_add_at_the_same_time()
    {
        self::initUploaderWithFiles();

        $response = $this->put($this->testBaseUrl.'/1', [
            'upload_multiple' => $this->getUploadedFiles(['avatar4.jpg',  'avatar5.jpg']),
            'clear_upload_multiple' => ['avatar2.jpg'],
            'id' => 1,
        ]);

        $response->assertStatus(302);

        $response->assertRedirect($this->testBaseUrl);

        $this->assertDatabaseCount('uploaders', 1);

        $this->assertDatabaseHas('uploaders', [
            'extras' => json_encode(['upload_multiple' => ['avatar3.jpg', 'avatar4.jpg',  'avatar5.jpg'], 'upload' => 'avatar1.jpg']),
        ]);

        $files = Storage::disk('uploaders')->allFiles();

        $this->assertEquals(4, count($files));

        $this->assertTrue(Storage::disk('uploaders')->exists('avatar1.jpg'));
        $this->assertTrue(Storage::disk('uploaders')->exists('avatar3.jpg'));
        $this->assertTrue(Storage::disk('uploaders')->exists('avatar4.jpg'));
        $this->assertTrue(Storage::disk('uploaders')->exists('avatar5.jpg'));
    }

    protected static function initUploaderWithFiles()
    {
        UploadedFile::fake()->image('avatar1.jpg')->storeAs('', 'avatar1.jpg', ['disk' => 'uploaders']);
        UploadedFile::fake()->image('avatar2.jpg')->storeAs('', 'avatar2.jpg', ['disk' => 'uploaders']);
        UploadedFile::fake()->image('avatar3.jpg')->storeAs('', 'avatar3.jpg', ['disk' => 'uploaders']);

        FakeUploader::create([
            'extras' => ['upload' => 'avatar1.jpg', 'upload_multiple' => ['avatar2.jpg',  'avatar3.jpg']],
        ]);
    }

    protected static function initUploader()
    {
        FakeUploader::create([
            'extras' => ['upload' => null, 'upload_multiple' => null],
        ]);
    }
}
