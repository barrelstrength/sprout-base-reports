<?php

namespace barrelstrength\sproutbasereports\visualizations;

use Craft;

class LineChartVisualization extends BaseVisualization implements VisualizationInterface
{

  protected $settingsTemplate = 'sprout-base-reports/visualizations/LineChart/settings.twig';

  protected $resultsTemplate = 'sprout-base-reports/visualizations/LineChart/visualization.twig';

  /**
   * @inheritdoc
   */

  public static function displayName(): string
  {
    return Craft::t('sprout-base-reports', 'Line chart');
  }

  /**
   * @inheritdoc
   */
  public static function getVisualizationType(): string
  {
    return LineChartVisualization::class;
  }

}