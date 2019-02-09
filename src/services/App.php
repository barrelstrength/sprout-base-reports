<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbasereports\services;

use craft\base\Component;

class App extends Component
{
    /**
     * @var DataSources
     */
    public $dataSources;

    /**
     * @var Exports
     */
    public $exports;

    /**
     * @var Reports
     */
    public $reports;

    /**
     * @var ReportGroups
     */
    public $reportGroups;

    /**
     * @inheritdoc
     */
    public function init()
    {
        // Sprout Reports
        $this->dataSources = new DataSources();
        $this->exports = new Exports();
        $this->reportGroups = new ReportGroups();
        $this->reports = new Reports();
    }
}
