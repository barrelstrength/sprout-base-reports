<?php
namespace barrelstrength\sproutbasereports\web\assets\visualizations;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class VisualizationAssetBundle extends AssetBundle
{
    public function init()
    {
        // define the path that your publishable resources live

        $this->sourcePath = "@sproutbasereports/web/assets/visualizations/src";

        // define the dependencies
        $this->depends = [
            CpAsset::class,
        ];

        // define the relative path to CSS/JS files that should be registered with the page
        // when this asset bundle is registered
        $this->js = [
            'js/apexcharts.js',
            'js/visualizations.js',
        ];

        $this->css = [
        ];

        parent::init();
    }
}