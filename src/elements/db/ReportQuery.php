<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbasereports\elements\db;


use barrelstrength\sproutbasereports\base\SegmentDataSource;
use function Couchbase\defaultDecoder;
use craft\elements\db\ElementQuery;
use craft\helpers\Db;
use Craft;

class ReportQuery extends ElementQuery
{
    public $viewContext;

    public $id;

    public $name;

    public $hasNameFormat;

    public $nameFormat;

    public $handle;

    public $description;

    public $allowHtml;

    public $settings;

    public $dataSourceId;

    public $enabled;

    public $groupId;

    public $dateCreated;

    public $dateUpdated;

    public $results;

    /**
     * This is needed to dynamically set the Base URL for the "View Results" buttons. In this case,
     * the URL is not specific to a data source but to the module displaying the Reports.
     *
     * @var string
     */
    public $dataSourceBaseUrl;
    public $pluginHandle;

    public function viewContext($value)
    {
        $this->viewContext = $value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function beforePrepare(): bool
    {
        $this->joinElementTable('sproutreports_reports');
        $this->query->select([
            'sproutreports_reports.dataSourceId',
            'sproutreports_reports.name',
            'sproutreports_reports.hasNameFormat',
            'sproutreports_reports.nameFormat',
            'sproutreports_reports.handle',
            'sproutreports_reports.description',
            'sproutreports_reports.allowHtml',
            'sproutreports_reports.settings',
            'sproutreports_reports.groupId',
            'sproutreports_reports.enabled',
            'sproutreports_datasources.viewContext'
        ]);

        $this->query->innerJoin('{{%sproutreports_datasources}} sproutreports_datasources', '[[sproutreports_datasources.id]] = [[sproutreports_reports.dataSourceId]]');

        // Property is used for Element Sources in sidebar
        if (!$this->viewContext) {
            // The request is available on the Element Index page and used for plugin integrations using Sprout Reports
            $this->viewContext = Craft::$app->getRequest()->getBodyParam('criteria.viewContext') ?? Craft::$app->getRequest()->getParam('viewContext');
        }

        if ($this->viewContext) {
            $this->query->andWhere(Db::parseParam(
                'sproutreports_datasources.viewContext', $this->viewContext)
            );
        }

        // Exclude Segments from all other listing views
        if ($this->viewContext !== SegmentDataSource::DEFAULT_VIEW_CONTEXT) {
            // Exclude 'segments' from 'reports' context
            $this->query->andWhere([
                'not in',
                'sproutreports_datasources.viewContext',
                [SegmentDataSource::DEFAULT_VIEW_CONTEXT]
            ]);
        }

        if ($this->dataSourceId) {
            $this->query->andWhere(Db::parseParam(
                'sproutreports_reports.dataSourceId', $this->dataSourceId)
            );
        }

        if ($this->groupId) {
            $this->query->andWhere(Db::parseParam(
                'sproutreports_reports.groupId', $this->groupId)
            );
        }

        return parent::beforePrepare();
    }
}
