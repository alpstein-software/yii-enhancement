<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\services;

use alpstein\yii\base\BaseObject;
use Aws\CloudWatch\CloudWatchClient;
use Aws\CodeDeploy\CodeDeployClient;
use Aws\Credentials\Credentials;
use Aws\DynamoDb\DynamoDbClient;
use Aws\Rds\RdsClient;
use Aws\S3\S3Client;
use Aws\Sqs\SqsClient;
use yii\helpers\ArrayHelper;

/**
 * Class Aws
 * @property Credentials credentials
 * @package alpstein\services
 */
class Aws extends BaseObject
{
    /**
     * @var string default region
     */
    public $region;
    /**
     * the API access key
     * @var string
     */
    private $_apiKey;
    /**
     * the API access secret
     * @var string
     */
    private $_apiSecret;

    /**
     * @param $value string
     */
    protected function setApiKey($value)
    {
        $this->_apiKey = $value;
    }

    /**
     * @param $value string
     */
    protected function setApiSecret($value)
    {
        $this->_apiSecret = $value;
    }

    /**
     * @return Credentials
     */
    public function getCredentials()
    {
        $credentials = new Credentials($this->_apiKey, $this->_apiSecret);
        return $credentials;
    }

    /**
     * @param array $config
     * @return S3Client
     */
    public function getS3Client($config = [])
    {
        $key = md5(__METHOD__ . '-v1-' . @serialize($config));
        return $this->retrieveData($key, function () use ($config) {
            $defaultOptions = [
                'version' => 'latest',
                'region' => $this->region,
                'credentials' => $this->credentials,
            ];

            $options = ArrayHelper::merge($defaultOptions, $config);
            return new S3Client($options);
        });
    }

    /**
     * @param array $config
     * @return RdsClient
     */
    public function getRdsClient($config = [])
    {
        $key = md5(__METHOD__ . '-v1-' . @serialize($config));
        return $this->retrieveData($key, function () use ($config) {
            $defaultOptions = [
                'version' => 'latest',
                'region' => $this->region,
                'credentials' => $this->credentials,
            ];

            $options = ArrayHelper::merge($defaultOptions, $config);
            return new RdsClient($options);
        });
    }

    /**
     * @param array $config
     * @return DynamoDbClient
     */
    public function getDynamoDbClient($config = [])
    {
        $key = md5(__METHOD__ . '-v1-' . @serialize($config));
        return $this->retrieveData($key, function () use ($config) {
            $defaultOptions = [
                'version' => 'latest',
                'region' => $this->region,
                'credentials' => $this->credentials,
            ];

            $options = ArrayHelper::merge($defaultOptions, $config);
            return new DynamoDbClient($options);
        });
    }

    /**
     * @param array $config
     * @return SqsClient
     */
    public function getSqsClient($config = [])
    {
        $key = md5(__METHOD__ . '-v1-' . @serialize($config));
        return $this->retrieveData($key, function () use ($config) {
            $defaultOptions = [
                'version' => 'latest',
                'region' => $this->region,
                'credentials' => $this->credentials,
            ];

            $options = ArrayHelper::merge($defaultOptions, $config);
            return new SqsClient($options);
        });
    }

    /**
     * @param array $config
     * @return CodeDeployClient
     */
    public function getCodeDeployClient($config = [])
    {
        $key = md5(__METHOD__ . '-v1-' . @serialize($config));
        return $this->retrieveData($key, function () use ($config) {
            $defaultOptions = [
                'version' => 'latest',
                'region' => $this->region,
                'credentials' => $this->credentials,
            ];

            $options = ArrayHelper::merge($defaultOptions, $config);
            return new CodeDeployClient($options);
        });
    }

    /**
     * @param array $config
     * @return CloudWatchClient
     */
    public function getCloudWatchClient($config = [])
    {
        $key = md5(__METHOD__ . '-v1-' . @serialize($config));
        return $this->retrieveData($key, function () use ($config) {
            $defaultOptions = [
                'version' => 'latest',
                'region' => $this->region,
                'credentials' => $this->credentials,
            ];

            $options = ArrayHelper::merge($defaultOptions, $config);
            return new CloudWatchClient($options);
        });
    }
}
