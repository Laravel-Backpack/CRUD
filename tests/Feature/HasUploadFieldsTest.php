<?php

namespace Backpack\CRUD\Tests\Feature;

use Backpack\CRUD\Tests\config\CrudPanel\BaseDBCrudPanel;
use Backpack\CRUD\Tests\config\Models\Uploader;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * @covers Backpack\CRUD\app\Models\Traits\HasUploadFields
 */
class HasUploadFieldsTest extends BaseDBCrudPanel
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('uploaders');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function createUploaderWithFiles(array $files): Uploader
    {
        foreach ($files as $path) {
            UploadedFile::fake()->image(basename($path))->storeAs(
                dirname($path) === '.' ? '' : dirname($path),
                basename($path),
                ['disk' => 'uploaders']
            );
        }

        return Uploader::find(
            Uploader::create(['upload_multiple' => json_encode($files)])->id
        );
    }

    private function fakeRequest(array $input): void
    {
        $request = Request::create('/admin/uploader/1', 'PUT', $input);
        $this->app->instance('request', $request);
    }

    public function test_uploadMultipleFilesToDisk_does_not_delete_files_not_owned_by_the_model(): void
    {
        $uploader = $this->createUploaderWithFiles(['owned1.jpg']);

        // A file that belongs to a different record / is a system file.
        UploadedFile::fake()->image('victim.jpg')->storeAs('', 'victim.jpg', ['disk' => 'uploaders']);

        Storage::disk('uploaders')->assertExists('owned1.jpg');
        Storage::disk('uploaders')->assertExists('victim.jpg');

        // Attacker submits clear list containing a path they do not own.
        $this->fakeRequest(['clear_upload_multiple' => ['owned1.jpg', 'victim.jpg']]);

        $uploader->uploadMultipleFilesToDisk(null, 'upload_multiple', 'uploaders', '');

        // Non-owned file must survive.
        Storage::disk('uploaders')->assertExists('victim.jpg');
    }

    /**
     * Path in a sub-directory that the model does not own must not be deleted.
     */
    public function test_uploadMultipleFilesToDisk_does_not_delete_files_in_other_paths(): void
    {
        $uploader = $this->createUploaderWithFiles(['attachments/my-doc.pdf']);

        // A file in a completely different directory on the same disk.
        UploadedFile::fake()->create('report.pdf')->storeAs('private', 'report.pdf', ['disk' => 'uploaders']);

        Storage::disk('uploaders')->assertExists('attachments/my-doc.pdf');
        Storage::disk('uploaders')->assertExists('private/report.pdf');

        $this->fakeRequest(['clear_upload_multiple' => ['private/report.pdf']]);

        $uploader->uploadMultipleFilesToDisk(null, 'upload_multiple', 'uploaders', '');

        Storage::disk('uploaders')->assertExists('private/report.pdf');
        // The model's own file was not requested for deletion, so it stays too.
        Storage::disk('uploaders')->assertExists('attachments/my-doc.pdf');
    }

    public function test_uploadMultipleFilesToDisk_deletes_owned_file_when_requested(): void
    {
        $uploader = $this->createUploaderWithFiles(['owned1.jpg', 'owned2.jpg']);

        $this->fakeRequest(['clear_upload_multiple' => ['owned1.jpg']]);

        $uploader->uploadMultipleFilesToDisk(null, 'upload_multiple', 'uploaders', '');

        Storage::disk('uploaders')->assertMissing('owned1.jpg');
        // The other owned file was not requested for deletion; it must remain.
        Storage::disk('uploaders')->assertExists('owned2.jpg');
    }

    /**
     * Clearing all owned files at once should work.
     */
    public function test_uploadMultipleFilesToDisk_can_clear_all_owned_files(): void
    {
        $uploader = $this->createUploaderWithFiles(['owned1.jpg', 'owned2.jpg']);

        $this->fakeRequest(['clear_upload_multiple' => ['owned1.jpg', 'owned2.jpg']]);

        $uploader->uploadMultipleFilesToDisk(null, 'upload_multiple', 'uploaders', '');

        Storage::disk('uploaders')->assertMissing('owned1.jpg');
        Storage::disk('uploaders')->assertMissing('owned2.jpg');
    }

    /**
     * When there is no clear_<attr>[] in the request, no files should be deleted.
     */
    public function test_uploadMultipleFilesToDisk_does_not_delete_files_when_no_clear_request(): void
    {
        $uploader = $this->createUploaderWithFiles(['owned1.jpg', 'owned2.jpg']);

        $this->fakeRequest([]);

        $uploader->uploadMultipleFilesToDisk(null, 'upload_multiple', 'uploaders', '');

        Storage::disk('uploaders')->assertExists('owned1.jpg');
        Storage::disk('uploaders')->assertExists('owned2.jpg');
    }

    /**
     * A clear_<attr>[] entry that happens to match a filename that is NOT on disk
     * (already removed externally) but IS in the model list should be handled
     * gracefully — Storage::delete() on a missing file is a no-op in Laravel.
     */
    public function test_uploadMultipleFilesToDisk_handles_already_missing_owned_file_gracefully(): void
    {
        $uploader = $this->createUploaderWithFiles(['owned1.jpg']);

        // Remove the physical file to simulate external deletion.
        Storage::disk('uploaders')->delete('owned1.jpg');
        Storage::disk('uploaders')->assertMissing('owned1.jpg');

        $this->fakeRequest(['clear_upload_multiple' => ['owned1.jpg']]);

        // Should not throw.
        $uploader->uploadMultipleFilesToDisk(null, 'upload_multiple', 'uploaders', '');

        Storage::disk('uploaders')->assertMissing('owned1.jpg');
    }
}
