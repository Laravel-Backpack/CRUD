<?php

namespace Backpack\CRUD\app\Library\Uploaders;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/** @codeCoverageIgnore */
class SingleBase64Image extends Uploader
{
    private const ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif'];

    private function validateAndDecodeBase64Image(string $value): string|false
    {
        if (! preg_match('#^data:image/(jpeg|png|gif|webp|avif);base64,#i', $value)) {
            return false;
        }

        $decoded = base64_decode(Str::after($value, ';base64,'), true);
        if ($decoded === false) {
            return false;
        }

        $detected = (new \finfo(FILEINFO_MIME_TYPE))->buffer($decoded);

        return in_array($detected, self::ALLOWED_MIME_TYPES, true) ? $decoded : false;
    }

    public function uploadFiles(Model $entry, $value = null)
    {
        $previousImage = $this->getPreviousFiles($entry);

        if (! $value && $previousImage) {
            $this->deleteStoredFile($previousImage);

            return null;
        }

        $decoded = $this->validateAndDecodeBase64Image((string) $value);
        if ($decoded !== false) {
            // get the name first, so an image that is not allowed does not remove the previous one
            $finalPath = $this->getPath().$this->getFileName($value);

            if ($previousImage) {
                $this->deleteStoredFile($previousImage);
            }

            Storage::disk($this->getDisk())->put($finalPath, $decoded);

            return $finalPath;
        }

        return $previousImage;
    }

    public function uploadRepeatableFiles($values, $previousRepeatableValues, $entry = null)
    {
        $ownedFiles = $this->getStoredFilesList($previousRepeatableValues);
        $newImageRows = [];

        foreach ($values as $row => $rowValue) {
            if (is_string($rowValue) && Str::startsWith($rowValue, 'data:')) {
                $newImageRows[] = $row;
                $decoded = $this->validateAndDecodeBase64Image($rowValue);
                $values[$row] = null;

                if ($decoded !== false) {
                    $finalPath = $this->getPath().$this->getFileName($rowValue);
                    Storage::disk($this->getDisk())->put($finalPath, $decoded);
                    $values[$row] = $finalPath;
                }
            }
        }

        // any other value can only reference an image this entry already owns
        foreach ($values as $row => $rowValue) {
            if (! in_array($row, $newImageRows, true)) {
                $values[$row] = $this->pullOwnedFile($rowValue, $ownedFiles);
            }
        }

        // owned images that are no longer referenced were removed or replaced by the user
        foreach ($ownedFiles as $image) {
            if (! in_array($image, $values, true)) {
                $this->deleteStoredFile($image);
            }
        }

        return $values;
    }

    public function shouldUploadFiles($value): bool
    {
        return $value && is_string($value) && (bool) preg_match('#^data:image/(jpeg|png|gif|webp|avif);base64,#i', $value);
    }

    public function shouldKeepPreviousValueUnchanged(Model $entry, $entryValue): bool
    {
        return $entry->exists && is_string($entryValue) && ! Str::startsWith($entryValue, 'data:image');
    }

    public function getUploadedFilesFromRequest()
    {
        return CRUD::getRequest()->input($this->getNameForRequest());
    }
}
