<?php

namespace barrelstrength\sproutbasereports\visualizations;

use barrelstrength\sproutbasereports\web\assets\visualizations\VisualizationAssetBundle;
use Craft;

abstract class BaseVisualization {


  protected $dataColumn;

  /**
   * Returns an array of the defined data columns
   * @inheritdoc
   */

  public function getDataColumns():array {
    if ($this->settings){
      if (is_array($this->settings['dataColumn'])) {
        return $this->settings['dataColumn'];
      } else {
        return [$this->settings['dataColumn']];
      }
    } else {
      return false;
    }
    return $dataColumn;
  }

  protected $labelColumn;

  /**
   * @inheritdoc
   */

  public function getLabelColumn(): string {
    if ($this->settings && array_key_exists('labelColumn', $this->settings)){
      return $this->settings['labelColumn'];
    } else {
      return false;
    }
  }

  /**
   * @inheritdoc
   */

  public $settings;

  public function setSettings(array $settings) {
    $this->settings = $settings;
  }

  /**
   * @inheritdoc
   */

  protected $values;

  public function setValues(array $values) {
    $this->values = $values;
  }

  /**
   * @inheritdoc
   */

  protected $labels;

  public function setLabels(array $labels) {
    $this->labels = $labels;
  }

  public function getLabels(){
    $labelColumn = $this->getLabelColumn();
    $labels = [];

    if ($labelColumn){
      $labelIndex = array_search($labelColumn, $this->labels);
      foreach($this->values as $row) {
        if(array_key_exists($labelColumn, $row)){
          $labels[] = $row[$labelColumn];
        } else {
          $labels[] = $row[$labelIndex];
        }
      }
    }

    return $labels;
  }

  public function getDataSeries()
  {
    $dataColumns = $this->getDataColumns();

    $dataSeries = [];
    foreach($dataColumns as $dataColumn) {

      $data = [];
      $dataIndex = array_search($dataColumn, $this->labels);
      foreach($this->values as $row) {
        if(array_key_exists($dataColumn, $row)){
          $data[] = $row[$dataColumn];
        } else {
          $data[] = $row[$dataIndex];
        }
      }
      $dataSeries[] = ['name' => $dataColumn, 'data' => $data];
    }
    return $dataSeries;

  }

  public function getVisualizationHtml()
  {
    Craft::$app->getView()->registerAssetBundle(VisualizationAssetBundle::class);
  }

}