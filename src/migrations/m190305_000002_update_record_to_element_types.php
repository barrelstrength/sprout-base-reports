<?php /** @noinspection ClassConstantCanBeUsedInspection */

namespace barrelstrength\sproutbasereports\migrations;

use craft\db\Migration;
use Craft;
use craft\db\Query;
use barrelstrength\sproutbasereports\elements\Report;

/**
 * m190305_000002_update_record_to_element_types migration.
 */
class m190305_000002_update_record_to_element_types extends Migration
{
    /**
     * @return bool
     * @throws \yii\db\Exception
     */
    public function safeUp(): bool
    {
        $query = new Query();
        $db = Craft::$app->getDb();

        $reports = $query->select('*')
            ->from(['{{%sproutreports_reports}}'])
            ->all();

        if (!empty($reports)) {
            foreach ($reports as $report) {

                $elementExist = $query->select('id')
                    ->from('{{%elements}}')
                    ->where(['id' =>$report['id']])
                    ->one();

                if ($elementExist) continue;

                $db->createCommand()->delete('{{%sproutreports_reports}}',
                    ['id' => $report['id']])->execute();

                $reportElement = new Report();
                unset($report['id']);
                $reportElement->setAttributes($report, false);

                Craft::$app->getElements()->saveElement($reportElement, false);

            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m190305_000002_update_record_to_element_types cannot be reverted.\n";

        return false;
    }
}
