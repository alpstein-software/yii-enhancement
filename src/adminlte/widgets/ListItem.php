<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\adminlte\widgets;

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\DetailView;

/**
 * Class ListItem
 * @package alpstein\adminlte\widgets
 */
class ListItem extends DetailView
{
    /**
     * @var array
     */
    public $options = ['tag' => 'ul', 'class' => 'list-group list-group-unbordered'];

    /**
     * @var string
     */
    public $template = '<li{captionOptions}><b>{label}</b><br /><small{contentOptions}>{value}</small></li>';

    /**
     * Renders a single attribute.
     * @param array $attribute the specification of the attribute to be rendered.
     * @param int $index the zero-based index of the attribute in the [[attributes]] array
     * @return string the rendering result
     */
    protected function renderAttribute($attribute, $index)
    {
        if (empty($attribute['value'])) {
            return null;
        }

        if (is_string($this->template)) {
            $captionOptions = ArrayHelper::getValue($attribute, 'captionOptions', []);
            $contentOptions = ArrayHelper::getValue($attribute, 'contentOptions', []);

            Html::addCssClass($captionOptions, 'list-group-item');
            Html::addCssClass($contentOptions, ['text-muted']);

            $captionOptions = Html::renderTagAttributes($captionOptions);
            $contentOptions = Html::renderTagAttributes($contentOptions);

            return strtr($this->template, [
                '{label}' => $attribute['label'],
                '{value}' => $this->formatter->format($attribute['value'], $attribute['format']),
                '{captionOptions}' => $captionOptions,
                '{contentOptions}' => $contentOptions,
            ]);
        }

        return call_user_func($this->template, $attribute, $index, $this);
    }
}
