<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbasereports\web\assets\apexcharts;

use craft\web\AssetBundle;

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