<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\yii\grid;

/**
 * Class DataColumn
 * @package alpstein\yii\grid
 */
class DataColumn extends \yii\grid\DataColumn
{
    /**
     * @var array
     */
    public $filterInputOptions = ['class' => 'form-control', 'id' => null, 'prompt' => '-- All --'];
}
