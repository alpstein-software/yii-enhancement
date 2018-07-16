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
        'plugins/iCheck/all.css',
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

    /**
     * @param View $view
     * @return mixed
     */
    public static function register($view)
    {
        $extraJs = <<<EOL
$('input[type="checkbox"].minimal, input[type="radio"].minimal').iCheck({
  checkboxClass: 'icheckbox_minimal-blue', radioClass: 'iradio_minimal-blue'
});
$('input[type="checkbox"].minimal-red, input[type="radio"].minimal-red').iCheck({
  checkboxClass: 'icheckbox_minimal-red', radioClass: 'iradio_minimal-red'
});
$('input[type="checkbox"].minimal-green, input[type="radio"].minimal-green').iCheck({
  checkboxClass: 'icheckbox_minimal-green', radioClass: 'iradio_minimal-green'
});
$('input[type="checkbox"].flat, input[type="radio"].flat').iCheck({
  checkboxClass: 'icheckbox_flat-blue', radioClass: 'iradio_flat-blue'
})
$('input[type="checkbox"].flat-red, input[type="radio"].flat-red').iCheck({
  checkboxClass: 'icheckbox_flat-red', radioClass: 'iradio_flat-red'
})
$('input[type="checkbox"].flat-green, input[type="radio"].flat-green').iCheck({
  checkboxClass: 'icheckbox_flat-green', radioClass: 'iradio_flat-green'
})
EOL;
        $view->registerJs($extraJs, View::POS_READY, 'adminlte.extra');

        return parent::register($view);
    }
}
