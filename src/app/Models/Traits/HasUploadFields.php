<?php

namespace Backpack\CRUD\app\Models\Traits;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

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
            // 1. Generate a new file name
            $file = request()->file($attribute_name);

            // use the provided file name or generate a random one
            $new_file_name = $fileName ?? md5($file->getClientOriginalName().random_int(1, 9999).time()).'.'.$file->getClientOriginalExtension();

            // 2. Move the new file to the correct path
            $file_path = $file->storeAs($destination_path, $new_file_name, $disk);

            // 3. Save the complete path to the database
            $this->attributes[$attribute_name] = $file_path;
        }
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

        $files_to_clear = request()->input('clear_'.$attribute_name);

        // if a file has been marked for removal,
        // delete it from the disk and from the db
        if ($files_to_clear) {
            foreach ($files_to_clear as $key => $filename) {
                \Storage::disk($disk)->delete($filename);
                $attribute_value = Arr::where($attribute_value, function ($value, $key) use ($filename) {
                    return $value != $filename;
                });
            }
        }

        // if a new file is uploaded, store it on disk and its filename in the database
        if (request()->hasFile($attribute_name)) {
            foreach (request()->file($attribute_name) as $file) {
                if ($file->isValid()) {
                    // 1. Generate a new file name
                    $new_file_name = md5($file->getClientOriginalName().random_int(1, 9999).time()).'.'.$file->getClientOriginalExtension();

                    // 2. Move the new file to the correct path
                    $file_path = $file->storeAs($destination_path, $new_file_name, $disk);

                    // 3. Add the public path to the database
                    $attribute_value[] = $file_path;
                }
            }
        }

        $this->attributes[$attribute_name] = json_encode($attribute_value);
    }
}
