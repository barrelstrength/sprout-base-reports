<?php

namespace barrelstrength\sproutbasereports\base;

use Craft;
use craft\base\Component;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;

/**
 * Class Visualization
 *
 * @package barrelstrength\sproutbasereports\visualizations
 *
 * @property array $timeSeries
 * @property array $settings
 * @property array $dataSeries
 */
abstract class Visualization extends Component
{
    protected $settingsTemplate = '';

    protected $resultsTemplate = '';

    protected $title = '';

    /**
     * if this is a date time report stores the earliest timestamp value from the data series
     */
    protected $firstDate = 0;

    /**
     * if this is a date time report stores the latest timestamp value from the data series
     */
    protected $lastDate = 0;

    protected $dataColumns;

    protected $labelColumn;

    /**
     * Set the visualization raw data values
     *
     * @param array $values
     */

    protected $values;

    /**
     * Set the visualization labels
     *
     * @param array $labels
     */

    protected $labels;

    /**
     * Returns the visualization title
     *
     * @returns string
     */

    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Set the report title
     *
     * @param string title
     */

    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Returns the first (earliest) timestamp value from the data series for a time series visualization
     *
     * @returns Number
     */
    public function getFirstDate()
    {
        return $this->firstDate;
    }

    /**
     * Returns the last (latest) timestamp value from the data series for a time series visualization
     *
     * @returns Number
     */
    public function getLastDate()
    {
        return $this->lastDate;
    }

    /**
     * Set the visualization settings
     *
     * Settings must include ['labelColumn' => string, 'dataColumns' => array(string)]
     *
     * @param array $settings
     */

    public function setSettings($settings)
    {
        $this->settings = $settings;
    }

    /**
     * Returns an array of the defined data columns
     *
     * @return array
     */
    public function getDataColumns(): array
    {
        if (!$this->settings) {
            return [];
        }

        if (is_array($this->settings['dataColumns'])) {
            return $this->settings['dataColumns'];
        }

        return [$this->settings['dataColumns']];
    }

    /**
     * Returns the label column
     *
     * @return string
     */

    public function getLabelColumn(): string
    {
        if ($this->settings && array_key_exists('labelColumn', $this->settings)) {
            return $this->settings['labelColumn'];
        }

        return false;
    }

    public function setValues(array $values)
    {
        $this->values = $values;
    }

    public function getLabels()
    {
        $labelColumn = $this->getLabelColumn();
        $labels = [];

        if ($labelColumn) {
            $labelIndex = array_search($labelColumn, $this->labels, true);
            foreach ($this->values as $row) {
                if (array_key_exists($labelColumn, $row)) {
                    $labels[] = $row[$labelColumn];
                } else {
                    $labels[] = $row[$labelIndex];
                }
            }
        }

        return $labels;
    }

    public function setLabels(array $labels)
    {
        $this->labels = $labels;
    }

    /**
     * Return the data series for each defined data column.
     * Each series contains a 'name' and 'data' value
     *
     * @return array ;
     */

    public function getDataSeries()
    {
        $dataColumns = $this->getDataColumns();

        $dataSeries = [];
        foreach ($dataColumns as $dataColumn) {

            $data = [];

            foreach ($this->values as $row) {
                if (array_key_exists($dataColumn, $row)) {
                    $data[] = $row[$dataColumn];
                } else {
                    $dataIndex = array_search($dataColumn, $this->labels, true);
                    $data[] = $row[$dataIndex];
                }
            }
            $dataSeries[] = ['name' => $dataColumn, 'data' => $data];
        }

        return $dataSeries;
    }

    /**
     * Return the data series for each defined data column.
     * Each series contains a 'name' and 'data' value
     *
     * @return array ;
     */

    public function getTimeSeries()
    {
        $dataColumns = $this->getDataColumns();
        $labelColumn = $this->getLabelColumn();

        $dataSeries = [];
        foreach ($dataColumns as $dataColumn) {

            $data = [];

            foreach ($this->values as $row) {
                $point = [];
                if (array_key_exists($dataColumn, $row)) {
                    $point['y'] = $row[$dataColumn];
                } else {
                    $dataIndex = array_search($dataColumn, $this->labels, true);
                    $point['y'] = $row[$dataIndex];
                }

                if (array_key_exists($labelColumn, $row)) {
                    $point['x'] = $row[$labelColumn];
                } else {
                    $labelIndex = array_search($labelColumn, $this->labels, true);
                    $point['x'] = $row[$labelIndex];
                }

                //convert value to timestamp
                //incoming date format should be in ISO-8601 format, ie 2020-04-27T15:19:21+00:00
                //in Twig this entry.postDate|date('c')
                $time = strtotime($point['x']);
                if ($time) {
                    $time *= 1000;
                    $point['x'] = $time;

                    if ($this->firstDate == 0 || $time < $this->firstDate) {
                        $this->firstDate = $time;
                    }

                    if ($this->lastDate == 0 || $time > $this->lastDate) {
                        $this->lastDate = $time;
                    }
                }

                $data[] = $point;
            }

            $dataSeries[] = ['name' => $dataColumn, 'data' => $data];
        }

        return $dataSeries;
    }

    /**
     * Returns the visualization settings template
     *
     * @param $settings
     *
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public function getSettingsHtml($settings): string
    {
        return Craft::$app->getView()->renderTemplate($this->settingsTemplate, ['settings' => $settings]);
    }

    /**
     * Returns the visualization results HTML
     *
     * @params $options values to pass through to the javascript charting instance
     *
     * @param array $options
     *
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public function getVisualizationHtml(array $options = []): string
    {
        return Craft::$app->getView()->renderTemplate($this->resultsTemplate,
            [
                'visualization' => $this,
                'options' => $options,
            ]);
    }

}