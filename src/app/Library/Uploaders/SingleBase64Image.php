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
            Storage::disk($this->getDisk())->delete($previousImage);

            return null;
        }

        $decoded = $this->validateAndDecodeBase64Image((string) $value);
        if ($decoded !== false) {
            if ($previousImage) {
                Storage::disk($this->getDisk())->delete($previousImage);
            }

            $finalPath = $this->getPath().$this->getFileName($value);
            Storage::disk($this->getDisk())->put($finalPath, $decoded);

            return $finalPath;
        }

        return $previousImage;
    }

    public function uploadRepeatableFiles($values, $previousRepeatableValues, $entry = null)
    {
        foreach ($values as $row => $rowValue) {
            if ($rowValue && Str::startsWith($rowValue, 'data:')) {
                $decoded = $this->validateAndDecodeBase64Image((string) $rowValue);
                if ($decoded !== false) {
                    $finalPath = $this->getPath().$this->getFileName($rowValue);
                    Storage::disk($this->getDisk())->put($finalPath, $decoded);
                    $values[$row] = $previousRepeatableValues[] = $finalPath;
                } else {
                    $values[$row] = null;
                }
            }
        }

        $imagesToDelete = array_diff(array_filter($previousRepeatableValues), $values);

        foreach ($imagesToDelete as $image) {
            Storage::disk($this->getDisk())->delete($image);
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
