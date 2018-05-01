<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\adminlte\widgets;


use kartik\select2\Select2;

/**
 * Class SelectDropdown
 * @package alpstein\adminlte\widgets
 */
class SelectDropdown extends Select2
{
    /**
     * @var string
     */
    public $theme = self::THEME_DEFAULT;

    /**
     * @var array
     */
    public $pluginOptions = ['allowClear' => true];

    /**
     * @var array
     */
    public $options = ['placeholder' => 'Select One'];
}