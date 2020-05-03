<?php

namespace barrelstrength\sproutbasereports\visualizations;

use Craft;

class BarChartVisualization extends BaseVisualization implements VisualizationInterface
{

    protected $settingsTemplate = 'sprout-base-reports/_components/visualizations/BarChart/settings.twig';

    protected $resultsTemplate = 'sprout-base-reports/_components/visualizations/BarChart/visualization.twig';

    /**
     * @inheritdoc
     */

    public static function displayName(): string
    {
        return Craft::t('sprout-base-reports', 'Bar Chart');
    }
}