<?php

namespace barrelstrength\sproutbasereports\base;

use barrelstrength\sproutbasereports\elements\Report;
use craft\base\Plugin;
use craft\base\SavableComponentInterface;

interface SegmentDataSourceInterface
{
    /**
     * The string that will be used to identify the email column in the results
     *
     * @return string
     */
    public function getEmailColumn(): string;
}
