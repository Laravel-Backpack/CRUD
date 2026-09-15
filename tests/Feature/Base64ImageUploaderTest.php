<?php

namespace Backpack\CRUD\Tests\Feature;

use Backpack\CRUD\Tests\config\CrudPanel\BaseDBCrudPanel;
use Backpack\CRUD\Tests\config\Http\Controllers\RepeatableUploaderCrudController;
use Backpack\CRUD\Tests\config\Http\Controllers\UploaderConfigurationCrudController;
use Backpack\CRUD\Tests\config\Models\User;
use Backpack\CRUD\Tests\config\Uploads\HasUploadedFiles;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @covers Backpack\CRUD\app\Library\Uploaders\SingleBase64Image
 */
class Base64ImageUploaderTest extends BaseDBCrudPanel
{
    use HasUploadedFiles;

    protected function defineRoutes($router)
    {
        $router->crud(config('backpack.base.route_prefix').'/uploader-configuration', UploaderConfigurationCrudController::class);
        $router->crud(config('backpack.base.route_prefix').'/repeatable-uploader', RepeatableUploaderCrudController::class);
    }

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('uploaders');
        $this->actingAs(User::find(1));
    }

    public static function invalidBase64Images(): array
    {
        $svg = base64_encode('<svg xmlns="http://www.w3.org/2000/svg"><script>alert(document.domain)</script></svg>');
        $html = base64_encode('<!DOCTYPE html><html><body><script>alert(document.domain)</script></body></html>');

        return [
            'svg' => ['data:image/svg+xml;base64,'.$svg],
            'html declared as image' => ['data:image/html;base64,'.$html],
            'html declared as png' => ['data:image/png;base64,'.$html],
            'svg declared as jpeg' => ['data:image/jpeg;base64,'.$svg],
            'invalid base64' => ['data:image/png;base64,not-base64!'],
        ];
    }

    public function test_it_stores_a_valid_base64_image()
    {
        $response = $this->post(config('backpack.base.route_prefix').'/uploader-configuration/base64-image', [
            'image' => $this->getBase64Image(),
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();

        $image = DB::table('uploaders')->value('image');

        $this->assertStringEndsWith('.jpeg', $image);
        Storage::disk('uploaders')->assertExists($image);
    }

    #[DataProvider('invalidBase64Images')]
    public function test_it_does_not_store_base64_images_that_are_not_valid(string $image)
    {
        $response = $this->post(config('backpack.base.route_prefix').'/uploader-configuration/base64-image', [
            'image' => $image,
        ]);

        $response->assertStatus(302);

        $this->assertNull(DB::table('uploaders')->value('image'));
        $this->assertEmpty(Storage::disk('uploaders')->allFiles());
    }

    #[DataProvider('invalidBase64Images')]
    public function test_repeatable_does_not_store_base64_images_that_are_not_valid(string $image)
    {
        $response = $this->post(config('backpack.base.route_prefix').'/repeatable-uploader', [
            'repeatable' => [
                ['image' => $this->getBase64Image()],
                ['image' => $image],
            ],
        ]);

        $response->assertStatus(302);

        // v6 stores the repeatable json encoded twice when the attribute is casted
        $rows = DB::table('uploaders')->value('repeatable');
        while (is_string($rows)) {
            $rows = json_decode($rows, true);
        }

        $this->assertSame('image.jpg', $rows[0]['image']);
        $this->assertNull($rows[1]['image']);
        $this->assertSame(['image.jpg'], Storage::disk('uploaders')->allFiles());
    }
}
