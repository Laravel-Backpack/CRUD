<?php

namespace Backpack\CRUD\Tests\Feature;

use Backpack\CRUD\Tests\config\CrudPanel\BaseDBCrudPanel;
use Backpack\CRUD\Tests\config\Http\Controllers\RepeatableUploaderCrudController;
use Backpack\CRUD\Tests\config\Http\Controllers\RepeatableUploaderPathCrudController;
use Backpack\CRUD\Tests\config\Http\Controllers\UploaderPathCrudController;
use Backpack\CRUD\Tests\config\Models\User;
use Backpack\CRUD\Tests\config\Uploads\HasUploadedFiles;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Uploaders must only delete files that the entry owns (and that live in the uploader path).
 * Client input must never be able to make an entry "own" an arbitrary file on the disk.
 *
 * @covers Backpack\CRUD\app\Library\Uploaders\Uploader
 * @covers Backpack\CRUD\app\Library\Uploaders\SingleFile
 * @covers Backpack\CRUD\app\Library\Uploaders\MultipleFiles
 * @covers Backpack\CRUD\app\Library\Uploaders\SingleBase64Image
 */
class UploadersFileOwnershipTest extends BaseDBCrudPanel
{
    use HasUploadedFiles;

    private const FOREIGN_FILE = 'victim/invoice.pdf';

    protected function defineRoutes($router)
    {
        $router->crud(config('backpack.base.route_prefix').'/repeatable-uploader', RepeatableUploaderCrudController::class);
        $router->crud(config('backpack.base.route_prefix').'/repeatable-uploader-path', RepeatableUploaderPathCrudController::class);
        $router->crud(config('backpack.base.route_prefix').'/uploader-path', UploaderPathCrudController::class);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('uploaders');
        $this->actingAs(User::find(1));

        // a file that belongs to someone else, stored on the same disk
        UploadedFile::fake()->create('invoice.pdf', 10)->storeAs('victim', 'invoice.pdf', ['disk' => 'uploaders']);
    }

    /*******************************
     * Client input can't inject file paths (repeatable)
     *******************************/
    public function test_repeatable_upload_multiple_order_cannot_reference_foreign_files()
    {
        $this->insertEntry(['repeatable' => [$this->emptyRow()]]);

        $this->updateRepeatable('repeatable-uploader', [$this->emptyRow()], [['upload_multiple' => self::FOREIGN_FILE]]);
        $this->assertNotContains(self::FOREIGN_FILE, $this->rawRepeatable()[0]['upload_multiple'] ?? []);

        $this->updateRepeatable('repeatable-uploader', [$this->emptyRow()], [[]]);
        $this->assertTrue(Storage::disk('uploaders')->exists(self::FOREIGN_FILE));
    }

    public function test_repeatable_upload_order_cannot_reference_foreign_files()
    {
        $this->insertEntry(['repeatable' => [$this->emptyRow()]]);

        $this->updateRepeatable('repeatable-uploader', [$this->emptyRow()], [['upload' => self::FOREIGN_FILE]]);
        $this->assertNull($this->rawRepeatable()[0]['upload'] ?? null);

        $this->updateRepeatable('repeatable-uploader', [$this->emptyRow()], [[]]);
        $this->assertTrue(Storage::disk('uploaders')->exists(self::FOREIGN_FILE));
    }

    public function test_repeatable_image_value_cannot_reference_foreign_files()
    {
        $this->insertEntry(['repeatable' => [$this->emptyRow()]]);

        $this->updateRepeatable('repeatable-uploader', [['image' => self::FOREIGN_FILE]]);
        $this->assertNull($this->rawRepeatable()[0]['image'] ?? null);

        $this->updateRepeatable('repeatable-uploader', [['image' => null]]);
        $this->assertTrue(Storage::disk('uploaders')->exists(self::FOREIGN_FILE));
    }

    public function test_repeatable_image_value_in_a_new_row_cannot_reference_foreign_files()
    {
        $this->storeFile('uploads/image.jpg');
        $this->insertEntry(['repeatable' => [$this->emptyRow(['image' => 'uploads/image.jpg'])]]);

        $this->updateRepeatable('repeatable-uploader-path', [['image' => 'image.jpg'], ['image' => self::FOREIGN_FILE]]);
        $this->assertSame('uploads/image.jpg', $this->rawRepeatable()[0]['image']);
        $this->assertNull($this->rawRepeatable()[1]['image'] ?? null);

        $this->updateRepeatable('repeatable-uploader-path', [['image' => 'image.jpg'], ['image' => null]]);
        $this->assertTrue(Storage::disk('uploaders')->exists(self::FOREIGN_FILE));
        $this->assertTrue(Storage::disk('uploaders')->exists('uploads/image.jpg'));
    }

    /*******************************
     * Owned files keep working (repeatable)
     *******************************/
    public function test_repeatable_keeps_owned_files_referenced_without_path()
    {
        $this->seedOwnedRepeatableFiles();

        $this->updateRepeatable(
            'repeatable-uploader-path',
            [['image' => 'image.jpg']],
            [['upload' => 'avatar1.jpg', 'upload_multiple' => 'avatar2.jpg,avatar3.jpg']]
        );

        $this->assertSame([
            'upload' => 'uploads/avatar1.jpg',
            'upload_multiple' => ['uploads/avatar2.jpg', 'uploads/avatar3.jpg'],
            'image' => 'uploads/image.jpg',
        ], $this->onlyUploads($this->rawRepeatable()[0]));

        foreach (['avatar1.jpg', 'avatar2.jpg', 'avatar3.jpg', 'image.jpg'] as $file) {
            $this->assertTrue(Storage::disk('uploaders')->exists('uploads/'.$file));
        }
    }

    public function test_repeatable_deletes_owned_files_removed_by_the_user()
    {
        $this->seedOwnedRepeatableFiles();

        $this->updateRepeatable('repeatable-uploader-path', [['image' => '']], [['upload_multiple' => 'avatar3.jpg']]);

        $this->assertSame([
            'upload' => null,
            'upload_multiple' => ['uploads/avatar3.jpg'],
            'image' => null,
        ], $this->onlyUploads($this->rawRepeatable()[0]));

        $this->assertFalse(Storage::disk('uploaders')->exists('uploads/avatar1.jpg'));
        $this->assertFalse(Storage::disk('uploaders')->exists('uploads/avatar2.jpg'));
        $this->assertFalse(Storage::disk('uploaders')->exists('uploads/image.jpg'));
        $this->assertTrue(Storage::disk('uploaders')->exists('uploads/avatar3.jpg'));
    }

    public function test_repeatable_can_move_owned_files_between_rows_and_add_new_uploads()
    {
        $this->seedOwnedRepeatableFiles();

        $this->updateRepeatable(
            'repeatable-uploader-path',
            [
                ['upload' => $this->getUploadedFile('pic1.jpg'), 'upload_multiple' => $this->getUploadedFiles(['pic2.jpg']), 'image' => $this->getBase64Image()],
                ['image' => 'image.jpg'],
            ],
            [
                ['upload_multiple' => 'avatar3.jpg'],
                ['upload' => 'avatar1.jpg', 'upload_multiple' => 'avatar2.jpg'],
            ]
        );

        $rows = $this->rawRepeatable();

        $this->assertSame('uploads/pic1.jpg', $rows[0]['upload']);
        $this->assertSame(['uploads/avatar3.jpg', 'uploads/pic2.jpg'], $rows[0]['upload_multiple']);
        $this->assertSame('uploads/avatar1.jpg', $rows[1]['upload']);
        $this->assertSame(['uploads/avatar2.jpg'], $rows[1]['upload_multiple']);

        foreach (['avatar1.jpg', 'avatar2.jpg', 'avatar3.jpg', 'pic1.jpg', 'pic2.jpg', 'image.jpg'] as $file) {
            $this->assertTrue(Storage::disk('uploaders')->exists('uploads/'.$file), $file.' should exist');
        }
    }

    public function test_repeatable_does_not_duplicate_an_owned_file_into_multiple_rows()
    {
        $this->seedOwnedRepeatableFiles();

        $this->updateRepeatable('repeatable-uploader-path', [['image' => 'image.jpg'], ['image' => null]], [
            ['upload' => 'avatar1.jpg', 'upload_multiple' => 'avatar2.jpg,avatar3.jpg'],
            ['upload' => 'avatar1.jpg', 'upload_multiple' => 'avatar2.jpg'],
        ]);

        $rows = $this->rawRepeatable();

        $this->assertSame('uploads/avatar1.jpg', $rows[0]['upload']);
        $this->assertNull($rows[1]['upload'] ?? null);
        $this->assertSame(['uploads/avatar2.jpg', 'uploads/avatar3.jpg'], $rows[0]['upload_multiple']);
        $this->assertEmpty($rows[1]['upload_multiple'] ?? null);
    }

    /*******************************
     * Deletion is restricted to the uploader path (defense in depth for already stored values)
     *******************************/
    public function test_repeatable_does_not_delete_stored_files_outside_the_uploader_path()
    {
        $this->insertEntry(['repeatable' => [$this->emptyRow([
            'upload' => self::FOREIGN_FILE,
            'upload_multiple' => [self::FOREIGN_FILE],
            'image' => self::FOREIGN_FILE,
        ])]]);

        $this->updateRepeatable('repeatable-uploader-path', [['image' => '']], [[]]);

        $this->assertTrue(Storage::disk('uploaders')->exists(self::FOREIGN_FILE));
    }

    public function test_repeatable_does_not_delete_stored_files_with_parent_path_segments()
    {
        $traversal = 'uploads/../'.self::FOREIGN_FILE;

        $this->insertEntry(['repeatable' => [$this->emptyRow([
            'upload' => $traversal,
            'upload_multiple' => [$traversal],
            'image' => $traversal,
        ])]]);

        $this->updateRepeatable('repeatable-uploader-path', [['image' => '']], [[]]);

        $this->assertTrue(Storage::disk('uploaders')->exists(self::FOREIGN_FILE));
    }

    public function test_repeatable_entry_deletion_does_not_delete_files_with_parent_path_segments()
    {
        $traversal = 'uploads/../'.self::FOREIGN_FILE;

        $this->insertEntry(['repeatable' => [$this->emptyRow([
            'upload' => $traversal,
            'upload_multiple' => [$traversal],
            'image' => $traversal,
        ])]]);

        $this->delete(config('backpack.base.route_prefix').'/repeatable-uploader-path/1')->assertStatus(200);

        $this->assertTrue(Storage::disk('uploaders')->exists(self::FOREIGN_FILE));
    }

    public function test_single_upload_does_not_delete_stored_files_outside_the_uploader_path_when_replaced()
    {
        $this->insertEntry(['upload' => self::FOREIGN_FILE]);

        $this->put(config('backpack.base.route_prefix').'/uploader-path/1', [
            'id' => 1,
            'upload' => $this->getUploadedFile('avatar1.jpg'),
        ])->assertStatus(302);

        $this->assertTrue(Storage::disk('uploaders')->exists('uploads/avatar1.jpg'));
        $this->assertTrue(Storage::disk('uploaders')->exists(self::FOREIGN_FILE));
    }

    public function test_upload_multiple_does_not_delete_stored_files_outside_the_uploader_path_when_cleared()
    {
        $this->insertEntry(['upload_multiple' => [self::FOREIGN_FILE, 'uploads/../'.self::FOREIGN_FILE]]);

        $this->put(config('backpack.base.route_prefix').'/uploader-path/1', [
            'id' => 1,
            'clear_upload_multiple' => [self::FOREIGN_FILE, 'uploads/../'.self::FOREIGN_FILE],
        ])->assertStatus(302);

        $this->assertTrue(Storage::disk('uploaders')->exists(self::FOREIGN_FILE));
    }

    public function test_entry_deletion_does_not_delete_files_with_parent_path_segments()
    {
        $this->insertEntry(['upload' => 'uploads/../'.self::FOREIGN_FILE, 'upload_multiple' => ['uploads/../'.self::FOREIGN_FILE]]);

        $this->delete(config('backpack.base.route_prefix').'/uploader-path/1')->assertStatus(200);

        $this->assertTrue(Storage::disk('uploaders')->exists(self::FOREIGN_FILE));
    }

    public function test_entry_deletion_still_deletes_owned_files()
    {
        $this->storeFile('uploads/avatar1.jpg');
        $this->storeFile('uploads/avatar2.jpg');
        $this->insertEntry(['upload' => 'uploads/avatar1.jpg', 'upload_multiple' => ['uploads/avatar2.jpg']]);

        $this->delete(config('backpack.base.route_prefix').'/uploader-path/1')->assertStatus(200);

        $this->assertFalse(Storage::disk('uploaders')->exists('uploads/avatar1.jpg'));
        $this->assertFalse(Storage::disk('uploaders')->exists('uploads/avatar2.jpg'));
    }

    /*******************************
     * Helpers
     *******************************/
    private function updateRepeatable(string $route, array $rows, ?array $order = null): void
    {
        $data = ['id' => 1, 'repeatable' => $rows];

        if ($order !== null) {
            $data['_order_repeatable'] = $order;
        }

        $this->put(config('backpack.base.route_prefix').'/'.$route.'/1', $data)->assertStatus(302);
    }

    private function emptyRow(array $values = []): array
    {
        return array_merge(['upload' => null, 'upload_multiple' => null, 'image' => null], $values);
    }

    private function insertEntry(array $attributes): void
    {
        DB::table('uploaders')->insert(array_merge(['id' => 1], array_map(
            fn ($value) => is_array($value) ? json_encode($value) : $value,
            $attributes
        )));
    }

    private function rawRepeatable(): array
    {
        return json_decode(DB::table('uploaders')->where('id', 1)->value('repeatable'), true) ?? [];
    }

    private function onlyUploads(array $row): array
    {
        return array_merge(['upload' => null, 'upload_multiple' => null, 'image' => null], array_intersect_key($row, array_flip(['upload', 'upload_multiple', 'image'])));
    }

    private function storeFile(string $path): void
    {
        UploadedFile::fake()->image(basename($path))->storeAs(dirname($path), basename($path), ['disk' => 'uploaders']);
    }

    private function seedOwnedRepeatableFiles(): void
    {
        foreach (['avatar1.jpg', 'avatar2.jpg', 'avatar3.jpg', 'image.jpg'] as $file) {
            $this->storeFile('uploads/'.$file);
        }

        $this->insertEntry(['repeatable' => [[
            'upload' => 'uploads/avatar1.jpg',
            'upload_multiple' => ['uploads/avatar2.jpg', 'uploads/avatar3.jpg'],
            'image' => 'uploads/image.jpg',
        ]]]);
    }
}
