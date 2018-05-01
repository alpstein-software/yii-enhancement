<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\yii\bootstrap;

/**
 * Class ButtonDropdown
 * @package alpstein\yii\bootstrap
 */
class ButtonDropdown extends \yii\bootstrap\ButtonDropdown
{
    /**
     * @var string
     */
    public $label = 'Action';

    /**
     * @var array
     */
    public $options = ['class' => 'btn-primary btn-sm'];
}
