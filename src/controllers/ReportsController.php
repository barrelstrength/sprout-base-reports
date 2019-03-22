<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbasereports\controllers;

use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutbasereports\base\DataSource;
use barrelstrength\sproutbasereports\elements\Report;
use barrelstrength\sproutbasereports\models\ReportGroup;
use barrelstrength\sproutbasereports\records\Report as ReportRecord;
use barrelstrength\sproutbasereports\SproutBaseReports;
use barrelstrength\sproutbasereports\models\Settings;
use Craft;
use craft\helpers\Json;
use craft\helpers\UrlHelper;
use craft\web\assets\cp\CpAsset;
use craft\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ReportsController extends Controller
{
    private $permissions = [];

    public function init()
    {
        $this->permissions = SproutBase::$app->settings->getPluginPermissions(new Settings(), 'sprout-reports');

        parent::init();
    }

    /**
     * @param string $pluginHandle
     * @param null   $dataSourceId
     * @param null   $groupId
     * @param bool   $hideSidebar
     *
     * @return Response
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionReportsIndexTemplate(string $pluginHandle, $dataSourceId = null, $groupId = null, $hideSidebar = false): Response
    {
        $this->requirePermission($this->permissions['sproutReports-viewReports']);

        $dataSources = [];

        if ($pluginHandle !== 'sprout-reports') {

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
            if ($dataSource AND (bool)$dataSource->allowNew() OR $pluginHandle !== 'sprout-reports') {
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
            'viewReportsPermission' => $this->permissions['sproutReports-viewReports'],
            'editReportsPermission' => $this->permissions['sproutReports-editReports'],
            'hideSidebar' => $hideSidebar
        ]);
    }

    /**
     * @param string      $pluginHandle
     * @param Report|null $report
     * @param int|null    $reportId
     *
     * @return Response
     * @throws NotFoundHttpException
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionResultsIndexTemplate(string $pluginHandle, Report $report = null, int $reportId = null): Response
    {
        $this->requirePermission($this->permissions['sproutReports-viewReports']);

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

        $reportIndexUrl = $dataSource->getUrl($report->groupId);

        if ($pluginHandle !== 'sprout-reports') {
            $reportIndexUrl = $dataSource->getUrl($dataSource->dataSourceId);
        }

        $values = $dataSource->getResults($report);

        if (empty($labels) && !empty($values)) {
            $firstItemInArray = reset($values);
            $labels = array_keys($firstItemInArray);
        }

        $this->getView()->registerAssetBundle(CpAsset::class);

        return $this->renderTemplate('sprout-base-reports/results/index', [
            'report' => $report,
            'dataSource' => $dataSource,
            'labels' => $labels,
            'values' => $values,
            'reportIndexUrl' => $reportIndexUrl,
            'redirectUrl' => Craft::$app->getRequest()->getSegment(1).'/reports/view/'.$reportId,
            'viewReportsPermission' => $this->permissions['sproutReports-viewReports'],
            'editReportsPermission' => $this->permissions['sproutReports-editReports']
        ]);
    }

    /**
     * @param string      $pluginHandle
     * @param string      $dataSourceId
     * @param Report|null $report
     * @param int|null    $reportId
     *
     * @return Response
     * @throws NotFoundHttpException
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionEditReportTemplate(string $pluginHandle, string $dataSourceId, Report $report = null, int $reportId = null): Response
    {
        $this->requirePermission($this->permissions['sproutReports-editReports']);

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

        if ($pluginHandle !== 'sprout-reports') {
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
            'continueEditingUrl' => $dataSource->getUrl()."/$dataSourceId/edit/{id}",
            'editReportsPermission' => $this->permissions['sproutReports-editReports']
        ]);
    }

    /**
     * Saves a report query to the database
     *
     * @return Response|null
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \craft\errors\MissingComponentException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionUpdateReport()
    {
        $this->requirePostRequest();
        $this->requirePermission($this->permissions['sproutReports-editReports']);

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
        $this->requirePermission($this->permissions['sproutReports-editReports']);

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
        $this->requirePermission($this->permissions['sproutReports-editReports']);

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
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionSaveGroup(): Response
    {
        $this->requirePostRequest();
        $this->requirePermission($this->permissions['sproutReports-editReports']);

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
        $this->requirePermission($this->permissions['sproutReports-editReports']);

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
        $this->requirePermission($this->permissions['sproutReports-viewReports']);

        $reportId = Craft::$app->getRequest()->getParam('reportId');
        $report = SproutBaseReports::$app->reports->getReport($reportId);
        $settings = Craft::$app->getRequest()->getBodyParam('settings') ?? [];

        if ($report) {
            $dataSource = SproutBaseReports::$app->dataSources->getDataSourceById($report->dataSourceId);

            if ($dataSource) {
                $date = date('Ymd-his');

                // Name the report using the $report toString method that will check both nameFormat and name
                $filename = $report.'-'.$date;

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
        $report->groupId = $request->getBodyParam('groupId');

        $dataSource = $report->getDataSource();

        if (!$dataSource) {
            throw new NotFoundHttpException(Craft::t('sprout-base-reports', 'Date Source not found.'));
        }

        $report->allowHtml = $request->getBodyParam('allowHtml', $dataSource->getDefaultAllowHtml());

        return $report;
    }
}
