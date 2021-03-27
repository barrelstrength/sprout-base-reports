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
        // Updates foreign key if it does not exist. Try catch avoid errors if it exist
        if ($this->db->columnExists('{{%sproutreports_reports}}', 'dataSourceId')) {

            $this->cleanUpSproutDataSourceStuff();

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

    // This use case will be solved by the above workflow and is
    // here as a legacy fix for too-specific of a use case
    public function cleanUpSproutDataSourceStuff()
    {
        $dataSourcesMap = [
            'sproutreports.users' => 'barrelstrength\sproutbasereports\datasources\Users',
            'sproutreports.categories' => 'barrelstrength\sproutreportscategories\integrations\sproutreports\datasources\Categories',
            'sproutreportscommerce.orderhistory' => 'barrelstrength\sproutreportscommerce\integrations\sproutreports\datasources\CommerceOrderHistoryDataSource',
            'sproutreportscommerce.productrevenue' => 'barrelstrength\sproutreportscommerce\integrations\sproutreports\datasources\CommerceProductRevenueDataSource',
        ];

        foreach ($dataSourcesMap as $oldDataSource => $newDataSource) {
            // Try to find a new data srouce
            $newDataSourceId = (new Query())
                ->select('id')
                ->from('{{%sproutreports_datasources}}')
                ->where([
                    'type' => $newDataSource,
                ])
                ->scalar();

            if (!$newDataSourceId) {
                // Scenario 1: an old data source may exist in the sproutreports_reports table.dataSourceId column
                // If we don't find a new version of the datasource, insert one

                $updatedValues = [
                    'viewContext' => 'sprout-reports',
                    'type' => $newDataSource,
                    'allowNew' => 1,
                ];

                // If the plugin was installed on C3 and not upgraded
                // from C2, the pluginHandle column may be missing.
                // If so, make sure not to try to add something to it
                if ($this->db->columnExists('{{%sproutreports_datasources}}', 'pluginHandle')) {
                    $updatedValues['pluginHandle'] = 'sprout-reports';
                }

                $this->insert('{{%sproutreports_datasources}}', $updatedValues);

                $newDataSourceId = $this->db->getLastInsertID();
            }

            // Scenario 2: old data sources might exist in the sproutreports_datasources.type column
            // Check for a matching datasources using the old syntax
            $oldDataSourceIds = (new Query())
                ->select('id')
                ->from('{{%sproutreports_datasources}}')
                ->where([
                    'type' => $oldDataSource,
                ])
                ->all();

            // Delete any old Data Sources
            $this->delete('{{%sproutreports_datasources}}', [
                'type' => $oldDataSource,
            ]);

            if ($newDataSourceId) {
                // update reports table with this ID
                $this->update('{{%sproutreports_reports}}', [
                    'dataSourceId' => $newDataSourceId,
                ], ['dataSourceId' => $oldDataSource], [], false);

                if ($oldDataSourceIds) {
                    foreach ($oldDataSourceIds as $oldDataSourceId) {
                        // Update any reports with old dataSourceIds and make sure they are new
                        $this->update('{{%sproutreports_reports}}', [
                            'dataSourceId' => $newDataSourceId,
                        ], ['dataSourceId' => $oldDataSourceId], [], false);
                    }
                }
            } else {
                // remove all rows from reports table that match dataSourceId sproutreports.users
                $this->delete('{{%sproutreports_reports}}', [
                    'dataSourceId' => $oldDataSource,
                ]);
            }
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
