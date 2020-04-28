<?php

namespace barrelstrength\sproutbasereports\visualizations;

use Craft;

class PieChartVisualization extends BaseVisualization implements VisualizationInterface
{

  /**
   * @inheritdoc
   */

  public static function displayName(): string
  {
    return Craft::t('sprout-base-reports', 'Pie chart');
  }

  /**
   * @inheritdoc
   */
  public static function getVisualizationType(): string
  {
    return PieChartVisualization::class;
  }

  /**
   * @inheritdoc
   */

  public function getSettingsHtml($settings): string
  {
    return Craft::$app->getView()->renderTemplate('sprout-base-reports/visualizations/PieChart/settings.twig', ['settings' => $settings]);
  }

   /**
   * @inheritdoc
   */

  public function getVisualizationHtml($options = []): string
  {
    parent::getVisualizationHtml();
    return Craft::$app->getView()->renderTemplate('sprout-base-reports/visualizations/PieChart/visualization.twig',
      [
        'visualization' => $this,
        'options' => $options
      ]);
  }
}