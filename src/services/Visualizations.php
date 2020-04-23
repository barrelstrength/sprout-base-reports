<?php


namespace barrelstrength\sproutbasereports\services;

use craft\base\Component;
use barrelstrength\sproutbasereports\visualizations\BarChartVisualization;
use barrelstrength\sproutbasereports\visualizations\LineChartVisualization;
use barrelstrength\sproutbasereports\visualizations\PieChartVisualization;

class Visualizations extends Component
{
  /**
   * Get the list of available visualizations
   *
   * @return array
   */

   public function getVisualizations()
   {
      $visualizationTypes = [
        BarChartVisualization::class,
        LineChartVisualization::class,
        PieChartVisualization::class,
      ];

      foreach ($visualizationTypes  as $class) {
        $visualizations[] = [
          'value' => $class,
          'label' => $class::displayName(),
          'chart' => new $class,
        ];
      }

     return $visualizations;
   }
}
