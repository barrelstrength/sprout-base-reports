<?php

namespace barrelstrength\sproutbasereports\visualizations;

use Craft;

class BarChartVisualization extends BaseVisualization implements VisualizationInterface
{

  /**
   * @inheritdoc
   */

  public static function displayName(): string
  {
    return Craft::t('sprout-base-reports', 'Bar chart');
  }

  /**
   * @inheritdoc
   */
  public static function getVisualizationType(): string
  {
    return BarChartVisualization::class;
  }

  /**
   * @inheritdoc
   */

  public function getSettingsHtml($settings): string
  {
    return Craft::$app->getView()->renderTemplate('sprout-base-reports/visualizations/BarChart/settings.twig', ['settings' => $settings]);
  }





}