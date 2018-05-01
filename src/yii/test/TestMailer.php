<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\yii\test;

use Yii;

/**
 * Class TestMailer
 * @package alpstein\yii\test
 */
class TestMailer extends \Codeception\Lib\Connector\Yii2\TestMailer
{
    /**
     * Creates a new message instance.
     * The newly created instance will be initialized with the configuration specified by [[messageConfig]].
     * If the configuration does not specify a 'class', the [[messageClass]] will be used as the class
     * of the new message instance.
     * @return object|\yii\mail\MessageInterface|\yii\swiftmailer\Message message instance.
     * @throws \yii\base\InvalidConfigException
     */
    public function createMessage()
    {
        $config = $this->messageConfig;
        if (!array_key_exists('class', $config)) {
            $config['class'] = $this->messageClass;
        }
        $config['mailer'] = $this;
        return Yii::createObject($config);
    }
}
