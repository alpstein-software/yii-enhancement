<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link http://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */
namespace alpstein\assets;

use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class FontAwesomeAsset extends AssetBundle
{
    /**
     * To be published
     * @var string
     */
    public $sourcePath = '@alpstein/assets/fontawesome';

    /**
     * @var array
     */
    public $css = [
        'css/fontawesome-all.min.css'
    ];

    /**
     * @var array
     */
    public $js = [
//        'js/fontawesome-all.js'
    ];
}
