<?php

/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */
namespace alpstein\yii\queue\sqs;

use Aws\Sqs\SqsClient;
use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;
use yii\queue\cli\LoopInterface;
use yii\queue\cli\Queue as CliQueue;
use Yii;

/**
 * Class Queue
 */
class Queue extends CliQueue
{
    /**
     * The SQS url.
     * @var string
     */
    public $url;

    /**
     * @var string command class name
     */
    public $commandClass = Command::class;
    /**
     * @var SqsClient
     */
    private $_client;

    /**
     * make sure url is set
     */
    public function init()
    {
        parent::init();

        if (!isset($this->url)) {
            throw new InvalidConfigException('$url must be set, and must be a string !');
        }
    }

    /**
     * Listens queue and runs each job.
     *
     * @param bool $repeat whether to continue listening when queue is empty.
     * @param int $delay number of seconds to sleep before next iteration.
     * @return null|int exit code.
     * @internal for worker command only
     * @since 2.0.2
     */
    public function run($repeat, $delay = 0)
    {
        return $this->runWorker(function (LoopInterface $loop) use ($repeat, $delay) {
            while ($loop->canContinue()) {
                if (($payload = $this->getPayload()) && isset($payload['Body'])) {
                    $body = base64_decode($payload['Body']);
                    list($ttr, $message) = explode(';', $body, 2);
                    $this->reserve($payload, $ttr); //reserve it so it is not visible to another worker till ttr

                    $id = isset($payload['MessageId']) ? $payload['MessageId'] : null;
                    if ($this->handleMessage($id, $message, $ttr, 1)) {
                        //if handled then remove from queue
                        $this->release($payload);
                    }
                } elseif (!$repeat) {
                    break;
                } elseif ($delay) {
                    sleep($delay);
                }
            }
        });
    }

    /**
     * @inheritdoc
     * @throws NotSupportedException
     */
    public function status($id)
    {
        throw new NotSupportedException('Status is not supported in the driver.');
    }

    /**
     * @inheritdoc
     * @throws NotSupportedException
     */
    protected function pushMessage($message, $ttr, $delay, $priority)
    {
        if ($priority) {
            throw new NotSupportedException('Priority is not supported in this driver');
        }

        $model = $this->getClient()->sendMessage([
            'DelaySeconds' => $delay,
            'QueueUrl' => $this->url,
            'MessageBody' => base64_encode($ttr . ';' . $message),
        ]);

        if ($model !== null) {
            return $model['MessageId'];
        } else {
            return false;
        }
    }

    /**
     * @return mixed|null
     */
    private function getPayload()
    {
        $payload = $this->getClient()->receiveMessage([
            'QueueUrl' => $this->url,
            'AttributeNames' => ['ApproximateReceiveCount'],
            'MaxNumberOfMessages' => 1,
        ]);
        $payload = $payload['Messages'];
        if ($payload) {
            return array_pop($payload);
        }
        return null;
    }

    /**
     * Set the visibility to reserve message
     * So that no other worker can see this message
     *
     * @param array $payload
     * @param int $ttr
     */
    private function reserve($payload, $ttr)
    {
        $receiptHandle = $payload['ReceiptHandle'];
        $this->getClient()->changeMessageVisibility([
            'QueueUrl' => $this->url,
            'ReceiptHandle' => $receiptHandle,
            'VisibilityTimeout' => $ttr
        ]);
    }

    /**
     * Mark the message as handled
     *
     * @param array $payload
     * @return boolean
     */
    private function release($payload)
    {
        if (!empty($payload['ReceiptHandle'])) {
            $receiptHandle = $payload['ReceiptHandle'];
            $response = $this->getClient()->deleteMessage([
                'QueueUrl'      => $this->url,
                'ReceiptHandle' => $receiptHandle,
            ]);
            return $response !== null;
        }
        return false;
    }

    /**
     * @return SqsClient
     */
    protected function getClient()
    {
        if (isset($this->_client)) {
            return $this->_client;
        }

        return $this->_client = Yii::$app->aws->getSqsClient();
    }
}
