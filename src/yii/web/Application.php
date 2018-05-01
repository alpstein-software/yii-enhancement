<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\yii\web;

/**
 * Class Application
 * @package alpstein\yii\web
 * @property \alpstein\yii\i18n\Formatter $formatter
 * @property \alpstein\mail\Mailer $mailer
 * @property \alpstein\yii\caching\FileCache $cache
 * @property \alpstein\yii\base\Security $security
 * @property \alpstein\yii\queue\core\Queue queue
 * @property \alpstein\audit\Audit audit
 * @property \alpstein\services\Aws $aws
 * @property \alpstein\services\Gcp $gcp
 * @property \alpstein\services\Platform $platform
 */
class Application extends \yii\web\Application
{

}
