<?php

namespace barrelstrength\sproutbasereports\visualizations;

use Craft;
use craft\base\Component;

abstract class BaseVisualization extends Component
{

    protected $settingsTemplate = "";

    protected $resultsTemplate = "";

    protected $title = "";

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
     * @param Array $values
     */

    protected $values;

    /**
     * Set the visualization labels
     *
     * @param Array $labels
     */

    protected $labels;

    /**
     * Returns the visualization title
     *
     * @returns string
     */

    public function getTitle()
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
     * Returns the first (earliest) timestamp value from the data series for a time series visualizaton
     *
     * @returns Number
     */
    public function getFirstDate()
    {
        return $this->firstDate;
    }

    /**
     * Returns the last (latest) timestamp value from the data series for a time series visualizaton
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
     * @param Array $settings
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
        if ($this->settings) {
            if (is_array($this->settings['dataColumns'])) {
                return $this->settings['dataColumns'];
            } else {
                return [$this->settings['dataColumns']];
            }
        } else {
            return false;
        }

        return $dataColumns;
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
        } else {
            return false;
        }
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
            $labelIndex = array_search($labelColumn, $this->labels);
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
     * @return Array;
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
                    $dataIndex = array_search($dataColumn, $this->labels);
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
     * @return Array;
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
                    $dataIndex = array_search($dataColumn, $this->labels);
                    $point['y'] = $row[$dataIndex];
                }

                if (array_key_exists($labelColumn, $row)) {
                    $point['x'] = $row[$labelColumn];
                } else {
                    $labelIndex = array_search($labelColumn, $this->labels);
                    $point['x'] = $row[$labelIndex];
                }

                //convert value to timestamp
                //incoming date format should be in ISO-8601 format, ie 2020-04-27T15:19:21+00:00
                //in Twig this entry.postDate|date('c')
                $time = strtotime($point['x']);
                if ($time) {
                    $time = $time * 1000;
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
     * Return the visualization settings template.
     *
     * @params $settings
     *
     * @return string
     */

    public function getSettingsHtml($settings): string
    {
        return Craft::$app->getView()->renderTemplate($this->settingsTemplate, ['settings' => $settings]);
    }

    /**
     * Return the visualization results html.
     *
     * @params $options values to pass through to the javascript charting instance
     *
     * @return string
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