<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\yii\web;

use yii\web\UploadedFile;

/**
 * Class UploadedBase64
 * @package alpstein\yii\web
 */
class UploadedBase64 extends UploadedFile
{
    /**
     * @param string $file
     * @param bool $deleteTempFile
     * @return bool|void
     */
    public function saveAs($file, $deleteTempFile = true)
    {
        return copy($this->tempName, $file);
    }
}