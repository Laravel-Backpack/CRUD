<?php

namespace Backpack\CRUD\app\Library\Uploaders;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\app\Library\Uploaders\Support\Interfaces\UploaderInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;

class MultipleFiles extends Uploader
{
    public static function for(array $field, $configuration): UploaderInterface
    {
        return (new self($field, $configuration))->multiple();
    }

    public function uploadFiles(Model $entry, $value = null)
    {
        if ($value && isset($value[0]) && is_null($value[0])) {
            $value = false;
        }

        $filesToDelete = $this->getFilesToDeleteFromRequest();
        $value = $value ?? collect($value)->flatten()->toArray();
        $previousFiles = $this->getPreviousFiles($entry) ?? [];

        if (is_array($previousFiles) && empty($previousFiles[0] ?? [])) {
            $previousFiles = [];
        }

        if (! is_array($previousFiles) && is_string($previousFiles)) {
            $previousFiles = json_decode($previousFiles, true);
        }

        if (! is_array($value)) {
            $value = [];
        }

        // name the new files before changing anything, so a file that is not allowed does not remove or leave files behind
        $filesToStore = [];

        foreach ($value as $file) {
            if ($file && is_file($file)) {
                $filesToStore[] = [$file, $this->getFileName($file)];
            }
        }

        if ($filesToDelete) {
            foreach ($previousFiles as $previousFile) {
                if (in_array($previousFile, $filesToDelete)) {
                    $this->deleteStoredFile($previousFile);

                    $previousFiles = Arr::where($previousFiles, function ($value, $key) use ($previousFile) {
                        return $value != $previousFile;
                    });
                }
            }
        }

        foreach ($filesToStore as [$file, $fileName]) {
            $file->storeAs($this->getPath(), $fileName, $this->getDisk());
            $previousFiles[] = $this->getPath().$fileName;
        }

        $previousFiles = array_values($previousFiles);

        if (empty($previousFiles)) {
            return null;
        }

        return isset($entry->getCasts()[$this->getName()]) || $this->isFake() ? $previousFiles : json_encode($previousFiles);
    }

    /** @codeCoverageIgnore */
    public function uploadRepeatableFiles($files, $previousRepeatableValues, $entry = null)
    {
        $ownedFiles = $this->getStoredFilesList($previousRepeatableValues);
        $fileOrder = [];

        // the request order can only reference files this entry already owns
        foreach ($this->getFileOrderFromRequest() as $row => $rowFiles) {
            if ($rowFiles === null) {
                $fileOrder[$row] = null;

                continue;
            }

            $fileOrder[$row] = [];

            foreach ((array) $rowFiles as $file) {
                if (($ownedFile = $this->pullOwnedFile($file, $ownedFiles)) !== null) {
                    $fileOrder[$row][] = $ownedFile;
                }
            }
        }

        // name all the files before storing any, so a file that is not allowed does not leave the others behind
        $filesToStore = [];

        foreach ($files as $row => $rowFiles) {
            foreach ((array) ($rowFiles ?? []) as $file) {
                if ($file instanceof UploadedFile && $file->isValid()) {
                    $filesToStore[] = [$row, $file, $this->getFileName($file)];
                }
            }
        }

        foreach ($filesToStore as [$row, $file, $fileName]) {
            $file->storeAs($this->getPath(), $fileName, $this->getDisk());
            $fileOrder[$row][] = $this->getPath().$fileName;
        }

        // owned files that are no longer referenced were removed by the user
        $keptFiles = $this->getStoredFilesList($fileOrder);

        foreach ($ownedFiles as $file) {
            if (! in_array($file, $keptFiles, true)) {
                $this->deleteStoredFile($file);
            }
        }

        return $fileOrder;
    }

    public function hasDeletedFiles($value): bool
    {
        return empty($this->getFilesToDeleteFromRequest()) ? false : true;
    }

    public function getEntryAttributeValue(Model $entry)
    {
        $value = $entry->{$this->getAttributeName()};

        return isset($entry->getCasts()[$this->getName()]) ? $value : json_encode($value);
    }

    private function getFilesToDeleteFromRequest(): array
    {
        return collect(CRUD::getRequest()->input('clear_'.$this->getNameForRequest()))->flatten()->toArray();
    }
}
