<?php

namespace barrelstrength\sproutbasereports\base;

use barrelstrength\sproutbasereports\base\DataSource;
use barrelstrength\sproutbasereports\elements\Report;
use Craft;
use Exception;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * @property string $emailColumn
 */
abstract class SegmentDataSource extends DataSource implements SegmentDataSourceInterface
{
    public $isUnlisted = true;

    /**
     * @inheritDoc
     */
    public function getEmailColumn(): string
    {
        return 'email';
    }
}
