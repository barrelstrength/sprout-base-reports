<?php /** @noinspection ClassConstantCanBeUsedInspection */

namespace barrelstrength\sproutbasereports\migrations;

use craft\db\Migration;
use Craft;
use craft\db\Query;
use barrelstrength\sproutbasereports\elements\Report;

/**
 * m190628_000000_fix_data_sources migration.
 */
class m190628_000000_fix_data_sources extends Migration
{
    private $reportTable = '{{%sproutreports_reports}}';
    private $reportGroupTable = '{{%sproutreports_reportgroups}}';
    private $dataSourcesTable = '{{%sproutreports_datasources}}';

    /**
     * @return bool
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function safeUp(): bool
    {
        $query = new Query();
        $db = Craft::$app->getDb();

        // Get all reports from the report table
        $dataSources = $query->select('*')
            ->from([$this->dataSourcesTable])
            ->all();

        if (empty($dataSources)) {
            return true;
        }

        $hashMap = [];
        foreach ($dataSources as $dataSource) {
            if (isset($hashMap[$dataSource['type']])){
                // We have a duplicate
                $this->update($this->reportTable, [
                    'type' => $hashMap[$dataSource]['type']['id']
                ], ['dataSourceId' => $dataSource['id']], [], false);
                // let's remove the duplicate data source
                $db->createCommand()->delete($this->dataSourcesTable,
                    ['id' => $dataSource['id']])->execute();
            }else{
                // New datasource
                $hashMap[$dataSource['type']] = $dataSource;
                if ($dataSource['type'] == 'barrelstrength\sproutforms\integrations\sproutreports\datasources\EntriesDataSource' ||
                    $dataSource['type'] == 'barrelstrength\sproutforms\integrations\sproutreports\datasources\SubmissionLogDataSource'){
                    $this->update($this->dataSourcesTable, [
                        'pluginHandle' => 'sprout-forms'
                    ], ['id' => $dataSource['id']], [], false);
                }else{
                    // Let's default all to sprout reports
                    $this->update($this->dataSourcesTable, [
                        'pluginHandle' => 'sprout-reports'
                    ], ['id' => $dataSource['id']], [], false);
                }
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m190628_000000_fix_data_sources cannot be reverted.\n";

        return false;
    }
}
