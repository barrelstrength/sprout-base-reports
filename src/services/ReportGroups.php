<?php

namespace barrelstrength\sproutbasereports\services;

use yii\base\Component;
use barrelstrength\sproutbasereports\models\ReportGroup as ReportGroupModel;
use barrelstrength\sproutbasereports\records\ReportGroup as ReportGroupRecord;
use Craft;
use yii\web\NotFoundHttpException;

/**
 * Class ReportGroups
 *
 * @package barrelstrength\sproutreports\services
 *
 * @property array|\yii\db\ActiveRecord[] $allReportGroups
 */
class ReportGroups extends Component
{
    /**
     * @param ReportGroupModel $group
     *
     * @return bool
     */
    public function saveGroup(ReportGroupModel $group): bool
    {
        $groupRecord = $this->getGroupRecord($group);

        if (!$groupRecord) {
            throw new \InvalidArgumentException(Craft::t('sprout-import', 'No report group found.'));
        }

        $groupRecord->name = $group->name;

        if ($groupRecord->validate()) {
            $groupRecord->save(false);

            // Now that we have an ID, save it on the model & models
            if (!$group->id) {
                $group->id = $groupRecord->id;
            }

            return true;
        }

        $group->addErrors($groupRecord->getErrors());
        return false;
    }

    /**
     * @param $name
     *
     * @return ReportGroupModel|bool
     * @throws \Exception
     */
    public function createGroupByName($name)
    {
        $group = new ReportGroupModel();
        $group->name = $name;

        if ($this->saveGroup($group)) {
            return $group;
        }

        return false;
    }


    /**
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getAllReportGroups(): array
    {
        return ReportGroupRecord::find()->indexBy('id')->all();
    }

    /**
     * @param $id
     *
     * @return bool
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function deleteGroup($id): bool
    {
        $reportGroupRecord = ReportGroupRecord::findOne($id);

        if (!$reportGroupRecord) {
            throw new NotFoundHttpException(Craft::t('sprout-base-reports', 'Report Group not found.'));
        }

        return (bool)$reportGroupRecord->delete();
    }

    /**
     * @param ReportGroupModel $group
     *
     * @return ReportGroupRecord|null
     */
    private function getGroupRecord(ReportGroupModel $group)
    {
        if ($group->id) {
            $groupRecord = ReportGroupRecord::findOne($group->id);

            if (!$groupRecord) {
                throw new \InvalidArgumentException(Craft::t('sprout-import', 'No field group exists with the ID “{id}”', ['id' => $group->id]));
            }
        } else {
            $groupRecord = new ReportGroupRecord();
        }

        return $groupRecord;
    }
}
