<?php

namespace barrelstrength\sproutbasereports\visualizations;

use Craft;

class PieChartVisualization extends BaseVisualization implements VisualizationInterface
{

    protected $settingsTemplate = 'sprout-base-reports/_components/visualizations/PieChart/settings.twig';

    protected $resultsTemplate = 'sprout-base-reports/_components/visualizations/PieChart/visualization.twig';


    /**
     * @inheritdoc
     */

    public static function displayName(): string
    {
        return Craft::t('sprout-base-reports', 'Pie Chart');
    }
}