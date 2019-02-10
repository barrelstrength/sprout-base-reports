<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbasereports\controllers;

use barrelstrength\sproutbasereports\base\DataSource;
use barrelstrength\sproutbasereports\elements\Report;
use barrelstrength\sproutbasereports\models\ReportGroup;
use barrelstrength\sproutbasereports\records\Report as ReportRecord;
use barrelstrength\sproutbasereports\SproutBaseReports;
use Craft;

use craft\helpers\Json;
use craft\helpers\UrlHelper;
use craft\web\assets\cp\CpAsset;
use craft\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ReportsController extends Controller
{
    /**
     * @param null $dataSourceId
     * @param null $groupId
     *
     * @return \yii\web\Response
     * @throws \yii\base\Exception
     */
    public function actionIndex($dataSourceId = null, $groupId = null): Response
    {
        $currentPluginHandle = Craft::$app->request->getSegment(1);

        $dataSources = [];

        if ($currentPluginHandle !== 'sprout-reports') {

            $dataSource = SproutBaseReports::$app->dataSources->getDataSourceById($dataSourceId);

            // Update to match the multi-datasource syntax
            if ($dataSource) {
                $dataSources[get_class($dataSource)] = $dataSource;
            }

            $reports = SproutBaseReports::$app->reports->getReportsBySourceId($dataSourceId);
        } else {

            $dataSources = SproutBaseReports::$app->dataSources->getAllDataSources();

            if ($groupId !== null) {
                $reports = SproutBaseReports::$app->reports->getReportsByGroupId($groupId);
            } else {
                $reports = SproutBaseReports::$app->reports->getAllReports();
            }
        }

        $newReportOptions = [];

        foreach ($dataSources as $dataSource) {
            /**
             * @var $dataSource DataSource
             */
            // Ignore the allowNew setting if we're displaying a Reports integration
            if ($dataSource AND (bool)$dataSource->allowNew() OR $currentPluginHandle !== 'sprout-reports') {
                $newReportOptions[] = [
                    'name' => $dataSource->getName(),
                    'url' => $dataSource->getUrl($dataSource->dataSourceId.'/new')
                ];
            }
        }

        return $this->renderTemplate('sprout-base-reports/reports/index', [
            'dataSources' => $dataSources,
            'groupId' => $groupId,
            'reports' => $reports,
            'newReportOptions' => $newReportOptions,
            'currentPluginHandle' => $currentPluginHandle
        ]);
    }

    /**
     * @param Report|null $report
     * @param int|null    $reportId
     *
     * @return \yii\web\Response
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionResultsIndex(Report $report = null, int $reportId = null): Response
    {
        $currentPluginHandle = Craft::$app->request->getSegment(1);

        if ($report === null) {
            $report = SproutBaseReports::$app->reports->getReport($reportId);
        }

        if (!$report) {
            throw new NotFoundHttpException(Craft::t('sprout-base-reports', 'Report not found.'));
        }

        $dataSource = $report->getDataSource();

        if (!$dataSource) {
            throw new NotFoundHttpException(Craft::t('sprout-base-reports', 'Data Source not found.'));
        }

        $labels = $dataSource->getDefaultLabels($report);

        $variables['reportIndexUrl'] = $dataSource->getUrl($report->groupId);

        if ($currentPluginHandle !== 'sprout-reports') {
            $variables['reportIndexUrl'] = $dataSource->getUrl($dataSource->dataSourceId);
        }

        $variables['dataSource'] = null;
        $variables['report'] = $report;
        $variables['values'] = [];
        $variables['reportId'] = $reportId;
        $variables['redirectUrl'] = Craft::$app->getRequest()->getSegment(1).'/reports/view/'.$reportId;

        if ($dataSource) {
            $values = $dataSource->getResults($report);

            if (empty($labels) && !empty($values)) {
                $firstItemInArray = reset($values);
                $labels = array_keys($firstItemInArray);
            }

            $variables['labels'] = $labels;
            $variables['values'] = $values;
            $variables['dataSource'] = $dataSource;
        }

        $this->getView()->registerAssetBundle(CpAsset::class);

        // @todo Hand off to the export service when a blank page and 404 issues are sorted out
        return $this->renderTemplate('sprout-base-reports/results/index', $variables);
    }

    /**
     * @param string      $dataSourceId
     * @param Report|null $report
     * @param int|null    $reportId
     *
     * @return \yii\web\Response
     * @throws \yii\base\Exception
     */
    public function actionEditReport(string $dataSourceId, Report $report = null, int $reportId = null): Response
    {
        $currentPluginHandle = Craft::$app->request->getSegment(1);

        $reportElement = new Report();
        $reportElement->enabled = 1;

        if ($report !== null) {
            $reportElement = $report;
        } elseif ($reportId !== null) {
            $reportElement = SproutBaseReports::$app->reports->getReport($reportId);
        }

        // This is for creating new report
        if ($dataSourceId !== null) {
            $reportElement->dataSourceId = $dataSourceId;
        }

        $dataSource = $reportElement->getDataSource();

        if (!$dataSource) {
            throw new NotFoundHttpException(Craft::t('sprout-base-reports', 'Data Source not found.'));
        }

        $reportIndexUrl = $dataSource->getUrl($reportElement->groupId);

        if ($currentPluginHandle !== 'sprout-reports') {
            $reportIndexUrl = $dataSource->getUrl($dataSource->dataSourceId);
        }

        // Make sure you navigate to the right plugin page after saving and breadcrumb
        if (Craft::$app->request->getSegment(1) == 'sprout-reports') {
            $reportIndexUrl = UrlHelper::cpUrl('/sprout-reports/reports');
        }

        $groups = [];

        if (Craft::$app->getPlugins()->getPlugin('sprout-reports')) {
            $groups = SproutBaseReports::$app->reportGroups->getAllReportGroups();
        }

        return $this->renderTemplate('sprout-base-reports/reports/_edit', [
            'report' => $reportElement,
            'dataSource' => $dataSource,
            'reportIndexUrl' => $reportIndexUrl,
            'groups' => $groups,
            'continueEditingUrl' => $dataSource->getUrl()."/$dataSourceId/edit/{id}"
        ]);
    }

    /**
     * Saves a report query to the database
     *
     * @return null|\yii\web\Response
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionUpdateReport()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        $reportElement = new Report();

        $reportId = $request->getBodyParam('reportId');
        $settings = $request->getBodyParam('settings');

        if ($reportId && $settings) {
            $reportElement = SproutBaseReports::$app->reports->getReport($reportId);

            if (!$reportElement) {
                throw new NotFoundHttpException(Craft::t('sprout-base-reports', 'No report exists with the id “{id}”', ['id' => $reportId]));
            }

            $reportElement->settings = is_array($settings) ? $settings : [];

            if (SproutBaseReports::$app->reports->saveReport($reportElement)) {
                Craft::$app->getSession()->setNotice(Craft::t('sprout-base-reports', 'Query updated.'));

                return $this->redirectToPostedUrl($reportElement);
            }
        }

        // Encode back to object after validation for getResults method to recognize option object
        $reportElement->settings = Json::encode($reportElement->settings);

        Craft::$app->getSession()->setError(Craft::t('sprout-base-reports', 'Could not update report.'));

        // Send the report back to the template
        Craft::$app->getUrlManager()->setRouteParams([
            'report' => $reportElement
        ]);

        return null;
    }

    /**
     * Saves a report query to the database
     *
     * @return null|\yii\web\Response
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSaveReport()
    {
        $this->requirePostRequest();

        $report = $this->prepareFromPost();

        if ($report->validate() && Craft::$app->getElements()->saveElement($report)) {
            Craft::$app->getSession()->setNotice(Craft::t('sprout-base-reports', 'Report saved.'));

            return $this->redirectToPostedUrl($report);
        }

        Craft::$app->getSession()->setError(Craft::t('sprout-base-reports', 'Couldn’t save report.'));

        // Send the report back to the template
        Craft::$app->getUrlManager()->setRouteParams([
            'report' => $report
        ]);

        return null;
    }

    /**
     * Deletes a Report
     *
     * @return \yii\web\Response
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionDeleteReport(): Response
    {
        $this->requirePostRequest();

        $reportId = Craft::$app->getRequest()->getBodyParam('id');

        if ($record = ReportRecord::findOne($reportId)) {
            $record->delete();

            Craft::$app->getSession()->setNotice(Craft::t('sprout-base-reports', 'Report deleted.'));

            return $this->redirectToPostedUrl($record);
        }

        throw new NotFoundHttpException(Craft::t('sprout-base-reports', 'Report not found.'));
    }

    /**
     * Saves a Report Group
     *
     * @return Response
     * @throws \craft\errors\MissingComponentException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSaveGroup(): Response
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        $groupName = $request->getBodyParam('name');

        $group = new ReportGroup();
        $group->id = $request->getBodyParam('id');
        $group->name = $groupName;

        if (SproutBaseReports::$app->reportGroups->saveGroup($group)) {

            Craft::$app->getSession()->setNotice(Craft::t('sprout-base-reports', 'Report group saved.'));

            return $this->asJson([
                'success' => true,
                'group' => $group->getAttributes(),
            ]);
        }

        return $this->asJson([
            'errors' => $group->getErrors(),
        ]);
    }

    /**
     * Deletes a Report Group
     *
     * @return \yii\web\Response
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionDeleteGroup(): Response
    {
        $this->requirePostRequest();

        $groupId = Craft::$app->getRequest()->getBodyParam('id');
        $success = SproutBaseReports::$app->reportGroups->deleteGroup($groupId);

        Craft::$app->getSession()->setNotice(Craft::t('sprout-base-reports', 'Group deleted..'));

        return $this->asJson([
            'success' => $success,
        ]);
    }

    /**
     * Export a Report
     *
     * @throws \yii\base\Exception
     */
    public function actionExportReport()
    {
        $reportId = Craft::$app->getRequest()->getParam('reportId');
        $report = SproutBaseReports::$app->reports->getReport($reportId);
        $settings = Craft::$app->getRequest()->getBodyParam('settings') ?? [];

        if ($report) {
            $dataSource = SproutBaseReports::$app->dataSources->getDataSourceById($report->dataSourceId);

            if ($dataSource) {
                $date = date('Ymd-his');

                $filename = $report->name.'-'.$date;
                $labels = $dataSource->getDefaultLabels($report, $settings);
                $values = $dataSource->getResults($report, $settings);

                SproutBaseReports::$app->exports->toCsv($values, $labels, $filename);
            }
        }
    }

    /**
     * Returns a report model populated from saved/POSTed data
     *
     * @return Report
     * @throws \yii\base\Exception
     */
    public function prepareFromPost(): Report
    {
        $request = Craft::$app->getRequest();

        $reportId = $request->getBodyParam('id');

        if ($reportId && is_numeric($reportId)) {
            $report = SproutBaseReports::$app->reports->getReport($reportId);

            if (!$report) {
                $report->addError('id', Craft::t('sprout-base-reports', 'Could not find a report with id {reportId}', [
                    'reportId' => $reportId
                ]));
            }
        } else {
            $report = new Report();
        }

        $settings = $request->getBodyParam('settings');

        $report->name = $request->getBodyParam('name');
        $report->hasNameFormat = $request->getBodyParam('hasNameFormat');
        $report->nameFormat = $request->getBodyParam('nameFormat');
        $report->handle = $request->getBodyParam('handle');
        $report->description = $request->getBodyParam('description');
        $report->settings = is_array($settings) ? $settings : [];
        $report->dataSourceId = $request->getBodyParam('dataSourceId');
        $report->enabled = $request->getBodyParam('enabled', false);
        $report->groupId = $request->getBodyParam('groupId', null);

        $dataSource = $report->getDataSource();

        if (!$dataSource) {
            throw new NotFoundHttpException(Craft::t('sprout-base-reports', 'Date Source not found.'));
        }

        $report->allowHtml = $request->getBodyParam('allowHtml', $dataSource->getDefaultAllowHtml());

        return $report;
    }
}
