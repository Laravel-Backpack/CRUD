<?php

namespace Backpack\CRUD\app\Library\Uploaders;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        $value = $value ?? CRUD::getRequest()->get($this->getName());
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
        // name all the images before storing any, so an image that is not allowed does not leave the others behind
        $imagesToStore = [];

        foreach ($values as $row => $rowValue) {
            if (is_string($rowValue) && Str::startsWith($rowValue, 'data:')) {
                $decoded = $this->validateAndDecodeBase64Image($rowValue);

                // images that are not valid are not stored
                $imagesToStore[$row] = $decoded === false ? null : [$decoded, $this->getPath().$this->getFileName($rowValue)];
            }
        }

        $ownedFiles = $this->getStoredFilesList($previousRepeatableValues);

        foreach ($imagesToStore as $row => $image) {
            $values[$row] = null;

            if ($image !== null) {
                [$decoded, $finalPath] = $image;
                Storage::disk($this->getDisk())->put($finalPath, $decoded);
                $values[$row] = $finalPath;
            }
        }

        // any other value can only reference an image this entry already owns
        foreach ($values as $row => $rowValue) {
            if (! array_key_exists($row, $imagesToStore)) {
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

    protected function shouldUploadFiles($value): bool
    {
        return $value && is_string($value) && (bool) preg_match('#^data:image/(jpeg|png|gif|webp|avif);base64,#i', $value);
    }

    protected function shouldKeepPreviousValueUnchanged(Model $entry, $entryValue): bool
    {
        return $entry->exists && is_string($entryValue) && ! Str::startsWith($entryValue, 'data:image');
    }
}
