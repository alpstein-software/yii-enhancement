<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\adminlte\assets;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * Class AdminLteAsset
 * @package alpstein\adminlte\assets
 */
class AdminLteAsset extends AssetBundle
{
    /**
     * To be published
     * @var string
     */
    public $sourcePath = __DIR__ . '/dist';

    /**
     * @var array
     */
    public $jsOptions = [
        'position' => View::POS_END,
    ];

    /**
     * @var array
     */
    public $css = [
        'ionicons/css/ionicons.min.css',
        'font-awesome/css/font-awesome.min.css',
        'css/AdminLTE.min.css',
        'css/skins/_all-skins.css',
        'plugins/iCheck/square/blue.css',
        'yii/extra.css',
        'yii/style.css',
    ];

    /**
     * @var array
     */
    public $js = [
        'plugins/iCheck/icheck.min.js',
        'js/adminlte.min.js',
    ];
}
