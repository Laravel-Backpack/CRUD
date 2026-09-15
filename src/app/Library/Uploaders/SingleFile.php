<?php

namespace Backpack\CRUD\app\Library\Uploaders;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

class SingleFile extends Uploader
{
    public function uploadFiles(Model $entry, $value = null)
    {
        $previousFile = $this->getPreviousFiles($entry);

        if ($value === false && $previousFile) {
            $this->deleteStoredFile($previousFile);

            return null;
        }

        if ($value && is_file($value) && $value->isValid()) {
            // get the name first, so a file that is not allowed does not remove the previous one
            $fileName = $this->getFileName($value);

            if ($previousFile) {
                $this->deleteStoredFile($previousFile);
            }
            $value->storeAs($this->getPath(), $fileName, $this->getDisk());

            return $this->getPath().$fileName;
        }

        if (! $value && CrudPanelFacade::getRequest()->has($this->getNameForRequest()) && $previousFile) {
            $this->deleteStoredFile($previousFile);

            return null;
        }

        return $previousFile;
    }

    /** @codeCoverageIgnore */
    public function uploadRepeatableFiles($values, $previousRepeatableValues, $entry = null)
    {
        $ownedFiles = $this->getStoredFilesList($previousRepeatableValues);
        $orderedFiles = [];

        // name all the files before storing any, so a file that is not allowed does not leave the others behind
        $filesToStore = [];

        foreach ($values as $row => $file) {
            if ($file instanceof UploadedFile && $file->isValid()) {
                $filesToStore[$row] = [$file, $this->getFileName($file)];
            }
        }

        foreach ($filesToStore as $row => [$file, $fileName]) {
            $file->storeAs($this->getPath(), $fileName, $this->getDisk());
            $orderedFiles[$row] = $this->getPath().$fileName;
        }

        // the request order can only reference files this entry already owns
        foreach ($this->getFileOrderFromRequest() as $row => $file) {
            if (! array_key_exists($row, $orderedFiles)) {
                $orderedFiles[$row] = $this->pullOwnedFile($file, $ownedFiles);
            }
        }

        // owned files that are no longer referenced were removed or replaced by the user
        foreach ($ownedFiles as $file) {
            if (! in_array($file, $orderedFiles, true)) {
                $this->deleteStoredFile($file);
            }
        }

        return $orderedFiles;
    }

    /**
     * Single file uploaders send no value when they are not dirty.
     */
    public function shouldKeepPreviousValueUnchanged(Model $entry, $entryValue): bool
    {
        return is_string($entryValue);
    }

    public function hasDeletedFiles($entryValue): bool
    {
        return $entryValue === null;
    }

    public function shouldUploadFiles($value): bool
    {
        return is_a($value, 'Illuminate\Http\UploadedFile', true);
    }
}
