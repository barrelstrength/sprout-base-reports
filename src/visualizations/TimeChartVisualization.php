<?php

namespace barrelstrength\sproutbasereports\visualizations;

use Craft;

class TimeChartVisualization extends BaseVisualization implements VisualizationInterface
{

  protected $settingsTemplate = 'sprout-base-reports/visualizations/TimeChart/settings.twig';

  protected $resultsTemplate = 'sprout-base-reports/visualizations/TimeChart/visualization.twig';


  /**
   * @inheritdoc
   */

  public static function displayName(): string
  {
    return Craft::t('sprout-base-reports', 'Time chart');
  }

  /**
   * @inheritdoc
   */
  public static function getVisualizationType(): string
  {
    return TimeChartVisualization::class;
  }

}