<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\yii\grid;

use Yii;
use yii\helpers\Html;

/**
 * Class ActionColumn
 * @package alpstein\yii\grid
 */
class ActionColumn extends \yii\grid\ActionColumn
{
    /**
     * @var string
     */
    public $template = '{view} {update} {delete}';

    /**
     * @var array
     */
    public $headerOptions = ['class' => 'action-column', 'style' => 'text-align: center; width: 90px'];

    /**
     * @var array
     */
    public $contentOptions = ['style' => 'text-align: center'];

    /**
     * @var array
     */
    public $buttonOptions = ['class' => 'grid-link'];

    /**
     * Initializes the default button rendering callbacks.
     */
    protected function initDefaultButtons()
    {
        $this->initDefaultButton('view', 'fa fa-eye');
        $this->initDefaultButton('update', 'fa fa-pencil');
        $this->initDefaultButton('delete', 'fa fa-trash', [
            'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
            'data-method' => 'post',
        ]);
    }

    /**
     * Initializes the default button rendering callback for single button.
     * @param string $name Button name as it's written in template
     * @param string $iconName The part of Bootstrap glyphicon class that makes it unique
     * @param array $additionalOptions Array of additional options
     * @since 2.0.11
     */
    protected function initDefaultButton($name, $iconName, $additionalOptions = [])
    {
        if (!isset($this->buttons[$name]) && strpos($this->template, '{' . $name . '}') !== false) {
            $this->buttons[$name] = function ($url, $model, $key) use ($name, $iconName, $additionalOptions) {
                $title = ucfirst($name);
                $options = array_merge([
                    'title' => $title,
                    'aria-label' => $title,
                    'data-pjax' => '0',
                ], $additionalOptions, $this->buttonOptions);
                $icon = Html::tag('i', '', ['class' => $iconName]);
                return Html::a($icon, $url, $options);
            };
        }
    }
}
