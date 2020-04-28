<?php

namespace barrelstrength\sproutbasereports\visualizations;

interface VisualizationInterface
{
  /**
   * Returns the visualization settings HTML
   * @param $settings visualization settings
   *
   * @return string The HTML that should be shown for this visualization's settings
   */

  public function getSettingsHtml(array $settings): string;

  /**
   * Return the visualization HTML
   *
   * @param $options override values passed to the javascript charting instance
   *
   * @return string The HTML that displays the chart/visualization
   */
  public function getVisualizationHtml(array $options = []): string;

  /**
   * Returns the type of visualization this represents
   *
   * @return string
   */

  public static function getVisualizationType(): string;

  /**
   * Returns the column names to be used as the data series.
   *
   * @return string
   */

  public function getDataColumns(): array;

  /** Returns the column name to be used as the label series
   *
   * @return string
   */

  public function getLabelColumn(): string;




}