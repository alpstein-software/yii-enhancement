<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\mail;

use yii\base\InvalidConfigException;
use yii\base\ViewContextInterface;
use yii\di\Instance;
use yii\helpers\Html;
use yii\mail\BaseMessage;
use yii\mail\MailerInterface;
use Yii;

/**
 * Class Message
 * @property BaseMessage message
 * @package alpstein\mail
 */
class Message extends BaseMessage implements ViewContextInterface
{
    /**
     * @var Mailer
     */
    public $mailer;

    /**
     * @var string|null the debug message
     */
    private $__debugMessage;

    /**
     * @var BaseMessage
     */
    private $__message;

    /**
     * @var string
     */
    private $__view;

    /**
     * @var array
     */
    private $__params = [];

    /**
     * forward to call function in message object if not found in this object
     * @param string $name
     * @param array $params
     * @return mixed
     */
    public function __call($name, $params)
    {
        if (!method_exists($this, $name)) {
            return call_user_func_array([$this->message, $name], $params);
        }

        return parent::__call($name, $params);
    }

    /**
     * ensure instance
     */
    public function init()
    {
        parent::init();
        $this->message = Instance::ensure($this->message, BaseMessage::class);
    }

    /**
     * @param Mailer|MailerInterface|null $mailer
     * @return bool
     */
    public function send(MailerInterface $mailer = null)
    {
        if (YII_DEBUG || $this->mailer->debugMode) {
            $this->loadDebugMessage();
            $this->setSubject('[DEBUG] ' . $this->getSubject());
        }

        //-- override sender and reply to
//        if (($config = Yii::$app->config) instanceof SystemConfig) {
//            if (($sender = $config->getMailerSender()) !== false) {
//                $this->setFrom($sender);
//            }
//            if (($replyTo = $config->getMailerReplyTo()) !== false) {
//                $this->setReplyTo($replyTo);
//            }
//        }

        $this->prepareContent();

        return parent::send($mailer);
    }

    /**
     * @throws InvalidConfigException
     * @return $this
     */
    protected function prepareContent()
    {
        if ($this->__view === null) {
            throw new InvalidConfigException('$view must be defined!');
        }

        $view = $this->__view;
        $params = $this->__params;
        if (!array_key_exists('message', $params)) {
            $params['message'] = $this;
        }

        //-- read email binding data
        $params['debugMessage'] = $this->getDebugMessage();

        /** @var Mailer $mailer */
        $mailer = $this->mailer;
        if (is_array($view)) {
            if (isset($view['html'])) {
                $html = $mailer->render($view['html'], $params, $mailer->htmlLayout);
            }
            if (isset($view['text'])) {
                $text = $mailer->render($view['text'], $params, $mailer->textLayout);
            }
        } else {
            $html = $mailer->render($view, $params, $mailer->htmlLayout);
        }

        if (isset($html)) {
            $this->setHtmlBody($html);
        }

        if (isset($text)) {
            $this->setTextBody($text);
        } elseif (isset($html)) {
            if (preg_match('~<body[^>]*>(.*?)</body>~is', $html, $match)) {
                $html = $match[1];
            }
            // remove style and script
            $html = preg_replace('~<((style|script))[^>]*>(.*?)</\1>~is', '', $html);
            // strip all HTML tags and decoded HTML entities
            $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, Yii::$app ? Yii::$app->charset : 'UTF-8');
            // improve whitespace
            $text = preg_replace("~^[ \t]+~m", '', trim($text));
            $text = preg_replace('~\R\R+~mu', "\n\n", $text);
            $this->setTextBody($text);
        }

        return $this;
    }

    /**
     * Add debug message to the mail
     */
    public function loadDebugMessage()
    {
        if (!method_exists($this, 'loadDebugMessage')) {
            $this->__debugMessage = call_user_func_array([$this->message, 'loadDebugMessage'], []);
        } else {
            $messages = [];
            $messages[] = Html::tag('b', 'Debug Message: ');

            $to = $this->getTo();
            if (!empty($to)) {
                $messages[] = Html::tag('b', 'Original To: ') . implode(', ', array_keys($to));
            }

            $cc = $this->getCc();
            if (!empty($cc)) {
                $messages[] = Html::tag('b', 'Original CC: ') . implode(', ', array_keys($cc));
            }

            $bcc = $this->getBcc();
            if (!empty($bcc)) {
                $messages[] = Html::tag('b', 'Original BCC: ') . implode(', ', array_keys($bcc));
            }

            $this->__debugMessage = implode('<br />', $messages);
        }
    }

    /**
     * @return string
     */
    public function getDebugMessage()
    {
        if (empty($this->__debugMessage)) {
            return '';
        }

        $text = Html::tag('span', '&nbsp;<br />');
        $text .= $this->__debugMessage;
        $text .= Html::tag('span', '<br />&nbsp;');

        return $text;
    }

    /**
     * @return BaseMessage
     */
    protected function getMessage()
    {
        return $this->__message;
    }

    /**
     * @param BaseMessage $value
     */
    protected function setMessage($value)
    {
        $this->__message = $value;
    }

    /**
     * @return string the view path that may be prefixed to a relative view name.
     */
    public function getViewPath()
    {
        // TODO: Implement getViewPath() method.
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setView($value)
    {
        $this->__view = $value;
        return $this;
    }

    /**
     * @param array $value
     * @return $this
     */
    public function setParams($value)
    {
        $this->__params = (array) $value;
        return $this;
    }


    /**
     * Returns the character set of this message.
     * @return string the character set of this message.
     */
    public function getCharset()
    {
        return $this->getMessage()->getCharset();
    }

    /**
     * Sets the character set of this message.
     * @param string $charset character set name.
     * @return $this self reference.
     */
    public function setCharset($charset)
    {
        $this->getMessage()->setCharset($charset);
        return $this;
    }

    /**
     * Returns the message sender.
     * @return string|array the sender
     */
    public function getFrom()
    {
        return $this->getMessage()->getFrom();
    }

    /**
     * Sets the message sender.
     * @param string|array $from sender email address.
     * You may pass an array of addresses if this message is from multiple people.
     * You may also specify sender name in addition to email address using format:
     * `[email => name]`.
     * @return $this self reference.
     */
    public function setFrom($from)
    {
        $this->getMessage()->setFrom($from);
        return $this;
    }

    /**
     * Returns the message recipient(s).
     * @return string|array the message recipients
     */
    public function getTo()
    {
        return $this->getMessage()->getTo();
    }

    /**
     * Sets the message recipient(s).
     * @param string|array $to receiver email address.
     * You may pass an array of addresses if multiple recipients should receive this message.
     * You may also specify receiver name in addition to email address using format:
     * `[email => name]`.
     * @return $this self reference.
     */
    public function setTo($to)
    {
        $this->getMessage()->setTo($to);
        return $this;
    }

    /**
     * Returns the reply-to address of this message.
     * @return string|array the reply-to address of this message.
     */
    public function getReplyTo()
    {
        return $this->getMessage()->getReplyTo();
    }

    /**
     * Sets the reply-to address of this message.
     * @param string|array $replyTo the reply-to address.
     * You may pass an array of addresses if this message should be replied to multiple people.
     * You may also specify reply-to name in addition to email address using format:
     * `[email => name]`.
     * @return $this self reference.
     */
    public function setReplyTo($replyTo)
    {
        $this->getMessage()->setReplyTo($replyTo);
        return $this;
    }

    /**
     * Returns the Cc (additional copy receiver) addresses of this message.
     * @return string|array the Cc (additional copy receiver) addresses of this message.
     */
    public function getCc()
    {
        return $this->getMessage()->getCc();
    }

    /**
     * Sets the Cc (additional copy receiver) addresses of this message.
     * @param string|array $cc copy receiver email address.
     * You may pass an array of addresses if multiple recipients should receive this message.
     * You may also specify receiver name in addition to email address using format:
     * `[email => name]`.
     * @return $this self reference.
     */
    public function setCc($cc)
    {
        $this->getMessage()->setCc($cc);
        return $this;
    }

    /**
     * Returns the Bcc (hidden copy receiver) addresses of this message.
     * @return string|array the Bcc (hidden copy receiver) addresses of this message.
     */
    public function getBcc()
    {
        return $this->getMessage()->getBcc();
    }

    /**
     * Sets the Bcc (hidden copy receiver) addresses of this message.
     * @param string|array $bcc hidden copy receiver email address.
     * You may pass an array of addresses if multiple recipients should receive this message.
     * You may also specify receiver name in addition to email address using format:
     * `[email => name]`.
     * @return $this self reference.
     */
    public function setBcc($bcc)
    {
        $this->getMessage()->setBcc($bcc);
        return $this;
    }

    /**
     * Returns the message subject.
     * @return string the message subject
     */
    public function getSubject()
    {
        return $this->getMessage()->getSubject();
    }

    /**
     * Sets the message subject.
     * @param string $subject message subject
     * @return $this self reference.
     */
    public function setSubject($subject)
    {
        $this->getMessage()->setSubject($subject);
        return $this;
    }

    /**
     * Sets message plain text content.
     * @param string $text message plain text content.
     * @return $this self reference.
     */
    public function setTextBody($text)
    {
        $this->getMessage()->setTextBody($text);
        return $this;
    }

    /**
     * Sets message HTML content.
     * @param string $html message HTML content.
     * @return $this self reference.
     */
    public function setHtmlBody($html)
    {
        $this->getMessage()->setHtmlBody($html);
        return $this;
    }

    /**
     * Attaches existing file to the email message.
     * @param string $fileName full file name
     * @param array $options options for embed file. Valid options are:
     *
     * - fileName: name, which should be used to attach file.
     * - contentType: attached file MIME type.
     *
     * @return $this self reference.
     */
    public function attach($fileName, array $options = [])
    {
        $this->getMessage()->attach($fileName, $options);
        return $this;
    }

    /**
     * Attach specified content as file for the email message.
     * @param string $content attachment file content.
     * @param array $options options for embed file. Valid options are:
     *
     * - fileName: name, which should be used to attach file.
     * - contentType: attached file MIME type.
     *
     * @return $this self reference.
     */
    public function attachContent($content, array $options = [])
    {
        $this->getMessage()->attachContent($content, $options);
        return $this;
    }

    /**
     * Attach a file and return it's CID source.
     * This method should be used when embedding images or other data in a message.
     * @param string $fileName file name.
     * @param array $options options for embed file. Valid options are:
     *
     * - fileName: name, which should be used to attach file.
     * - contentType: attached file MIME type.
     *
     * @return string attachment CID.
     */
    public function embed($fileName, array $options = [])
    {
        $this->getMessage()->embed($fileName, $options);
        return $this;
    }

    /**
     * Attach a content as file and return it's CID source.
     * This method should be used when embedding images or other data in a message.
     * @param string $content attachment file content.
     * @param array $options options for embed file. Valid options are:
     *
     * - fileName: name, which should be used to attach file.
     * - contentType: attached file MIME type.
     *
     * @return string attachment CID.
     */
    public function embedContent($content, array $options = [])
    {
        $this->getMessage()->embedContent($content, $options);
        return $this;
    }

    /**
     * Returns string representation of this message.
     * @return string the string representation of this message.
     */
    public function toString()
    {
        return $this->getMessage()->toString();
    }
}