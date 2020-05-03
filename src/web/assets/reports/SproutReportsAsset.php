<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbasereports\web\assets\reports;

use barrelstrength\sproutbasereports\web\assets\apexcharts\ApexChartsAsset;
use barrelstrength\sproutbasereports\web\assets\datatables\DataTablesAsset;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class SproutReportsAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutbasereports/web/assets/reports/dist';

        $this->depends = [
            CpAsset::class,
            ApexChartsAsset::class,
            DataTablesAsset::class
        ];

        $this->css = [
            'css/reports.css',
            'css/visualizations.css'
        ];

        $this->js = [
            'js/reports.js'
        ];

        parent::init();
    }
}