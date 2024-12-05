<?php

namespace Backpack\CRUD\app\Library\Uploaders;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SingleFile extends Uploader
{
    public function uploadFiles(Model $entry, $value = null)
    {
        $previousFile = $this->getPreviousFiles($entry);

        if ($value === false && $previousFile) {
            Storage::disk($this->getDisk())->delete($previousFile);

            return null;
        }

        if ($value && is_file($value) && $value->isValid()) {
            if ($previousFile) {
                Storage::disk($this->getDisk())->delete($previousFile);
            }
            $fileName = $this->getFileName($value);
            $value->storeAs($this->getPath(), $fileName, $this->getDisk());

            return $this->getPath().$fileName;
        }

        if (! $value && CrudPanelFacade::getRequest()->has($this->getNameForRequest()) && $previousFile) {
            Storage::disk($this->getDisk())->delete($previousFile);

            return null;
        }

        return $previousFile;
    }

    /** @codeCoverageIgnore */
    public function uploadRepeatableFiles($values, $previousRepeatableValues, $entry = null)
    {
        $orderedFiles = $this->getFileOrderFromRequest();

        foreach ($values as $row => $file) {
            if ($file && is_file($file) && $file->isValid()) {
                $fileName = $this->getFileName($file);
                $file->storeAs($this->getPath(), $fileName, $this->getDisk());
                $orderedFiles[$row] = $this->getPath().$fileName;

                continue;
            }
        }

        foreach ($previousRepeatableValues as $row => $file) {
            if ($file) {
                if (! isset($orderedFiles[$row])) {
                    $orderedFiles[$row] = null;
                }
                if (! in_array($file, $orderedFiles)) {
                    Storage::disk($this->getDisk())->delete($file);
                }
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
