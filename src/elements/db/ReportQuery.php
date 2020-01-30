<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbasereports\elements\db;


use barrelstrength\sproutbasereports\base\DataSource;
use Craft;
use craft\elements\db\ElementQuery;
use craft\helpers\Db;

class ReportQuery extends ElementQuery
{
    public $id;

    public $name;

    public $hasNameFormat;

    public $nameFormat;

    public $handle;

    public $description;

    public $allowHtml;

    public $settings;

    public $viewContext;

    public $emailColumn;

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

    /**
     * @return bool
     * @throws \craft\errors\MissingComponentException
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
            'sproutreports_reports.emailColumn',
            'sproutreports_datasources.viewContext',
        ]);

        $this->query->innerJoin('{{%sproutreports_datasources}} sproutreports_datasources', '[[sproutreports_datasources.id]] = [[sproutreports_reports.dataSourceId]]');

        // Property is used for Element Sources in sidebar
        if (!$this->viewContext && !Craft::$app->getRequest()->getIsConsoleRequest()) {
            // Request available on Element Index page and used for plugin integrations using Sprout Reports
            $this->viewContext = Craft::$app->getSession()->get('sprout.viewContext');
        }

        // Limit results by viewContext when not viewing in Sprout Reports
        if ($this->viewContext && $this->viewContext !== DataSource::DEFAULT_VIEW_CONTEXT) {
            $viewContextList = array_unique([
                $this->viewContext
            ]);

            $this->query->andWhere(
                ['in', 'sproutreports_datasources.viewContext', $viewContextList]
            );
        }

        if ($this->emailColumn) {
            $this->query->andWhere(Db::parseParam(
                '[[sproutreports_reports.emailColumn]]', $this->emailColumn
            ));
        }

        if ($this->dataSourceId) {
            $this->query->andWhere(Db::parseParam(
                '[[sproutreports_reports.dataSourceId]]', $this->dataSourceId)
            );
        }

        if ($this->groupId) {
            $this->query->andWhere(Db::parseParam(
                '[[sproutreports_reports.groupId]]', $this->groupId)
            );
        }

        return parent::beforePrepare();
    }
}
