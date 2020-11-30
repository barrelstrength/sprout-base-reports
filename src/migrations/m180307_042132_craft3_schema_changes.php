<?php

namespace barrelstrength\sproutbasereports\migrations;

use barrelstrength\sproutbasereports\elements\Report;
use barrelstrength\sproutbasereports\records\Report as ReportRecord;
use craft\db\Migration;
use craft\db\Query;
use yii\base\NotSupportedException;
use Craft;

/**
 * m180307_042132_craft3_schema_changes migration.
 */
class m180307_042132_craft3_schema_changes extends Migration
{
    /**
     * @inheritdoc
     *
     * @throws NotSupportedException
     */
    public function safeUp(): bool
    {
        // Update Reports Table columns
        if (!$this->db->columnExists(ReportRecord::tableName(), 'hasNameFormat')) {
            $this->addColumn(ReportRecord::tableName(), 'hasNameFormat', $this->integer()->after('name'));
        }

        if (!$this->db->columnExists(ReportRecord::tableName(), 'nameFormat')) {
            $this->addColumn(ReportRecord::tableName(), 'nameFormat', $this->string()->after('name'));
        }

        if (!$this->db->columnExists(ReportRecord::tableName(), 'sortOrder')) {
            $this->addColumn(ReportRecord::tableName(), 'sortOrder', $this->string()->after('allowHtml'));

            $this->addColumn(ReportRecord::tableName(), 'sortColumn', $this->string()->after('sortOrder'));
        }

        if (!$this->db->columnExists(ReportRecord::tableName(), 'emailColumn')) {
            $this->addColumn(ReportRecord::tableName(), 'emailColumn', $this->string()->after('allowHtml'));
        }

        if (!$this->db->columnExists(ReportRecord::tableName(), 'delimiter')) {
            $this->addColumn(ReportRecord::tableName(), 'delimiter', $this->string()->after('sortColumn'));
        }

        if (!$this->db->columnExists(ReportRecord::tableName(), 'settings')) {
            $this->renameColumn(ReportRecord::tableName(), 'options', 'settings');
        }

        // Update Data Source Table columns
        if (!$this->db->columnExists('{{%sproutreports_datasources}}', 'type')) {
            $this->renameColumn('{{%sproutreports_datasources}}', 'dataSourceId', 'type');
        }

        $this->prepExistingDataSourcesUsingOldDataSourceIdFormat();

        $dataSourcesMap = [
            'sproutreports.query' => 'barrelstrength\sproutreports\integrations\sproutreports\datasources\CustomQuery',
            'sproutreports.twig' => 'barrelstrength\sproutreports\integrations\sproutreports\datasources\CustomTwigTemplate'
        ];

        // Update our Data Source records and related IDs in the Reports table
        foreach ($dataSourcesMap as $oldDataSourceId => $dataSourceClass) {

            $query = new Query();

            // See if our old data source exists
            $dataSource = $query->select('*')
                ->from(['{{%sproutreports_datasources}}'])
                ->where(['type' => $oldDataSourceId])
                ->one();

            if ($dataSource === null) {
                // If not, see if our new Data Source exists
                $dataSource = $query->select('*')
                    ->from(['{{%sproutreports_datasources}}'])
                    ->where(['type' => $dataSourceClass])
                    ->one();
            }

            // If we don't have a Data Source record, add it
            if ($dataSource === null) {
                $this->insert('{{%sproutreports_datasources}}', [
                    'type' => $dataSourceClass,
                    'allowNew' => 1
                ]);
                $dataSource['id'] = $this->db->getLastInsertID('{{%sproutreports_datasources}}');
                $dataSource['allowNew'] = 1;
            }

            // Update our existing or new Data Source
            $this->update('{{%sproutreports_datasources}}', [
                'type' => $dataSourceClass,
                'allowNew' => $dataSource['allowNew'] ?? 1
            ], [
                'id' => $dataSource['id']
            ], [], false);

            // Update any related dataSourceIds in our Reports table
            $this->update('{{%sproutreports_reports}}', [
                'dataSourceId' => $dataSource['id']
            ], [
                'dataSourceId' => $oldDataSourceId
            ], [], false);
        }

        // Remove Data Source Table columns
        if ($this->db->columnExists('{{%sproutreports_datasources}}', 'options')) {
            $this->dropColumn('{{%sproutreports_datasources}}', 'options');
        }

        // Make Report Records Elements
        $query = new Query();
        $db = Craft::$app->getDb();

        // Get all reports from the report table
        $reports = $query->select('*')
            ->from(['{{%sproutreports_reports}}'])
            ->all();

        if (empty($reports)) {
            return true;
        }

        foreach ($reports as $report) {

            // Only convert report record to element if it doesn't exist in the elements table
            $elementExists = $query->select('id')
                ->from('{{%elements}}')
                ->where([
                    'id' => $report['id'],
                    'type' => 'barrelstrength\sproutbasereports\elements\Report'
                ])
                ->one();

            if ($elementExists) {
                continue;
            }

            $db->createCommand()->delete('{{%sproutreports_reports}}',
                ['id' => $report['id']])->execute();

            unset($report['id']);

            $reportElement = new Report();

            // Migrated attributes
            $reportElement->dataSourceId = $report['dataSourceId'];
            $reportElement->groupId = $report['groupId'];
            $reportElement->name = $report['name'];
            $reportElement->nameFormat = $report['nameFormat'];
            $reportElement->handle = $report['handle'];
            $reportElement->description = $report['description'];
            $reportElement->allowHtml = $report['allowHtml'];
            $reportElement->enabled = $report['enabled'];
            $reportElement->settings = $report['settings'];
            $reportElement->dateCreated = $report['dateCreated'];
            $reportElement->dateCreated = $report['dateCreated'];

            // New attributes
            $reportElement->hasNameFormat = !empty($report['nameFormat']) ? 1 : 0;
            $reportElement->sortOrder = '';
            $reportElement->sortColumn = '';
            $reportElement->delimiter = '';
            $reportElement->emailColumn = '';

            Craft::$app->getElements()->saveElement($reportElement, false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m180307_042132_craft3_schema_changes cannot be reverted.\n";

        return false;
    }

    // Developers have to make sure the old datasources are saved in the db
    // before triggering the upgrade/migrations or this won't do anything
    public function prepExistingDataSourcesUsingOldDataSourceIdFormat() {

        $dataSourcesInDb = (new Query())
            ->select(['id', 'type'])
            ->from('{{%sproutreports_datasources}}')
            ->all();

        if (!$dataSourcesInDb) {
            return;
        }

        foreach($dataSourcesInDb as $dataSource) {
            $dataSourceId = $dataSource['id'];
            $oldDataSourceId = $dataSource['type'];

            $this->update('{{%sproutreports_reports}}', [
                'dataSourceId' => $dataSourceId,
            ], ['dataSourceId' => $oldDataSourceId], [], false);
        }
    }
}
