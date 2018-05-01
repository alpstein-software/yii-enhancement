<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\services;

use alpstein\yii\base\BaseObject;
use Google\Cloud\Datastore\DatastoreClient;
use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\Storage\StorageClient;
use Psr\Cache\CacheItemPoolInterface;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * Class Gcp
 * @package alpstein\services
 */
class Gcp extends BaseObject
{
    /**
     * The project id
     * @var string
     */
    public $projectId;
    /**
     * @var string
     */
    public $privateKey;
    /**
     * @var string
     */
    public $privateKeyId;
    /**
     * @var string
     */
    public $clientId;
    /**
     * @var string
     */
    public $clientEmail;
    /**
     * @var string
     */
    public $clientX509CertUrl;
    /**
     * The project id
     * @var string
     */
    public $type = 'service_account';
    /**
     * @var string
     */
    public $authUri = 'https://accounts.google.com/o/oauth2/auth';
    /**
     * @var string
     */
    public $tokenUri = 'https://accounts.google.com/o/oauth2/token';
    /**
     * @var string
     */
    public $authProviderX509CertUrl = 'https://www.googleapis.com/oauth2/v1/certs';
    /**
     * @var string
     */
    public $authCache;

    /**
     * initializing, make sure have required params
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        foreach (['projectId', 'privateKey', 'privateKeyId', 'clientId', 'clientEmail', 'clientX509CertUrl'] as $property) {
            if (!isset($this->{$property})) {
                throw new InvalidConfigException(strtr('{property} must be set !', ['{property}' => $property]));
            }
        }
    }

    /**
     * @param array $config
     * @return DatastoreClient
     * @throws InvalidConfigException
     */
    public function getDatastoreClient($config = [])
    {
        $config = ArrayHelper::merge($this->getDefaultConfig(), $config);
        return new DatastoreClient($config);
    }

    /**
     * @param array $config
     * @return StorageClient
     * @throws InvalidConfigException
     */
    public function getStorageClient($config = [])
    {
        $config = ArrayHelper::merge($this->getDefaultConfig(), $config);
        return new StorageClient($config);
    }

    /**
     * @param array $config
     * @return PubSubClient
     * @throws InvalidConfigException
     */
    public function getPubSubClient($config = [])
    {
        $config = ArrayHelper::merge($this->getDefaultConfig(), $config);
        return new PubSubClient($config);
    }

    /**
     * @return array
     * @throws InvalidConfigException
     */
    protected function getDefaultConfig()
    {
        $config = [
            'projectId' => $this->projectId,
            'keyFile' => $this->getKeyFile(),
        ];

        if (isset($this->authCache) && ($authCache = Yii::createObject($this->authCache)) instanceof CacheItemPoolInterface) {
            $config['authCache'] = $authCache;
        }

        return $config;
    }

    /**
     * @return array
     */
    protected function getKeyFile()
    {
        return [
            'type' => $this->type,
            'project_id' => $this->projectId,
            'private_key_id' => $this->privateKeyId,
            'private_key' => $this->privateKey,
            'client_email' => $this->clientEmail,
            'client_id' => $this->clientId,
            'auth_uri' => $this->authUri,
            'token_uri' => $this->tokenUri,
            'auth_provider_x509_cert_url' => $this->authProviderX509CertUrl,
            'client_x509_cert_url' => $this->clientX509CertUrl,
        ];
    }
}
