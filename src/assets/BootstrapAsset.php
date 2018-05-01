<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\assets;

use yii\web\AssetBundle;

/**
 * Class BootstrapAsset
 * @package alpstein\assets
 */
class BootstrapAsset extends AssetBundle
{
    /**
     * To be published
     * @var string
     */
    public $sourcePath = '@alpstein/assets/bootstrap';

    /**
     * @var array
     */
    public $css = [
        'css/bootstrap.min.css'
    ];

    /**
     * @var array
     */
    public $js = [
        'js/bootstrap.min.js'
    ];
}
