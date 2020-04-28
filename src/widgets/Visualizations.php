<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace barrelstrength\sproutbasereports\widgets;

use Craft;
use craft\base\Widget;
use craft\helpers\Json;
use craft\helpers\UrlHelper;
use barrelstrength\sproutbasereports\SproutBaseReports;
use barrelstrength\sproutbasereports\elements\Report;
use barrelstrength\sproutbasereports\base\DataSource;

/**
 * RecentEntries represents a Recent Entries dashboard widget.
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 3.0.0
 */
class Visualizations extends Widget
{
    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('sprout-base-reports', 'Sprout Report Chart');
    }

    /**
     * @inheritdoc
     */
    public static function icon()
    {
        return Craft::getAlias('@app/icons/clock.svg');
    }

    /**
     * string The reportId of the report to be displayed
     */

    public $reportId = 0;


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }

    /**
     * @inheritdoc
     */
    protected function defineRules(): array
    {
        $rules = parent::defineRules();
        $rules[] = [['reportId'], 'number', 'integerOnly' => true];
        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
      return Craft::$app->getView()->renderTemplate('sprout-base-reports/_components/widgets/Visualizations/settings.twig',
      [
          'widget' => $this,
          'reports' => SproutBaseReports::$app->reports->getAllReports(),
          'reportId' => $this->reportId
      ]);
    }

    /**
     * @inheritdoc
     */
    public function getTitle(): string
    {
        $report = Craft::$app->elements->getElementById($this->reportId, Report::class);
        if ($report){
          $title = $report->name;
        } else {
          $title = Craft::t('sprout-base-reports', 'Sprout Report Chart');
        }

        return $title;
    }

    /**
     * @inheritdoc
     */
    public function getBodyHtml()
    {

        $report = false;
        $dataSource = false;
        $visualization = false;
        $reportIndexUrl = '';

        $report = Craft::$app->elements->getElementById($this->reportId, Report::class);

        if ($report) {
          $dataSource = $report->getDataSource();
        }

        if ($report && $dataSource)
        {
          $dataSourceBaseUrl = Craft::$app->getSession()->get('sprout.reports.dataSourceBaseUrl');
          $reportIndexUrl = UrlHelper::cpUrl($dataSourceBaseUrl.'view/'.$report->id);

          $labels = $dataSource->getDefaultLabels($report);
          $values = $dataSource->getResults($report);

          if (empty($labels) && !empty($values)) {
              $firstItemInArray = reset($values);
              $labels = array_keys($firstItemInArray);
          }

          $settings = \json_decode($report->settings, true);

          if (array_key_exists('visualization', $settings)) {
            $visualization = new $settings['visualization']['type'];
            $visualization->setSettings($settings['visualization']);
            $visualization->setLabels($labels);
            $visualization->setValues($values);
            $visualization->setTitle($report->name);
          } else {
            $visualization = false;
          }
        } else {
          $visualization = false;
          $reportIndexUrl = '';
        }

        $view = Craft::$app->getView();

        return $view->renderTemplate('sprout-base-reports/_components/widgets/Visualizations/body',
          [
            'title' => 'report title',
            'visualization' => $visualization,
            'reportIndexUrl' => $reportIndexUrl
          ]);
    }

}
