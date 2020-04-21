<?php


namespace barrelstrength\sproutbasereports\services;

use craft\base\Component;
use barrelstrength\sproutbasereports\visualizations\BarChartVisualization;
use barrelstrength\sproutbasereports\visualizations\LineChartVisualization;

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

   /**
   * Get the list of available visualizations
   *
   * @return array
   */

  public function getVisualization(string $class)
  {
     /*$visualizationTypes = [
       LineChartVisualization::class,
     ];

     $visualizations = [];

     foreach ($visualizationTypes  as $class) {
       $visualizations[] = [
         'value' => $class,
         'label' => $class::displayName(),
         'chart' => new $class,
       ];
     }*/
     return new LineChartVisualization();

    //return $visualizations;

  }
}
