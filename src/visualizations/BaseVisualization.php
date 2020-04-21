<?php

namespace barrelstrength\sproutbasereports\visualizations;


abstract class BaseVisualization {


  protected $dataColumn;

  /**
   * @inheritdoc
   */

  public function getDataColumn(): string {
    return $dataColumn;
  }

  protected $labelColumn;

  /**
   * @inheritdoc
   */

  public function getLabelColumn(): string {
    return $labelColumn;
  }

}