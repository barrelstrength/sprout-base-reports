<?php
/**
 * @link      https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license   https://craftcms.github.io/license/
 */

namespace barrelstrength\sproutbasereports\widgets;

use barrelstrength\sproutbasereports\base\DataSource;
use barrelstrength\sproutbasereports\elements\Report;
use barrelstrength\sproutbasereports\SproutBaseReports;
use Craft;
use craft\base\Widget;
use craft\errors\MissingComponentException;
use craft\helpers\UrlHelper;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;
use function json_decode;

/**
 * RecentEntries represents a Recent Entries dashboard widget.
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since  3.0.0
 *
 * @property mixed  $bodyHtml
 * @property mixed  $settingsHtml
 * @property string $title
 */
class Visualization extends Widget
{
    /**
     * string The reportId of the report to be displayed
     */

    public $reportId = 0;

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
     * @return string|null
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
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
        if ($report) {
            $title = $report->name;
        } else {
            $title = Craft::t('sprout-base-reports', 'Sprout Report Chart');
        }

        return $title;
    }

    /**
     * @return false|string
     * @throws Exception
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws MissingComponentException
     */
    public function getBodyHtml()
    {
        $dataSource = false;

        $report = Craft::$app->elements->getElementById($this->reportId, Report::class);

        if ($report) {
            $dataSource = $report->getDataSource();
        }

        if ($report instanceof Report && $dataSource instanceof DataSource) {
            $dataSourceBaseUrl = Craft::$app->getSession()->get('sprout.reports.dataSourceBaseUrl');
            $reportIndexUrl = UrlHelper::cpUrl($dataSourceBaseUrl.'view/'.$report->id);

            $labels = $dataSource->getDefaultLabels($report);
            $values = $dataSource->getResults($report);

            if (empty($labels) && !empty($values)) {
                $firstItemInArray = reset($values);
                $labels = array_keys($firstItemInArray);
            }

            $settings = json_decode($report->settings, true);

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

        return Craft::$app->getView()->renderTemplate('sprout-base-reports/_components/widgets/Visualizations/body', [
            'title' => 'report title',
            'visualization' => $visualization,
            'reportIndexUrl' => $reportIndexUrl
        ]);
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

}
