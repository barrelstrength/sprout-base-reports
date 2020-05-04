<?php

namespace barrelstrength\sproutbasereports\visualizations;

use barrelstrength\sproutbasereports\base\Visualization;
use barrelstrength\sproutbasereports\base\VisualizationInterface;
use Craft;

class BarChartVisualization extends Visualization implements VisualizationInterface
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