<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\adminlte\widgets;

use kartik\growl\Growl;
use yii\base\Widget;
use Yii;

/**
 * Class Notification
 * @package alpstein\adminlte\widgets
 */
class Notification extends Widget
{
    /**
     * @return array
     */
    protected function getTypes()
    {
        return [
            'error' => Growl::TYPE_DANGER,
            'danger' => Growl::TYPE_DANGER,
            'success' => Growl::TYPE_SUCCESS,
            'info' => Growl::TYPE_INFO,
            'warning' => Growl::TYPE_WARNING,
        ];
    }

    /**
     * @return array
     */
    protected function getIcons()
    {
        return [
            'error' => 'glyphicon glyphicon-remove-sign',
            'danger' => 'glyphicon glyphicon-remove-sign',
            'success' => 'glyphicon glyphicon-ok-sign',
            'info' => 'glyphicon glyphicon-info-sign',
            'warning' => 'glyphicon glyphicon-exclamation-sign',
        ];
    }

    /**
     * @return array
     */
    protected function getTitles()
    {
        return [
            'error' => 'Error !',
            'danger' => 'Error !',
            'success' => 'Success !',
            'info' => 'Info !',
            'warning' => 'Warning !',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $session = Yii::$app->session;
        $flashes = $session->getAllFlashes();

        $icons = $this->getIcons();
        $types = $this->getTypes();
        $titles = $this->getTitles();

        foreach ($flashes as $type => $flash) {
            if (!isset($icons[$type])) {
                continue;
            }

            if (!isset($types[$type])) {
                continue;
            }

            if (!isset($titles[$type])) {
                continue;
            }

            foreach ((array) $flash as $i => $message) {
                echo Growl::widget([
                    'type' => $types[$type],
                    'title' => $titles[$type],
                    'icon' => $icons[$type],
                    'body' => $message,
                    'showSeparator' => true,
                    'delay' => 200,
                    'pluginOptions' => [
                        'showProgressbar' => true,
                        'placement' => [
                            'from' => 'top',
                            'align' => 'right',
                        ]
                    ]
                ]);
            }

            $session->removeFlash($type);
        }
    }
}
