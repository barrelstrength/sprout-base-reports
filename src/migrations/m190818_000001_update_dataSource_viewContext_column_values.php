<?php

namespace barrelstrength\sproutbasereports\migrations;

use craft\db\Migration;
use craft\db\Query;
use yii\base\NotSupportedException;

/**
 * m190818_000001_update_dataSource_viewContext_column_values migration.
 */
class m190818_000001_update_dataSource_viewContext_column_values extends Migration
{
    /**
     * @inheritdoc
     *
     * @throws NotSupportedException
     */
    public function safeUp(): bool
    {
        $table = '{{%sproutreports_datasources}}';

        // Migrate data to the `viewContext` column, Remove the pluginHandle column
        if ($this->db->columnExists($table, 'viewContext')) {
            $dataSources = (new Query())
                ->select(['*'])
                ->from([$table])
                ->all();

            foreach ($dataSources as $dataSource) {

                switch ($dataSource['viewContext']) {
                    case 'global':
                    case '':
                        // Update our default to be labeled 'reports'
                        $this->update($table, ['viewContext' => 'reports'], ['id' => $dataSource['id']], [], false);
                        break;
                    case 'sprout-lists':
                        // Update Sprout Lists to use the 'segments' context
                        $this->update($table, ['viewContext' => 'segments'], ['id' => $dataSource['id']], [], false);
                        break;
                    default:
                        // No need to update
                        break;
                }
            }
            // @todo - remove the comment after we add minimum version required.
            #$this->dropColumn($table, 'pluginHandle');
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m190818_000001_update_dataSource_viewContext_column_values cannot be reverted.\n";
        return false;
    }
}
