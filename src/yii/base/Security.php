<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\yii\base;

use yii\base\InvalidConfigException;
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
