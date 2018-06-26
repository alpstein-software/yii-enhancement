<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\adminlte\widgets;

use kartik\file\FileInput;

/**
 * Class ImageFileInput
 * @package alpstein\adminlte\widgets
 */
class ImageFileInput extends FileInput
{
    /**
     * @var array
     */
    public $options = ['accept' => 'image/*'];

    /**
     * @var array
     */
    public $pluginOptions = [
        'allowedPreviewTypes' => ['image', 'video'],
        'allowedFileExtensions' => ['jpg', 'jpeg', 'png', 'gif'],
        'fileActionSettings' => [
            'showZoom' => false,
            'indicatorNew' => '',
        ],
        'dropZoneEnabled' => false,
        'showUpload' => false,
        'showClose' => false,
    ];
}
