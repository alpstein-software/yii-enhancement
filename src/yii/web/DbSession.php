<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\yii\web;

/**
 * Class DbSession
 * @package alpstein\yii\web
 */
class DbSession extends \yii\web\DbSession
{
    public $sessionTable = '{{%http_session}}';
}
