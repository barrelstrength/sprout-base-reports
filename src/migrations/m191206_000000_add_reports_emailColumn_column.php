<?php

namespace barrelstrength\sproutbasereports\migrations;

use craft\db\Migration;
use yii\base\NotSupportedException;

/**
 * m191206_000000_add_reports_emailColumn_column migration.
 */
class m191206_000000_add_reports_emailColumn_column extends Migration
{
    /**
     * @inheritdoc
     *
     * @throws NotSupportedException
     */
    public function safeUp(): bool
    {
        $table = '{{%sproutreports_reports}}';

        // Add a `viewContext` column
        if (!$this->db->columnExists($table, 'emailColumn')) {
            $this->addColumn($table, 'emailColumn', $this->string()->after('allowHtml'));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m191206_000000_add_reports_emailColumn_column cannot be reverted.\n";
        return false;
    }
}
