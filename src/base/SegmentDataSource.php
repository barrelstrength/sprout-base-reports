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
 * @property string $viewContextLabel
 * @property string $emailColumn
 */
abstract class SegmentDataSource extends DataSource implements SegmentDataSourceInterface
{
    const DEFAULT_VIEW_CONTEXT = 'segments';

    /**
     * @inheritDoc
     */
    public function getViewContext(): string
    {
        return self::DEFAULT_VIEW_CONTEXT;
    }

    /**
     * @inheritDoc
     */
    public function getViewContextLabel(): string
    {
        return 'Segments';
    }

    /**
     * @inheritDoc
     */
    public function getEmailColumn(): string
    {
        return 'email';
    }
}
