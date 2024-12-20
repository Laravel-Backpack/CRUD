<?php

namespace Backpack\CRUD\Tests\Feature;

use Backpack\CRUD\Tests\config\CrudPanel\BaseDBCrudPanel;
use Backpack\CRUD\Tests\config\Http\Controllers\UploaderValidationCrudController;
use Backpack\CRUD\Tests\config\Models\Uploader;
use Backpack\CRUD\Tests\config\Models\User;
use Backpack\CRUD\Tests\config\Uploads\HasUploadedFiles;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * @covers Backpack\CRUD\app\Library\Validation\Rules\BackpackCustomRule
 * @covers Backpack\CRUD\app\Library\Validation\Rules\ValidUpload
 * @covers Backpack\CRUD\app\Library\Validation\Rules\ValidUploadMultiple
 */
class UploadersValidationTest extends BaseDBCrudPanel
{
    use HasUploadedFiles;

    protected string $testBaseUrl;

    protected function defineRoutes($router)
    {
        $router->crud(config('backpack.base.route_prefix').'/uploader-validation', UploaderValidationCrudController::class);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->testBaseUrl = config('backpack.base.route_prefix').'/uploader-validation';

        Storage::fake('uploaders');
        $this->actingAs(User::find(1));
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
            'upload' => 'avatar1.jpg',
            'upload_multiple' => json_encode(['avatar2.jpg',  'avatar3.jpg']),
        ]);

        $this->assertTrue(Storage::disk('uploaders')->exists('avatar1.jpg'));
        $this->assertTrue(Storage::disk('uploaders')->exists('avatar2.jpg'));
        $this->assertTrue(Storage::disk('uploaders')->exists('avatar3.jpg'));
    }

    public function test_it_display_the_edit_page_without_files()
    {
        self::initUploader();

        $response = $this->get($this->testBaseUrl.'/1/edit');
        $response->assertStatus(200);
    }

    public function test_it_display_the_upload_page_with_files()
    {
        self::initUploaderWithImages();

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
            'upload' => $this->getUploadedFile('avatar4.jpg'),
            'upload_multiple' => $this->getUploadedFiles(['avatar5.jpg',  'avatar6.jpg']),
            'clear_upload_multiple' => ['avatar2.jpg',  'avatar3.jpg'],
            'id' => 1,
        ]);

        $response->assertStatus(302);

        $response->assertRedirect($this->testBaseUrl);

        $this->assertDatabaseCount('uploaders', 1);

        $this->assertDatabaseHas('uploaders', [
            'upload' => 'avatar4.jpg',
            'upload_multiple' => json_encode(['avatar5.jpg',  'avatar6.jpg']),
        ]);

        $files = Storage::disk('uploaders')->allFiles();

        $this->assertEquals(3, count($files));

        $this->assertTrue(Storage::disk('uploaders')->exists('avatar4.jpg'));
        $this->assertTrue(Storage::disk('uploaders')->exists('avatar5.jpg'));
        $this->assertTrue(Storage::disk('uploaders')->exists('avatar6.jpg'));
    }

    public function test_it_can_delete_uploaded_files()
    {
        self::initUploaderWithImages();

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
            'upload_multiple' => ['avatar2.jpg',  'avatar3.jpg'],
            'id' => 1,
        ]);

        $response->assertStatus(302);

        $response->assertRedirect($this->testBaseUrl);

        $this->assertDatabaseCount('uploaders', 1);

        $this->assertDatabaseHas('uploaders', [
            'upload' => 'avatar1.jpg',
            'upload_multiple' => json_encode(['avatar2.jpg',  'avatar3.jpg']),
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
            'upload' => 'avatar1.jpg',
            'upload_multiple' => json_encode(['avatar3.jpg', 'avatar4.jpg',  'avatar5.jpg']),
        ]);

        $files = Storage::disk('uploaders')->allFiles();

        $this->assertEquals(4, count($files));

        $this->assertTrue(Storage::disk('uploaders')->exists('avatar1.jpg'));
        $this->assertTrue(Storage::disk('uploaders')->exists('avatar3.jpg'));
        $this->assertTrue(Storage::disk('uploaders')->exists('avatar4.jpg'));
        $this->assertTrue(Storage::disk('uploaders')->exists('avatar5.jpg'));
    }

    public function test_it_validates_files_on_a_single_upload()
    {
        $response = $this->post($this->testBaseUrl, [
            'upload' => 'not-a-file',
            'upload_multiple' => $this->getUploadedFiles(['avatar1.jpg', 'avatar2.jpg']),
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('upload');

        $this->assertDatabaseCount('uploaders', 0);
    }

    public function test_it_validates_files_on_multiple_uploader()
    {
        $response = $this->post($this->testBaseUrl, [
            'upload' => $this->getUploadedFile('avatar1.jpg'),
            'upload_multiple' => array_merge($this->getUploadedFiles(['avatar1.jpg']), ['not-a-file']),
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('upload_multiple');

        $this->assertDatabaseCount('uploaders', 0);
    }

    public function test_it_validates_mime_types_on_single_and_multi_uploads()
    {
        $response = $this->post($this->testBaseUrl, [
            'upload' => $this->getUploadedFile('avatar1.pdf', 'application/pdf'),
            'upload_multiple' => $this->getUploadedFiles(['avatar1.pdf', 'avatar1.pdf'], 'application/pdf'),
        ]);

        $response->assertStatus(302);

        $response->assertSessionHasErrors('upload_multiple');
        $response->assertSessionHasErrors('upload');

        // assert the error content
        $this->assertEquals('The upload multiple field must be a file of type: jpg.', session('errors')->get('upload_multiple')[0]);
        $this->assertEquals('The upload field must be a file of type: jpg.', session('errors')->get('upload')[0]);

        $this->assertDatabaseCount('uploaders', 0);
    }

    public function test_it_validates_file_size_on_single_and_multi_uploads()
    {
        $response = $this->post($this->testBaseUrl, [
            'upload' => $this->getUploadedFile('avatar1_big.jpg'),
            'upload_multiple' => $this->getUploadedFiles(['avatar2_big.jpg', 'avatar3_big.jpg']),
        ]);

        $response->assertStatus(302);

        $response->assertSessionHasErrors('upload_multiple');
        $response->assertSessionHasErrors('upload');

        // assert the error content
        $this->assertEquals('The upload multiple field must not be greater than 100 kilobytes.', session('errors')->get('upload_multiple')[0]);
        $this->assertEquals('The upload field must not be greater than 100 kilobytes.', session('errors')->get('upload')[0]);

        $this->assertDatabaseCount('uploaders', 0);
    }

    public function test_it_validates_min_files_on_multi_uploads()
    {
        $response = $this->post($this->testBaseUrl, [
            'upload' => $this->getUploadedFile('avatar1.jpg'),
            'upload_multiple' => $this->getUploadedFiles(['avatar2.jpg']),
        ]);

        $response->assertStatus(302);

        $response->assertSessionHasErrors('upload_multiple');

        // assert the error content
        $this->assertEquals('The upload multiple field must have at least 2 items.', session('errors')->get('upload_multiple')[0]);

        $this->assertDatabaseCount('uploaders', 0);
    }

    public function test_it_validates_required_files_on_single_and_multi_uploads()
    {
        $response = $this->post($this->testBaseUrl, [
            'upload' => null,
            'upload_multiple' => null,
        ]);

        $response->assertStatus(302);

        $response->assertSessionHasErrors('upload');
        $response->assertSessionHasErrors('upload_multiple');

        // assert the error content
        $this->assertEquals('The upload field is required.', session('errors')->get('upload')[0]);
        $this->assertEquals('The upload multiple field is required.', session('errors')->get('upload_multiple')[0]);

        $this->assertDatabaseCount('uploaders', 0);
    }

    public function test_it_validates_required_when_not_present_in_request()
    {
        $response = $this->post($this->testBaseUrl, []);

        $response->assertStatus(302);

        $response->assertSessionHasErrors('upload');
        $response->assertSessionHasErrors('upload_multiple');

        $this->assertEquals('The upload field is required.', session('errors')->get('upload')[0]);
        $this->assertEquals('The upload multiple field is required.', session('errors')->get('upload_multiple')[0]);

        $this->assertDatabaseCount('uploaders', 0);
    }

    public function test_it_validates_required_files_on_single_and_multi_uploads_when_updating()
    {
        self::initUploader();

        $response = $this->put($this->testBaseUrl.'/1', [
            'upload' => null,
            'upload_multiple' => null,
            'id' => 1,
        ]);

        $response->assertStatus(302);

        $response->assertSessionHasErrors('upload');
        $response->assertSessionHasErrors('upload_multiple');

        // assert the error content
        $this->assertEquals('The upload field is required.', session('errors')->get('upload')[0]);
        $this->assertEquals('The upload multiple field is required.', session('errors')->get('upload_multiple')[0]);

        $this->assertDatabaseCount('uploaders', 1);
    }

    public function test_it_validates_required_files_on_single_and_multi_uploads_when_updating_with_files()
    {
        self::initUploaderWithFiles();

        $response = $this->put($this->testBaseUrl.'/1', [
            'upload' => null,
            'upload_multiple' => null,
            'clear_upload_multiple' => ['avatar2.jpg',  'avatar3.jpg'],
            'id' => 1,
        ]);

        $response->assertStatus(302);

        $response->assertSessionHasErrors('upload');
        $response->assertSessionHasErrors('upload_multiple');

        // assert the error content
        $this->assertEquals('The upload field is required.', session('errors')->get('upload')[0]);
        $this->assertEquals('The upload multiple field is required.', session('errors')->get('upload_multiple')[0]);

        $this->assertDatabaseCount('uploaders', 1);
    }

    public function test_it_validates_min_files_on_multi_uploads_when_updating()
    {
        self::initUploaderWithFiles();

        $response = $this->put($this->testBaseUrl.'/1', [
            'upload_multiple' => $this->getUploadedFiles(['avatar2.jpg']),
            'clear_upload_multiple' => ['avatar2.jpg',  'avatar3.jpg'],
            'id' => 1,
        ]);

        $response->assertStatus(302);

        $response->assertSessionHasErrors('upload_multiple');

        // assert the error content
        $this->assertEquals('The upload multiple field must have at least 2 items.', session('errors')->get('upload_multiple')[0]);

        $this->assertDatabaseCount('uploaders', 1);
    }

    protected static function initUploaderWithImages()
    {
        UploadedFile::fake()->image('avatar1.jpg')->storeAs('', 'avatar1.jpg', ['disk' => 'uploaders']);
        UploadedFile::fake()->image('avatar2.jpg')->storeAs('', 'avatar2.jpg', ['disk' => 'uploaders']);
        UploadedFile::fake()->image('avatar3.jpg')->storeAs('', 'avatar3.jpg', ['disk' => 'uploaders']);

        Uploader::create([
            'upload' => 'avatar1.jpg',
            'upload_multiple' => json_encode(['avatar2.jpg',  'avatar3.jpg']),
        ]);
    }

    protected static function initUploaderWithFiles()
    {
        UploadedFile::fake()->create('avatar1.jpg')->storeAs('', 'avatar1.jpg', ['disk' => 'uploaders']);
        UploadedFile::fake()->create('avatar2.jpg')->storeAs('', 'avatar2.jpg', ['disk' => 'uploaders']);
        UploadedFile::fake()->create('avatar3.jpg')->storeAs('', 'avatar3.jpg', ['disk' => 'uploaders']);

        Uploader::create([
            'upload' => 'avatar1.jpg',
            'upload_multiple' => json_encode(['avatar2.jpg',  'avatar3.jpg']),
        ]);
    }

    protected static function initUploader()
    {
        Uploader::create([
            'upload' => null,
            'upload_multiple' => null,
        ]);
    }
}
