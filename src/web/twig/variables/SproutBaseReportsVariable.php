<?php

namespace barrelstrength\sproutbasereports\web\twig\variables;

use barrelstrength\sproutbasereports\SproutBaseReports;
use barrelstrength\sproutreports\SproutReports;
use yii\base\Exception;

class SproutBaseReportsVariable
{
  /**
   * @return Report[]
   */
  public function getVisualizationAggregates(): array
  {
      return SproutBaseReports::$app->visualizations->getAggregates();
  }
}
