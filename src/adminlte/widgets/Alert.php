<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\adminlte\widgets;

use yii\bootstrap\Widget;
use Yii;
use yii\helpers\Html;

/**
 * Class Alert
 * @package alpstein\adminlte\widgets
 */
class Alert extends Widget
{
    /**
     * @var array the alert types configuration for the flash messages.
     * This array is setup as $key => $value, where:
     * - key: the name of the session flash variable
     * - value: the bootstrap alert type (i.e. danger, success, info, warning)
     */
    public $alertTypes = [
        'error'   => 'alert-danger',
        'danger'  => 'alert-danger',
        'success' => 'alert-success',
        'info'    => 'alert-info',
        'warning' => 'alert-warning'
    ];

    /**
     * @var array
     */
    public $alertTitles = [
        'error'   => '<i class="icon fa fa-ban"></i> Error !',
        'danger'  => '<i class="icon fa fa-ban"></i> Error !',
        'success' => '<i class="icon fa fa-check"></i> Success !',
        'info'    => '<i class="icon fa fa-info"></i> Info !',
        'warning' => '<i class="icon fa fa-exclamation-triangle"></i> Warning !',
    ];

    /**
     * @var array the options for rendering the close button tag.
     * Array will be passed to [[\yii\bootstrap\Alert::closeButton]].
     */
    public $closeButton = [];


    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $session = Yii::$app->session;
        $flashes = $session->getAllFlashes();
        $appendClass = isset($this->options['class']) ? ' ' . $this->options['class'] : '';

        foreach ($flashes as $type => $flash) {
            if (!isset($this->alertTypes[$type])) {
                continue;
            }
            if (!isset($this->alertTitles[$type])) {
                continue;
            }

            foreach ((array) $flash as $i => $message) {
                $message = Html::tag('h4', $this->alertTitles[$type]) . $message;

                echo \yii\bootstrap\Alert::widget([
                    'body' => $message,
                    'closeButton' => $this->closeButton,
                    'options' => array_merge($this->options, [
                        'id' => $this->getId() . '-' . $type . '-' . $i,
                        'class' => $this->alertTypes[$type] . $appendClass,
                    ]),
                ]);
            }

            $session->removeFlash($type);
        }
    }
}