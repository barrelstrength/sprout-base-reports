<?php
/**
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbasereports\web\assets\datatables;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class ApexChartsAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutbasereportslib/apexcharts';

        $this->js = [
            'apexcharts.min.js'
        ];

        parent::init();
    }
}