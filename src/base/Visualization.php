<?php

namespace barrelstrength\sproutbasereports\base;

use craft\base\Component;

/**
 * Class Visualization
 *
 * @package barrelstrength\sproutbasereports\visualizations
 *
 * @property array $timeSeries
 * @property array $settings
 * @property array $dataSeries
 */
abstract class Visualization extends Component implements VisualizationInterface
{
    /**
     * if this is a date time report stores the earliest timestamp value from the data series
     */
    protected $startDate = 0;

    /**
     * if this is a date time report stores the latest timestamp value from the data series
     */
    protected $endDate = 0;

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
     * Returns the first (earliest) timestamp value from the data series for a time series visualization
     *
     * @returns Number
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Returns the last (latest) timestamp value from the data series for a time series visualization
     *
     * @returns Number
     */
    public function getEndDate()
    {
        return $this->endDate;
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

    public function setValues(array $values)
    {
        $this->values = $values;
    }

    public function getLabels(): array
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
     * @inheritDoc
     */
    public function getSettingsHtml(array $settings): string
    {
        return '';
    }

    /**
     * Return the data series for each defined data column.
     * Each series contains a 'name' and 'data' value
     *
     * @return array
     */

    public function getDataSeries(): array
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
     * @return array
     */

    public function getTimeSeries(): array
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

                    if ($this->startDate == 0 || $time < $this->startDate) {
                        $this->startDate = $time;
                    }

                    if ($this->endDate == 0 || $time > $this->endDate) {
                        $this->endDate = $time;
                    }
                }

                $data[] = $point;
            }

            $dataSeries[] = ['name' => $dataColumn, 'data' => $data];
        }

        return $dataSeries;
    }
}