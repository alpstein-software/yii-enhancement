<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\yii\base;

use yii\base\InvalidConfigException;
use yii\helpers\Json;
use Yii;

/**
 * Class Security
 * @property \alpstein\helpers\TokenHelper token
 * @package alpstein\yii\base
 */
class Security extends \yii\base\Security
{
    /**
     * @var \alpstein\helpers\TokenHelper
     */
    private $_token;

    public $encodeKey = 'Alpstein-139A';

    /**
     * @param array $params
     * @return string
     */
    public function encodeParams($params = [])
    {
        $json = Json::encode($params);
        $hash = md5($json . $this->encodeKey);
        return base64_encode($json) . '.' . $hash;
    }

    /**
     * @param string $query
     * @return array|false
     */
    public function decodeQuery($query)
    {
        if (strpos($query, '.') === false) {
            return false;
        }

        list($data, $hash) = explode('.', $query);
        $json = base64_decode($data);
        if ($hash == md5($json . $this->encodeKey)) {
            return Json::decode($json);
        }

        return false;
    }

    /**
     * initialize the web token helper
     *
     * @param string|array $config
     * @throws InvalidConfigException
     */
    public function setToken($config)
    {
        $this->_token = Yii::createObject($config);
    }

    /**
     * @return \alpstein\helpers\TokenHelper
     */
    public function getToken()
    {
        return $this->_token;
    }
}
