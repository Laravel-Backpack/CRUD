<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

trait Assets
{
    protected $loaded_assets = [];

    public function markAssetAsLoaded($asset_path)
    {
        array_push($this->loaded_assets, $asset_path);
    }

    public function isAssetLoaded($asset_path)
    {
        if (in_array($asset_path, $this->loaded_assets)) {
            return true;
        }

        return false;
    }

    public function getLoadedAssets()
    {
        return $this->loaded_assets;
    }

    public function echoJsScript($path)
    {
        return '<script src="'.asset($path).'"></script>';
    }

    public function echoCssFileLink($path)
    {
        return '<link href="'.asset($path).'" rel="stylesheet" type="text/css" />';
    }
}
