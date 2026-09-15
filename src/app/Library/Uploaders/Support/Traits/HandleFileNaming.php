<?php

namespace Backpack\CRUD\app\Library\Uploaders\Support\Traits;

use Backpack\CRUD\app\Exceptions\FileTypeNotAllowedException;
use Backpack\CRUD\app\Library\Uploaders\Support\FileExtensions;
use Backpack\CRUD\app\Library\Uploaders\Support\Interfaces\FileNameGeneratorInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\File\File;

trait HandleFileNaming
{
    public mixed $fileNamer = null;

    /**
     * The extensions this uploader can store files with. When null, the `backpack.crud.allowed_upload_extensions` config is used.
     */
    public ?array $allowedExtensions = null;

    /**
     * Get the name the file will be stored with.
     *
     * @throws ValidationException when the file type is not allowed
     */
    public function getFileName(string|UploadedFile|File $file): string
    {
        try {
            $fileName = is_callable($this->fileNamer) ? ($this->fileNamer)($file, $this) : $this->fileNamer->getName($file);

            FileExtensions::ensureFileNameIsAllowed((string) $fileName, $this->allowedExtensions);
        } catch (FileTypeNotAllowedException $e) {
            throw ValidationException::withMessages([
                $this->getNameForRequest() => trans('backpack::crud.upload_file_type_not_allowed', [
                    'extension' => $e->extension === '' ? '-' : $e->extension,
                ]),
            ]);
        }

        return $fileName;
    }

    private function getFileNameGeneratorInstance(?string $fileNameGenerator): FileNameGeneratorInterface
    {
        $fileGeneratorClass = $fileNameGenerator ?? config('backpack.crud.file_name_generator');

        if (! class_exists($fileGeneratorClass)) {
            throw new \Exception("The file name generator class [{$fileGeneratorClass}] does not exist.");
        }

        if (! in_array(FileNameGeneratorInterface::class, class_implements($fileGeneratorClass, false))) {
            throw new \Exception("The file name generator class [{$fileGeneratorClass}] must implement the [".FileNameGeneratorInterface::class.'] interface.');
        }

        return new $fileGeneratorClass();
    }
}
