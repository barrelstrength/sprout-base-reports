<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbasereports\elements\db;


use craft\elements\db\ElementQuery;
use craft\helpers\Db;
use Craft;

class ReportQuery extends ElementQuery
{
    public $pluginHandle;

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
            'sproutreports_datasources.pluginHandle'
        ]);

        $this->query->innerJoin('{{%sproutreports_datasources}} sproutreports_datasources', '[[sproutreports_datasources.id]] = [[sproutreports_reports.dataSourceId]]');

        // Property is used for Element Sources in sidebar
        if (!$this->pluginHandle) {
            // The request is available on the Element Index page and used for plugin integrations using Sprout Reports
            $this->pluginHandle = Craft::$app->request->getBodyParam('criteria.pluginHandle');
        }

        if ($this->pluginHandle) {
            $this->query->andWhere(Db::parseParam(
                'sproutreports_datasources.pluginHandle', $this->pluginHandle)
            );
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