<?php

namespace Backpack\CRUD\app\Models\Traits;

use Backpack\CRUD\app\Exceptions\FileTypeNotAllowedException;
use Backpack\CRUD\app\Library\Uploaders\Support\FileExtensions;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

/*
|--------------------------------------------------------------------------
| Methods for storing uploaded files (used in CRUD).
|--------------------------------------------------------------------------
*/
trait HasUploadFields
{
    /**
     * Handle file upload and DB storage for a file:
     * - on CREATE
     *     - stores the file at the destination path
     *     - generates a name
     *     - stores the full path in the DB;
     * - on UPDATE
     *     - if the value is null, deletes the file and sets null in the DB
     *     - if the value is different, stores the different file and updates DB value.
     *
     * @param  string  $value  Value for that column sent from the input.
     * @param  string  $attribute_name  Model attribute name (and column in the db).
     * @param  string  $disk  Filesystem disk used to store files.
     * @param  string  $destination_path  Path in disk where to store the files.
     * @param  string  $fileName  Optional filename for the stored file (without the extension)
     */
    public function uploadFileToDisk($value, $attribute_name, $disk, $destination_path, $fileName = null)
    {
        // name the new file before changing anything, so a file that is not allowed does not remove the previous one
        if (request()->hasFile($attribute_name) && request()->file($attribute_name)->isValid()) {
            $new_file_name = $this->buildUploadedFileName(request()->file($attribute_name), $attribute_name, $fileName);
        }

        // if a new file is uploaded, delete the previous file from the disk
        if (request()->hasFile($attribute_name) &&
            $this->{$attribute_name} &&
            $this->{$attribute_name} != null) {
            \Storage::disk($disk)->delete($this->{$attribute_name});
            $this->attributes[$attribute_name] = null;
        }

        // if the file input is empty, delete the file from the disk
        if (is_null($value) && $this->{$attribute_name} != null) {
            \Storage::disk($disk)->delete($this->{$attribute_name});
            $this->attributes[$attribute_name] = null;
        }

        // if a new file is uploaded, store it on disk and its filename in the database
        if (request()->hasFile($attribute_name) && request()->file($attribute_name)->isValid()) {
            // 1. Move the new file to the correct path
            $file_path = request()->file($attribute_name)->storeAs($destination_path, $new_file_name, $disk);

            // 2. Save the complete path to the database
            $this->attributes[$attribute_name] = $file_path;
        }
    }

    /**
     * Get the name an uploaded file will be stored with, making sure its type is allowed.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  string  $attribute_name  Model attribute name, used to report a file type that is not allowed.
     * @param  string|null  $fileName  Optional filename for the stored file
     *
     * @throws ValidationException when the file type is not allowed
     */
    protected function buildUploadedFileName($file, $attribute_name, $fileName = null)
    {
        $ext = strtolower((string) $file->extension());
        $ext = $ext === '' ? FileExtensions::FALLBACK : $ext;

        try {
            FileExtensions::ensureFileNameIsAllowed('file.'.$ext);

            if ($fileName !== null && FileExtensions::fromFileName($fileName) !== '') {
                FileExtensions::ensureFileNameIsAllowed($fileName);
            }
        } catch (FileTypeNotAllowedException $e) {
            throw ValidationException::withMessages([
                $attribute_name => trans('backpack::crud.upload_file_type_not_allowed', ['extension' => $e->extension]),
            ]);
        }

        // use the provided file name or generate a random one
        return $fileName ?? md5($file->getClientOriginalName().random_int(1, 9999).time()).'.'.$ext;
    }

    /**
     * Handle multiple file upload and DB storage:
     * - if files are sent
     *     - stores the files at the destination path
     *     - generates random names
     *     - stores the full path in the DB, as JSON array;
     * - if a hidden input is sent to clear one or more files
     *     - deletes the file
     *     - removes that file from the DB.
     *
     * @param  string  $value  Value for that column sent from the input.
     * @param  string  $attribute_name  Model attribute name (and column in the db).
     * @param  string  $disk  Filesystem disk used to store files.
     * @param  string  $destination_path  Path in disk where to store the files.
     */
    public function uploadMultipleFilesToDisk($value, $attribute_name, $disk, $destination_path)
    {
        $originalModelValue = $this->getOriginal()[$attribute_name] ?? [];

        if (! is_array($originalModelValue)) {
            $attribute_value = json_decode($originalModelValue, true) ?? [];
        } else {
            $attribute_value = $originalModelValue;
        }

        // name the new files before changing anything, so a file that is not allowed does not remove or leave files behind
        $files_to_store = [];

        if (request()->hasFile($attribute_name)) {
            foreach (request()->file($attribute_name) as $file) {
                if ($file->isValid()) {
                    $files_to_store[] = [$file, $this->buildUploadedFileName($file, $attribute_name)];
                }
            }
        }

        $files_to_clear = request()->input('clear_'.$attribute_name);

        // if a file has been marked for removal,
        // delete it from the disk and from the db
        // only delete files that are actually owned by this model record
        if ($files_to_clear) {
            foreach ($attribute_value as $filename) {
                if (in_array($filename, $files_to_clear)) {
                    \Storage::disk($disk)->delete($filename);
                    $attribute_value = Arr::where($attribute_value, function ($value, $key) use ($filename) {
                        return $value != $filename;
                    });
                }
            }
        }

        // if a new file is uploaded, store it on disk and its filename in the database
        foreach ($files_to_store as [$file, $new_file_name]) {
            // 1. Move the new file to the correct path
            $file_path = $file->storeAs($destination_path, $new_file_name, $disk);

            // 2. Add the public path to the database
            $attribute_value[] = $file_path;
        }

        $this->attributes[$attribute_name] = json_encode($attribute_value);
    }
}
