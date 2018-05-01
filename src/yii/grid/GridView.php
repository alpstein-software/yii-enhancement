<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\yii\grid;

use yii\helpers\Html;
use yii\widgets\LinkPager;

/**
 * Class GridView
 * @package alpstein\yii\grid
 */
class GridView extends \yii\grid\GridView
{
    /**
     * @var array default table options
     */
    public $tableOptions = ['class' => 'table table-condensed table-striped table-bordered'];
    /**
     * @var string the data column class
     */
    public $dataColumnClass = DataColumn::class;

    /**
     * @var LinkPager
     */
    public $pager = [
        'class' => 'yii\widgets\LinkPager',
        'options' => ['class' => 'pagination pagination-sm no-margin pull-right'],
    ];

    /**
     * Renders the pager.
     * @return string the rendering result
     */
    public function renderPager()
    {
        $pager = parent::renderPager();
        if (!empty($pager)) {
            return Html::tag('div', $pager, ['class' => 'box-footer clearfix']);
        }
        return '';
    }
}
