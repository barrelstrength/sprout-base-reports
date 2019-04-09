<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbasereports\services;

use barrelstrength\sproutbasereports\elements\Report;
use barrelstrength\sproutreports\SproutReports;
use Craft;
use craft\db\Query;
use craft\errors\ElementNotFoundException;
use yii\base\Component;
use barrelstrength\sproutbasereports\records\Report as ReportRecord;
use barrelstrength\sproutbasereports\records\ReportGroup as ReportGroupRecord;
use yii\base\Exception;
use craft\helpers\DateTimeHelper;

/**
 *
 * @property null|\barrelstrength\sproutbasereports\elements\Report[] $allReports
 * @property array                                                    $reportsAsSelectFieldOptions
 * @property \craft\db\Query                                          $reportsQuery
 */
class Reports extends Component
{
    /**
     * @param $reportId
     *
     * @return Report
     * @throws ElementNotFoundException
     */
    public function getReport($reportId): Report
    {
        $reportRecord = ReportRecord::findOne($reportId);

        if (!$reportRecord) {
            throw new ElementNotFoundException(Craft::t('sprout-base-reports', 'Unable to find Report.'));
        }

        $report = new Report();

        $report->id = $reportRecord->id;
        $report->dataSourceId = $reportRecord->dataSourceId;
        $report->groupId = $reportRecord->groupId;
        $report->name = $reportRecord->name;
        $report->hasNameFormat = $reportRecord->hasNameFormat;
        $report->nameFormat = $reportRecord->nameFormat;
        $report->handle = $reportRecord->handle;
        $report->description = $reportRecord->description;
        $report->allowHtml = $reportRecord->allowHtml;
        $report->settings = $reportRecord->settings;
        $report->enabled = $reportRecord->enabled;

        return $report;
    }

    /**
     * @param Report $report
     *
     * @return bool
     * @throws \Throwable
     */
    public function saveReport(Report $report): bool
    {
        if (!$report) {

            Craft::info('Report not saved due to validation error.', __METHOD__);

            return false;
        }

        $report->title = $report->name;

        $report->validate();

        if ($report->hasErrors()) {

            SproutReports::error('Unable to save Report.');

            return false;
        }

        $transaction = Craft::$app->db->beginTransaction();

        try {
            Craft::$app->getElements()->saveElement($report, false);

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();

            throw $e;
        }

        return true;
    }

    /**
     * @param Report $report
     *
     * @return bool
     * @throws Exception
     */
    protected function validateSettings(Report $report): bool
    {
        $errors = [];

        $dataSource = $report->getDataSource();

        if ($dataSource AND !$dataSource->validateSettings($report->settings, $errors)) {
            $report->addError('settings', $errors);

            return false;
        }

        return true;
    }

    /**
     * @param $dataSourceId
     *
     * @return array
     */
    public function getReportsBySourceId($dataSourceId): array
    {
        $reportRecords = ReportRecord::find()->where(['dataSourceId' => $dataSourceId])->all();

        return $this->populateModels($reportRecords);
    }

    /**
     * @return null|Report[]
     */
    public function getAllReports()
    {
        $rows = $this->getReportsQuery()->all();

        return $this->populateReports($rows);
    }

    private function getReportsQuery(): Query
    {
        $query = new Query();
        // We only get reports that currently has dataSourceId or existing installed dataSource
        $query->select('reports.*')
            ->from('{{%sproutreports_reports}} as reports')
            ->innerJoin('{{%sproutreports_datasources}} as datasource', '[[datasource.id]] = [[reports.dataSourceId]]');

        return $query;
    }

    private function populateReports($rows): array
    {
        $reports = [];

        if ($rows) {
            foreach ($rows as $row) {

                $model = new Report();
                $model->setAttributes($row, false);
                $reports[] = $model;
            }
        }

        return $reports;
    }

    /**
     * @param $groupId
     *
     * @return array
     * @throws Exception
     */
    public function getReportsByGroupId($groupId): array
    {
        $reports = [];

        $group = ReportGroupRecord::findOne($groupId);

        if ($group === null) {
            throw new Exception(Craft::t('sprout-base-reports', 'No Report Group exists with id: {id}', [
                'id' => $groupId
            ]));
        }

        if ($group !== null) {
            $rows = $this->getReportsQuery()->where([
                'groupId' => $groupId
            ])->all();

            $reports = $this->populateReports($rows);
        }

        return $reports;
    }

    public function getReportsAsSelectFieldOptions(): array
    {
        $options = [];

        $reports = $this->getAllReports();

        if ($reports) {
            foreach ($reports as $report) {
                $options[] = [
                    'label' => $report->name,
                    'value' => $report->id,
                ];
            }
        }
        return $options;
    }

    /**
     * Returns the number of reports that have been created based on a given data source
     *
     * @param $dataSourceId
     *
     * @return int
     *
     */
    public function getCountByDataSourceId($dataSourceId): int
    {
        return (int)ReportRecord::find()->where(['dataSourceId' => $dataSourceId])->count();
    }

    /**
     * @param array $records
     *
     * @return array
     */
    public function populateModels(array $records): array
    {
        $models = [];

        if (!empty($records)) {
            foreach ($records as $record) {
                $recordAttributes = $record->getAttributes();
                $model = new Report();
                $model->setAttributes($recordAttributes);

                $models[] = $model;
            }
        }

        return $models;
    }

    /**
     * Convert DateTime to UTC to get correct result when querying SQL. SQL data is always on UTC.
     * @param $dateSetting
     *
     * @return \DateTime|false
     * @throws \Exception
     */
    public function getUtcDateTime($dateSetting)
    {
        $timeZone =  new \DateTimeZone('UTC');

        return DateTimeHelper::toDateTime($dateSetting, true)->setTimezone($timeZone);
    }

    public function getStartEndDateRange($value)
    {
        // The date function still return date based on the cpPanel timezone settings
        $dateTime = [
            'startDate' => date('Y-m-d H:i:s'),
            'endDate'   => date('Y-m-d H:i:s')
        ];

        switch ($value) {

            case 'thisWeek':
                $dateTime['startDate'] = date('Y-m-d H:i:s', strtotime('-7 days'));
            break;

            case 'lastWeek':
                $dateTime['startDate'] = date('Y-m-d H:i:s', strtotime('-8 days'));
                $dateTime['endDate']   = date('Y-m-d H:i:s', strtotime('-1 days'));
            break;

            case 'thisMonth':

                $dateTime['startDate'] = date('Y-m-1 00:00:00');
                $dateTime['endDate'] = date('Y-m-t 00:00:00');

            break;

            case 'lastMonth':

                $dateTime['startDate'] = date('Y-m-1 00:00:00', strtotime('-1 month'));
                $dateTime['endDate'] = date('Y-m-t 00:00:00', strtotime('-1 month'));

            break;

            case 'thisQuarter':
                $dateTime = $this->thisQuarter();
            break;

            case 'lastQuarter':
                $dateTime = $this->lastQuarter();
            break;

            case 'thisYear':
                $dateTime['startDate'] = date('Y-1-1 00:00:00');
                $dateTime['endDate'] = date('Y-12-t 00:00:00');
            break;

            case 'lastYear':
                $dateTime['startDate'] = date('Y-1-1 00:00:00', strtotime('-1 year'));
                $dateTime['endDate'] = date('Y-12-t 00:00:00', strtotime('-1 year'));
            break;
        }

        return $dateTime;
    }

    private function thisQuarter()
    {
        $startDate = '';
        $endDate   = '';
        $current_month = date('m');
        $current_year = date('Y');
        if($current_month>=1 && $current_month<=3)
        {
            $startDate = strtotime('1-January-'.$current_year);  // timestamp or 1-Januray 12:00:00 AM
            $endDate = strtotime('31-March-'.$current_year);  // timestamp or 1-April 12:00:00 AM means end of 31 March
        }
        else  if($current_month>=4 && $current_month<=6)
        {
            $startDate = strtotime('1-April-'.$current_year);  // timestamp or 1-April 12:00:00 AM
            $endDate = strtotime('30-June-'.$current_year);  // timestamp or 1-July 12:00:00 AM means end of 30 June
        }
        else  if($current_month>=7 && $current_month<=9)
        {
            $startDate = strtotime('1-July-'.$current_year);  // timestamp or 1-July 12:00:00 AM
            $endDate = strtotime('30-September-'.$current_year);  // timestamp or 1-October 12:00:00 AM means end of 30 September
        }
        else  if($current_month>=10 && $current_month<=12)
        {
            $startDate = strtotime('1-October-'.$current_year);  // timestamp or 1-October 12:00:00 AM
            $endDate = strtotime('31-December-'.$current_year);  // timestamp or 1-January Next year 12:00:00 AM means end of 31 December this year
        }

        return [
            'startDate' => date('Y-m-d H:i:s', $startDate),
            'endDate'   => date('Y-m-d H:i:s', $endDate)
        ];
    }

    private function lastQuarter()
    {
        $startDate = '';
        $endDate   = '';
        $current_month = date('m');
        $current_year = date('Y');

        if($current_month>=1 && $current_month<=3)
        {
            $startDate = strtotime('1-October-'.($current_year-1));  // timestamp or 1-October Last Year 12:00:00 AM
            $endDate = strtotime('31-December-'.($current_year-1));  // // timestamp or 1-January  12:00:00 AM means end of 31 December Last year
        }
        else if($current_month>=4 && $current_month<=6)
        {
            $startDate = strtotime('1-January-'.$current_year);  // timestamp or 1-Januray 12:00:00 AM
            $endDate = strtotime('31-March-'.$current_year);  // timestamp or 1-April 12:00:00 AM means end of 31 March
        }
        else  if($current_month>=7 && $current_month<=9)
        {
            $startDate = strtotime('1-April-'.$current_year);  // timestamp or 1-April 12:00:00 AM
            $endDate = strtotime('30-June-'.$current_year);  // timestamp or 1-July 12:00:00 AM means end of 30 June
        }
        else  if($current_month>=10 && $current_month<=12)
        {
            $startDate = strtotime('1-July-'.$current_year);  // timestamp or 1-July 12:00:00 AM
            $endDate = strtotime('30-September-'.$current_year);  // timestamp or 1-October 12:00:00 AM means end of 30 September
        }

        return [
            'startDate' => date('Y-m-d H:i:s',$startDate),
            'endDate'   => date('Y-m-d H:i:s', $endDate)
        ];
    }

    public function getDateRanges()
    {
        return [
            'thisWeek' => Craft::t('sprout-base-reports', 'This Week'),
            'lastWeek' => Craft::t('sprout-base-reports', 'Last Week'),
            'thisMonth' => Craft::t('sprout-base-reports', 'This Month'),
            'lastMonth' => Craft::t('sprout-base-reports', 'Last Month'),
            'thisQuarter' => Craft::t('sprout-base-reports', 'This Quarter'),
            'lastQuarter' => Craft::t('sprout-base-reports', 'Last Quarter'),
            'thisYear' => Craft::t('sprout-base-reports', 'This Year'),
            'lastYear' => Craft::t('sprout-base-reports', 'Last Week'),
            'customRange' => Craft::t('sprout-base-reports', 'Custom Date Range')
        ];
    }
}
