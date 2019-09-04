<?php

namespace barrelstrength\sproutbasereports\services;

use barrelstrength\sproutbasereports\base\DataSource;
use barrelstrength\sproutbasereports\models\ReportGroup;
use craft\db\Query;
use InvalidArgumentException;
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
            throw new InvalidArgumentException('No report group found.');
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
     * @param string $viewContext
     *
     * @return array
     */
    public function getReportGroups($viewContext = DataSource::DEFAULT_VIEW_CONTEXT): array
    {
        $query = (new Query())
            ->select('*')
            ->from('{{%sproutreports_reportgroups}}')
            ->where([
                'viewContext' => $viewContext
            ])
            ->indexBy('id');

        $results = $query->all();

        $reportGroups = [];
        foreach($results as $reportGroup) {
            $reportGroupModel = new ReportGroup();
            $reportGroupModel->id = $reportGroup['id'];
            $reportGroupModel->name = $reportGroup['name'];
            $reportGroupModel->viewContext = $reportGroup['viewContext'];
            $reportGroups[$reportGroup['id']] = $reportGroupModel;
        }

        return $reportGroups;
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
            throw new NotFoundHttpException('Report Group not found.');
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
                throw new InvalidArgumentException('No field group exists with the ID: '.$group->id);
            }
        } else {
            $groupRecord = new ReportGroupRecord();
        }

        return $groupRecord;
    }
}
