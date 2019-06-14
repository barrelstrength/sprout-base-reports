<?php

namespace barrelstrength\sproutbasereports\base;

use barrelstrength\sproutbasereports\elements\Report;
use craft\base\Plugin;

/**
 * DataSourceTrait implements the common methods and properties for DataSource classes.
 */
trait DataSourceTrait
{
    // Properties
    // =========================================================================

    public $pluginHandle;

    /**
     * Allows a user to disable a Data Source from displaying in the New Report dropdown
     *
     * @return bool|mixed
     */
    public $allowNew;

    /**
     * @var Plugin
     */
    protected $plugin;

    /**
     * @var Report()
     */
    protected $report;
}
