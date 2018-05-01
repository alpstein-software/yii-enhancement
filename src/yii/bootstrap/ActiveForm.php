<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\yii\bootstrap;

/**
 * Class ActiveForm
 * @package alpstein\yii\bootstrap
 */
class ActiveForm extends \yii\bootstrap\ActiveForm
{
    /**
     * @var array the input field config
     */
    public $fieldConfig = [
        'horizontalCssClasses' => [
            'label' => 'col-sm-2',
            'offset' => 'col-sm-offset-2',
            'wrapper' => 'col-sm-10',
            'error' => '',
            'hint' => '',
        ],
    ];
}
