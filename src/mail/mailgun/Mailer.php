<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\mail\mailgun;

use Mailgun\Mailgun;
use yii\base\InvalidConfigException;
use yii\mail\BaseMailer;
use Yii;

/**
 * Class MailGunMailer
 * @package alpstein\mail
 */
class Mailer extends BaseMailer
{
    /**
     * @var string
     */
    public $key;

    /**
     * @var string
     */
    public $domain;

    /**
     * @var string message default class name.
     */
    public $messageClass = 'alpstein\mail\mailgun\Message';

    /**
     * @var
     */
    private $__mailer;

    /**
     * initialize
     */
    public function init()
    {
        parent::init();

        if (!isset($this->key)) {
            throw new InvalidConfigException('key must be set !');
        }

        if (!isset($this->domain)) {
            throw new InvalidConfigException('domain must be set !');
        }
    }

    /**
     * @return Mailgun Mailgun mailer instance.
     */
    public function getMailer()
    {
        if (!isset($this->__mailer)) {
            $this->__mailer = Mailgun::create($this->key);
        }
        return $this->__mailer;
    }

    /**
     * Sends the specified message.
     * This method should be implemented by child classes with the actual email sending logic.
     * @param Message $message the message to be sent
     * @return bool whether the message is sent successfully
     */
    protected function sendMessage($message)
    {
        Yii::info('Sending email', __METHOD__);

        $params = $message->getMessageParams();
        var_dump($params);

        $response = $this->getMailer()->messages()->send($this->domain, $params);

        Yii::info('Response : ' . print_r($response, true), __METHOD__);
        return true;
    }
}