<?php

namespace barrelstrength\sproutbasereports\visualizations;

use Craft;

class TimeChartVisualization extends BaseVisualization implements VisualizationInterface
{

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

  /**
   * @inheritdoc
   */

  public function getSettingsHtml($settings): string
  {
    return Craft::$app->getView()->renderTemplate('sprout-base-reports/visualizations/TimeChart/settings.twig', ['settings' => $settings]);
  }

   /**
   * @inheritdoc
   */

  public function getVisualizationHtml($options = []): string
  {
    parent::getVisualizationHtml();
    return Craft::$app->getView()->renderTemplate('sprout-base-reports/visualizations/TimeChart/visualization.twig',
      [
        'visualization' => $this,
        'options' => $options
      ]);
  }
}