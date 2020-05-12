<?php

namespace barrelstrength\sproutbasereports\migrations;

use barrelstrength\sproutbasereports\records\Report as ReportRecord;
use craft\db\Migration;
use yii\base\NotSupportedException;

class m200512_000000_add_reports_delimiter_column extends Migration
{
    /**
     * @inheritdoc
     *
     * @throws NotSupportedException
     */
    public function safeUp(): bool
    {
        if (!$this->db->columnExists(ReportRecord::tableName(), 'delimiter')) {
            $this->addColumn(ReportRecord::tableName(), 'delimiter', $this->string()->after('sortColumn'));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m200512_000000_add_reports_delimiter_column cannot be reverted.\n";

        return false;
    }
}
