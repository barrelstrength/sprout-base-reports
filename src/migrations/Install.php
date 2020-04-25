<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbasereports\migrations;

use barrelstrength\sproutbasereports\elements\Report;
use barrelstrength\sproutbasereports\records\DataSource as DataSourceRecord;
use barrelstrength\sproutbasereports\records\Report as ReportRecord;
use barrelstrength\sproutbasereports\records\ReportGroup as ReportGroupRecord;
use craft\db\Migration;
use craft\db\Table;

class Install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (!$this->getDb()->tableExists(ReportRecord::tableName())) {
            $this->createTable(ReportRecord::tableName(),
                [
                    'id' => $this->primaryKey(),
                    'dataSourceId' => $this->integer(),
                    'groupId' => $this->integer(),
                    'name' => $this->string()->notNull(),
                    'hasNameFormat' => $this->boolean(),
                    'nameFormat' => $this->string(),
                    'handle' => $this->string()->notNull(),
                    'description' => $this->text(),
                    'allowHtml' => $this->boolean(),
                    'sortOrder' => $this->string(),
                    'sortColumn' => $this->string(),
                    'emailColumn' => $this->string(),
                    'settings' => $this->text(),
                    'enabled' => $this->boolean(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid()
                ]
            );

            $this->addForeignKey(null, ReportRecord::tableName(), ['id'], '{{%elements}}', ['id'], 'CASCADE');

            $this->createIndex($this->db->getIndexName(ReportRecord::tableName(), 'handle', true, true),
                ReportRecord::tableName(), 'handle', true);

            $this->createIndex($this->db->getIndexName(ReportRecord::tableName(), 'name', true, true),
                ReportRecord::tableName(), 'name', true);
        }

        if (!$this->getDb()->tableExists(ReportGroupRecord::tableName())) {
            $this->createTable(ReportGroupRecord::tableName(), [
                'id' => $this->primaryKey(),
                'name' => $this->string()->notNull(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid()
            ]);

            $this->createIndex(
                $this->db->getIndexName(ReportGroupRecord::tableName(), 'name', false, true),
                ReportGroupRecord::tableName(),
                'name'
            );
        }

        if (!$this->getDb()->tableExists(DataSourceRecord::tableName())) {
            $this->createTable(DataSourceRecord::tableName(), [
                'id' => $this->primaryKey(),
                'viewContext' => $this->string(),
                'type' => $this->string(),
                'allowNew' => $this->boolean(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid()
            ]);
        }
    }

    public function safeDown()
    {
        // Delete Report Elements
        $this->delete(Table::ELEMENTS, ['type' => Report::class]);

        $this->dropTableIfExists(ReportRecord::tableName());
        $this->dropTableIfExists(ReportGroupRecord::tableName());
        $this->dropTableIfExists(DataSourceRecord::tableName());
    }
}