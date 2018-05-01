<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\mail;

use yii\di\Instance;
use yii\mail\BaseMailer;
use yii\mail\MessageInterface;
use Yii;

/**
 * Class Mailer
 * @package alpstein\mail
 */
class Mailer extends BaseMailer
{
    /**
     * @var string|bool
     */
    public $htmlLayout =  false;

    /**
     * @var string|bool
     */
    public $textLayout = false;

    /**
     * @var string
     */
    public $provider = 'mailgun';

    /**
     * @var array
     */
    public $providerConfig = [];

    /**
     * @var bool debug mode or not
     */
    public $debugMode = false;

    /**
     * @var string the debug mode delivery to
     */
    public $debugEmail = ['ryu@alpstein.my' => 'Alpstein Developers'];

    /**
     * the actual mailer
     * @var BaseMailer
     */
    private $__mailer;

    /**
     * @inheritdoc
     */
    public function compose($view = null, array $params = [])
    {
        $message = $this->createMessage();
        if ($view === null) {
            return $message;
        }

        $message->setView($view);
        $message->setParams($params);

        return $message;
    }

    /**
     * @param MessageInterface $message
     * @return bool
     */
    public function send($message)
    {
        if (YII_DEBUG || $this->debugMode) {
            $message->setTo($this->debugEmail);
        }

        return parent::send($message);
    }

    /**
     * @return BaseMailer
     */
    protected function getMailer()
    {
        if (isset($this->__mailer)) {
            return $this->__mailer;
        }

        $this->__mailer = $this->getProviderConfig();
        $this->__mailer = Instance::ensure($this->__mailer, BaseMailer::class);

        return $this->__mailer;
    }

    /**
     * @return array
     */
    protected function getProviderConfig()
    {
        if (isset($this->providerConfig[$this->provider])) {
            return $this->providerConfig[$this->provider];
        }

        return [];
    }

    /**
     * @return Message
     */
    protected function createMessage()
    {
        $messageConfig = $this->messageConfig;
        if (!array_key_exists('class', $messageConfig)) {
            $messageConfig['class'] = $this->getMailer()->messageClass;
        }

        $config = [
            'class' => 'alpstein\mail\Message',
            'message' => $messageConfig,
            'mailer' => $this,
        ];

        /** @var Message $message */
        $message = Yii::createObject($config);

        return $message;
    }

    /**
     * Sends the specified message.
     * This method should be implemented by child classes with the actual email sending logic.
     * @param MessageInterface $message the message to be sent
     * @return bool whether the message is sent successfully
     */
    protected function sendMessage($message)
    {
        return $this->getMailer()->sendMessage($message);
    }
}
