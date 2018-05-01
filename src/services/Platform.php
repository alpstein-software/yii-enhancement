<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\services;

use alpstein\yii\base\BaseObject;
use alpstein\yii\helpers\StringHelper;
use Jenssegers\Agent\Agent;
use yii\web\HeaderCollection;
use yii\web\Request;
use Yii;

/**
 * Class Platform
 * @property string $deviceName
 * @property string $osName
 * @property string $osVersion
 * @property string $browserName
 * @property string $browserVersion
 * @property string $robotName
 * @property bool $isDesktop
 * @property bool $isPhone
 * @property bool $isTablet
 * @property bool $isRobot
 * @package alpstein\services
 */
class Platform extends BaseObject
{
    /**
     * @var mixed
     */
    private $_helper;

    /**
     * use 3rd party library as helper to find out the data we want
     * @return Agent
     */
    protected function getHelper()
    {
        if (isset($this->_helper)) {
            return $this->_helper;
        }

        return $this->_helper = new Agent();
    }

    /**
     * @return HeaderCollection|array
     */
    protected function getHeaders()
    {
        return $this->retrieveData(__METHOD__, function () {
            if ($request = $this->getRequest()) {
                return $request->getHeaders();
            }

            return [];
        }, []);
    }

    /**
     * @return Request|bool
     */
    protected function getRequest()
    {
        return $this->retrieveData(__METHOD__, function () {
            if (($request = Yii::$app->request) instanceof Request) {
                return  $request;
            }

            return false;
        }, false);
    }

    /**
     * @return string|null
     */
    public function getIpAddress()
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            if (($request = Yii::$app->request) instanceof Request) {
                return $request->getUserIP();
            }
        }
        return null;
    }

    /**
     * Get the device name, if mobile. (iPhone, Nexus, AsusTablet, ...)
     * @return mixed
     */
    public function getDeviceName()
    {
        return $this->getHelper()->device();
    }

    /**
     * Get the operating system. (Ubuntu, Windows, OS X, ...)
     * @return mixed
     */
    public function getOsName()
    {
        if ($this->getIsApp()) {
            return $this->getAppOsName();
        }

        return $this->getHelper()->platform();
    }

    /**
     * Get the operating system version
     * @return mixed
     */
    public function getOsVersion()
    {
        if ($this->getIsApp()) {
            return $this->getAppVersion();
        }

        return $this->getHelper()->version($this->getOsName());
    }
    /**
     * Get the browser name. (Chrome, IE, Safari, Firefox, ...)
     * @return mixed
     */
    public function getBrowserName()
    {
        if ($this->getIsApp()) {
            return 'Alpstein ' . $this->getAppOsName();
        }

        return $this->getHelper()->browser();
    }

    /**
     * Get the browser version
     * @return mixed
     */
    public function getBrowserVersion()
    {
        if ($this->getIsApp()) {
            return $this->getAppVersion();
        }

        return $this->getHelper()->version($this->getBrowserName());
    }

    /**
     * Get the robot name. Note: this currently only works for major robots like Google, Facebook, Twitter, Bing, Baidu etc
     * @return mixed
     */
    public function getRobotName()
    {
        return $this->getHelper()->robot();
    }

    /**
     * @return string
     */
    public function getDeviceType()
    {
        if ($this->getIsApp()) {
            return 'app';
        } elseif ($this->getIsDesktop()) {
            return 'desktop';
        } elseif ($this->getIsPhone()) {
            return 'mobile';
        } elseif ($this->getIsTablet()) {
            return 'tablet';
        } elseif ($this->getIsRobot()) {
            return 'robot';
        }
        
        return 'desktop';
    }

    /**
     * Check if the user is using a desktop device.
     * @return bool
     */
    public function getIsDesktop()
    {
        return $this->getHelper()->isDesktop();
    }

    /**
     * Check if the user is using a phone device.
     * @return bool
     */
    public function getIsPhone()
    {
        return $this->getHelper()->isPhone();
    }

    /**
     * Check if the user is using a tablet device.
     * @return bool
     */
    public function getIsTablet()
    {
        return $this->getHelper()->isTablet();
    }

    /**
     * Check if the user is a robot.
     * @return bool
     */
    public function getIsRobot()
    {
        return $this->getHelper()->isRobot();
    }

    /**
     * @return bool
     */
    public function getIsWeb()
    {
        return !$this->getIsApp();
    }

    /**
     * check if the request from app
     * @return bool
     */
    public function getIsApp()
    {
        return $this->retrieveData(__METHOD__, function () {
            if (($osName = $this->getAppOsName()) === null) {
                return false;
            }

            $osName = strtolower($osName);
            if (!in_array($osName, ['android', 'ios'])) {
                return false;
            }

            if ($this->getAppVersion() === null) {
                return false;
            }

            return true;
        });
    }

    /**
     * @return bool
     */
    public function getIsIos()
    {
        return $this->getIsApp() && $this->getOsName() == 'iOS';
    }

    /**
     * @return bool
     */
    public function getIsAndroid()
    {
        return $this->getIsApp() && $this->getOsName() == 'Android';
    }

    /**
     * @param string|int $version
     * @return bool
     */
    public function getIsAppVersionGreaterThan($version)
    {
        $version = StringHelper::resolveVersion($version);
        $appVersion = StringHelper::resolveVersion($this->getAppVersion());

        return $appVersion > $version;
    }

    /**
     * @param string|int $version
     * @return bool
     */
    public function getIsAppVersionGreaterOrEqualThan($version)
    {
        $version = StringHelper::resolveVersion($version);
        $appVersion = StringHelper::resolveVersion($this->getAppVersion());

        return $appVersion >= $version;
    }

    /**
     * @param string|int $version
     * @return bool
     */
    public function getIsAppVersionLesserThan($version)
    {
        $version = StringHelper::resolveVersion($version);
        $appVersion = StringHelper::resolveVersion($this->getAppVersion());

        return $appVersion < $version;
    }

    /**
     * @param string|int $version
     * @return bool
     */
    public function getIsAppVersionLesserOrEqualThan($version)
    {
        $version = StringHelper::resolveVersion($version);
        $appVersion = StringHelper::resolveVersion($this->getAppVersion());

        return $appVersion <= $version;
    }

    /**
     * @return string
     */
    public function getAppOsName()
    {
        return $this->retrieveData(__METHOD__ . '-v1', function () {
            $headers = $this->getHeaders();
            $osName = isset($headers['APP-OS-NAME']) ? $headers['APP-OS-NAME'] : null;

            if (is_scalar($osName)) {
                $osName = strtolower($osName);
                if ($osName === 'android') {
                    return 'Android';
                } elseif ($osName === 'ios' || $osName === 'iphone os') {
                    return 'iOS';
                }

                return ucwords($osName);
            }

            return $osName;
        });
    }

    /**
     * @return string
     */
    public function getAppVersion()
    {
        return $this->retrieveData(__METHOD__, function () {
            $headers = $this->getHeaders();
            return isset($headers['APP-VERSION']) ? $headers['APP-VERSION'] : null;
        });
    }
}
