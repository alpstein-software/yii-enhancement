<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\mail\mailgun;

use Mailgun\Messages\MessageBuilder;
use yii\base\InvalidArgumentException;
use yii\helpers\VarDumper;
use yii\mail\MessageInterface;

/**
 * Class MailGunMessage
 * @package alpstein\mail
 */
class Message extends \yii\swiftmailer\Message implements MessageInterface
{
    /**
     * @var MessageBuilder
     */
    private $__builder;

    private $__messageParams = [];

    /**
     * @return array
     */
    public function getMessageParams()
    {
        $params = $this->__messageParams;

        if (($from = $this->generateFromData()) !== false) {
            $params['from'] = $from;
        }

        if (($to = $this->generateToData()) !== false) {
            $params['to'] = $to;
        }

        if (($cc = $this->generateCcData()) !== false) {
            $params['cc'] = $cc;
        }

        if (($bcc = $this->generateBccData()) !== false) {
            $params['bcc'] = $bcc;
        }

        return $params;
    }

    /**
     * @return string
     */
    protected function generateFromData()
    {
        $value = $this->getFrom();
        return $this->generateRecipientData($value);
    }

    /**
     * @return string
     */
    protected function generateToData()
    {
        $value = $this->getTo();
        return $this->generateRecipientData($value);
    }

    /**
     * @return string
     */
    protected function generateCcData()
    {
        $value = $this->getCc();
        return $this->generateRecipientData($value);
    }

    /**
     * @return string
     */
    protected function generateBccData()
    {
        $value = $this->getBcc();
        return $this->generateRecipientData($value);
    }

    /**
     * @param string|array $value
     * @return string
     */
    protected function generateRecipientData($value)
    {
        $data = [];
        if (is_array($value)) {
            foreach ($value as $email => $name) {
                if (empty($name)) {
                    $data[] = $email;
                } else {
                    $data[] = sprintf('"%s" <%s>', $name, $email);
                }
            }
        } elseif (!empty($value)) {
            $data[] = $value;
        }
        return empty($data) ? false : implode(', ', $data);
    }

    /**
     * @return MessageBuilder Mailgun message instance.
     */
    public function getBuilder()
    {
        if (!isset($this->__builder)) {
            $this->__builder = new MessageBuilder();
        }
        return $this->__builder;
    }

    /**
     * Returns the message subject.
     * @return string the message subject
     */
    public function getSubject()
    {
        return isset($this->__messageParams['subject']) ? $this->__messageParams['subject'] : '';
    }

    /**
     * Sets the message subject.
     * @param string $subject message subject
     * @return $this self reference.
     */
    public function setSubject($subject)
    {
        $this->__messageParams['subject'] = $subject;
        return $this;
    }

    /**
     * Sets message plain text content.
     * @param string $text message plain text content.
     * @return $this self reference.
     */
    public function setTextBody($text)
    {
        $this->__messageParams['text'] = $text;
        return $this;
    }

    /**
     * Sets message HTML content.
     * @param string $html message HTML content.
     * @return $this self reference.
     */
    public function setHtmlBody($html)
    {
        $this->__messageParams['html'] = $html;
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
        $attachment = $this->resolveFilePath($fileName, isset($options['fileName']) ? $options['fileName'] : null);
        return $this->addAttachment($attachment);
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
        $attachment = $this->resolveFileContent($content, isset($options['fileName']) ? $options['fileName'] : null);
        return $this->addAttachment($attachment);
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
        $inline = $this->resolveFilePath($fileName, isset($options['fileName']) ? $options['fileName'] : null);
        return $this->addInline($inline);
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
        $inline = $this->resolveFileContent($content, isset($options['fileName']) ? $options['fileName'] : null);
        return $this->addInline($inline);
    }

    /**
     * Returns string representation of this message.
     * @return string the string representation of this message.
     */
    public function toString()
    {
        return VarDumper::dumpAsString($this->__messageParams);
    }

    /**
     * @param array $attachment
     * @return $this
     */
    protected function addAttachment($attachment)
    {
        return $this->addFile('attachment', $attachment);
    }

    /**
     * @param array $inline
     * @return $this
     */
    protected function addInline($inline)
    {
        return $this->addFile('inline', $inline);
    }

    /**
     * @param string $path
     * @param string $name
     * @return array
     */
    protected function resolveFilePath($path, $name)
    {
        $file = ['filename' => $name];

        if (is_file($path)) {
            $file['filePath'] = $path;
        } else {
            throw new InvalidArgumentException('Invalid $path: ' . $path);
        }

        return $file;
    }

    /**
     * @param string $content
     * @param string $name
     * @return array
     */
    protected function resolveFileContent($content, $name)
    {
        $file = ['filename' => $name];

        if (is_string($content)) {
            $file['fileContent'] = $content;
        } else {
            throw new InvalidArgumentException('Invalid $content !!');
        }

        return $file;
    }

    /**
     * @param string $type attachment or inline
     * @param array $attachment
     * @return $this
     */
    protected function addFile($type, $attachment)
    {
        if (isset($this->__messageParams[$type]) && is_array($this->__messageParams[$type])) {
            $this->__messageParams[$type][] = $attachment;
        } else {
            $this->__messageParams[$type] = [$attachment];
        }
        return $this;
    }
}
