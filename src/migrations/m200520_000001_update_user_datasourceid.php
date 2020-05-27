<?php

namespace barrelstrength\sproutbasereports\migrations;

use craft\db\Migration;
use craft\db\Query;

class m200520_000001_update_user_datasourceid extends Migration
{
    /**
     * @inheritdoc
     * @noinspection ClassConstantCanBeUsedInspection
     */
    public function safeUp(): bool
    {
        if ($this->db->getIsPgsql()) {
            return true;
        }

        $userDataSourceId = (new Query())
            ->select('id')
            ->from('{{%sproutreports_datasources}}')
            ->where([
                'type' => 'barrelstrength\sproutbasereports\datasources\Users'
            ])
            ->scalar();

        if ($userDataSourceId) {
            $this->update('{{%sproutreports_reports}}', [
                'dataSourceId' => $userDataSourceId
            ], ['dataSourceId' => "CAST('sproutreports.users' AS VARCHAR)"], [], false);
        } else {
            // Remove unused User data sources so the next migration
            // adding a foreign key doesn't fail
            $this->delete('{{%sproutreports_reports}}', [
                'dataSourceId' => "CAST('sproutreports.users' AS VARCHAR)"
            ]);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m200520_000001_update_user_datasourceid cannot be reverted.\n";

        return false;
    }
}
