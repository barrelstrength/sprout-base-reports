<?php

namespace barrelstrength\sproutbasereports\visualizations;

use Craft;

class LineChartVisualization extends BaseVisualization implements VisualizationInterface
{

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

  /**
   * @inheritdoc
   */

  public function getSettingsHtml($settings): string
  {
    return Craft::$app->getView()->renderTemplate('sprout-base-reports/visualizations/LineChart/settings.twig', ['settings' => $settings]);
  }

  /**
   * @inheritdoc
   */

  public function getVisualizationHtml($options = []): string
  {
    parent::getVisualizationHtml();

    return Craft::$app->getView()->renderTemplate('sprout-base-reports/visualizations/LineChart/visualization.twig',
      [
        'visualization' => $this,
        'options' => $options
      ]);
  }




}