<?php

namespace barrelstrength\sproutbasereports\migrations;

use craft\db\Migration;
use craft\db\Query;
use yii\base\NotSupportedException;

/**
 * m190818_000000_add_reportgroups_viewContext_column migration.
 */
class m190818_000000_add_reportgroups_viewContext_column extends Migration
{
    /**
     * @inheritdoc
     *
     * @throws NotSupportedException
     */
    public function safeUp(): bool
    {
        $table = '{{%sproutreports_reportgroups}}';

        // Add a `viewContext` column
        if (!$this->db->columnExists($table, 'viewContext')) {
            $this->addColumn($table, 'viewContext', $this->string()->after('id'));
        }

        $reportGroups = (new Query())
            ->select(['*'])
            ->from([$table])
            ->all();

        foreach ($reportGroups as $reportGroup) {
            $this->update($table, ['viewContext' => 'reports'], ['id' => $reportGroup['id']], [], false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m190818_000000_add_reportgroups_viewContext_column cannot be reverted.\n";
        return false;
    }
}
