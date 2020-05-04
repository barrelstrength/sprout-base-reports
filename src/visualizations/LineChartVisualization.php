<?php

namespace barrelstrength\sproutbasereports\visualizations;

use barrelstrength\sproutbasereports\base\Visualization;
use barrelstrength\sproutbasereports\base\VisualizationInterface;
use Craft;

class LineChartVisualization extends Visualization implements VisualizationInterface
{

    protected $settingsTemplate = 'sprout-base-reports/_components/visualizations/LineChart/settings.twig';

    protected $resultsTemplate = 'sprout-base-reports/_components/visualizations/LineChart/visualization.twig';

    /**
     * @inheritdoc
     */

    public static function displayName(): string
    {
        return Craft::t('sprout-base-reports', 'Line Chart');
    }
}