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
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\Exception
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
}
