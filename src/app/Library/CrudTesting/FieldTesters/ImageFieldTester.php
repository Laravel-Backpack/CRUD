<?php

namespace Backpack\CRUD\app\Library\CrudTesting\FieldTesters;

/**
 * Tester for image upload fields.
 */
class ImageFieldTester extends UploadFieldTester
{
    /**
     * {@inheritdoc}
     */
    protected function getTestFilePath(): string
    {
        return storage_path('app/test-files/test.jpg');
    }

    /**
     * Create a minimal test image file.
     *
     * @return string
     */
    public function createTestImage(): string
    {
        $path = $this->getTestFilePath();

        // Create a minimal 1x1 pixel JPG
        $image = imagecreatetruecolor(1, 1);
        $directory = dirname($path);

        if (! file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        imagejpeg($image, $path);
        imagedestroy($image);

        return $path;
    }
}
