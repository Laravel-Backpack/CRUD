<?php

namespace Backpack\CRUD\app\Library\Assets;

class AssetManager
{
    public $loaded_assets;

    public function __construct()
    {
        $this->loaded_assets = [];
    }

    /**
     * Adds the asset to the current loaded assets.
     *
     * @param string $asset
     * @return void
     */
    public function markAssetAsLoaded($asset)
    {
        if (! $this->isAssetLoaded($asset)) {
            $this->loaded_assets[] = $asset;
        }
    }

    /**
     * Checks if the asset is already on loaded asset list.
     *
     * @param string $asset
     * @return bool
     */
    public function isAssetLoaded($asset)
    {
        if (in_array($asset, $this->loaded_assets)) {
            return true;
        }

        return false;
    }

    /**
     * Returns the current loaded assets on app lifecycle.
     *
     * @return array
     */
    public function loadedAssets()
    {
        return $this->loaded_assets;
    }

    /**
     * Echoes the link to load the js file.
     *
     * @param string $path
     * @return string
     */
    public function echoJsFileLink($path)
    {
        return '<script src="'.asset($path).'"></script>';
    }

    /**
     * Echoes the link to load the css file.
     *
     * @param string $path
     * @return string
     */
    public function echoCssFileLink($path)
    {
        //dd($path);
        return '<link href="'.asset($path).'" rel="stylesheet" type="text/css" />';
    }
}
