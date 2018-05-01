<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\adminlte\widgets;

use kolyunya\yii2\assets\MapInputAsset;
use Yii;

/**
 * Class MapInputWidget
 * @package alpstein\adminlte\widgets
 */
class MapInputWidget extends \kolyunya\yii2\widgets\MapInputWidget
{
    /**
     * @var number
     */
    public $longitude = 103.67379824926479;

    /**
     * @var number
     */
    public $latitude = 1.5602998580622973;

    /**
     * @var int
     */
    public $zoom = 18;

    /**
     * @var string
     */
    public $width = '100%';

    /**
     * @var string
     */
    public $height = '400px';

    /**
     * @var string
     */
    public $pattern = '%longitude%,%latitude%';

    /**
     * @var bool
     */
    public $animateMarker = false;

    /**
     * @return string
     */
    public function run()
    {
        Yii::setAlias('@kolyunya', '@vendor/kolyunya');
        MapInputAsset::$key = Yii::$app->params['map.api.key'];

        return $this->render(
            '@kolyunya/yii2-map-input-widget/sources//widgets/views/MapInputWidget',
            [
                'id' => $this->getId(),
                'model' => $this->model,
                'attribute' => $this->attribute,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'zoom' => $this->zoom,
                'width' => $this->width,
                'height' => $this->height,
                'pattern' => $this->pattern,
                'mapType' => $this->mapType,
                'animateMarker' => $this->animateMarker,
                'alignMapCenter' => $this->alignMapCenter,
                'enableSearchBar' => $this->enableSearchBar,
            ]
        );
    }
}
