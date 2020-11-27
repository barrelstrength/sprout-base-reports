<?php

namespace barrelstrength\sproutbasereports\migrations;

use craft\db\Migration;
use craft\db\Query;
use yii\base\NotSupportedException;

class m200520_000002_update_datasourceId_column_type extends Migration
{
    /**
     * @return bool
     * @throws NotSupportedException
     */
    public function safeUp(): bool
    {
        $reportTable = '{{%sproutreports_reports}}';

        // Updates foreign key if it does not exist. Try catch avoid errors if it exist
        if ($this->db->columnExists($reportTable, 'dataSourceId')) {

            $this->cleanUpUsersDataSourceStuff();

            if ($this->db->getIsPgsql()) {
                // Manually construct the SQL for Postgres`
                // (see https://github.com/yiisoft/yii2/issues/12077)
                $this->execute('alter table {{%sproutreports_reports}} alter column [[dataSourceId]] type integer using [[dataSourceId]]::integer, alter column [[dataSourceId]] drop not null');
            } else {
                $this->alterColumn('{{%sproutreports_reports}}', 'dataSourceId', $this->integer());
            }
        }

        return true;
    }

    public function cleanUpUsersDataSourceStuff() {

        $usersDataSourceId = (new Query())
            ->select('id')
            ->from('{{%sproutreports_datasources}}')
            ->where([
                'type' => 'barrelstrength\sproutbasereports\datasources\Users'
            ])
            ->scalar();

        if ($usersDataSourceId) {
            // update reports table with this ID
            $this->update('{{%sproutreports_reports}}', [
                'dataSourceId' => $usersDataSourceId
            ], ['dataSourceId' => 'sproutreports.users'], [], false);
        } else {
            // remove all rows from reports table that match dataSourceId sproutreports.users
            $this->delete('{{%sproutreports_reports}}', [
                'dataSourceId' => 'sproutreports.users'
            ]);
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m200520_000002_update_datasourceId_column_type cannot be reverted.\n";

        return false;
    }
}
