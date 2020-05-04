<?php

namespace barrelstrength\sproutbasereports\visualizations;

use barrelstrength\sproutbasereports\base\Visualization;
use Craft;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;

class BarChartVisualization extends Visualization
{
    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('sprout-base-reports', 'Bar Chart');
    }

    /**
     * @param array $settings
     *
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public function getSettingsHtml(array $settings): string
    {
        return Craft::$app->getView()->renderTemplate('sprout-base-reports/_components/visualizations/BarChart/settings', [
            'settings' => $settings
        ]);
    }

    /**
     * @param array $options
     *
     * @return string
     * @throws Exception
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getVisualizationHtml(array $options = []): string
    {
        return Craft::$app->getView()->renderTemplate('sprout-base-reports/_components/visualizations/BarChart/visualization', [
            'visualization' => $this,
            'options' => $options,
        ]);
    }
}