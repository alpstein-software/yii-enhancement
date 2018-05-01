<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\adminlte\widgets;

/**
 * Class DatePicker
 * @package alpstein\adminlte\widgets
 */
class DatePicker extends \dosamigos\datepicker\DatePicker
{
    public $clientOptions = ['format' => 'yyyy-mm-dd', 'orientation' => 'bottom', 'autoclose' => true];
}
