<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\yii;

/**
 * Class DateTime
 * @package alpstein
 */
class DateTime extends \DateTime
{
    /**
     * DateTime constructor.
     * @param string $time
     * @param \DateTimeZone $timezone
     */
    public function __construct($time = 'now', \DateTimeZone $timezone = null)
    {
        if ($time === 'now') {
            $datetime = static::getCurrentDateTime();
            return parent::__construct($datetime, $timezone);
        }
        return parent::__construct($time, $timezone);
    }

    /**
     * @return $this
     */
    public function local()
    {
        $this->setTimezone(new \DateTimeZone(\Yii::$app->timeZone));
        return $this;
    }

    /**
     * @return string
     */
    public function formatToDatabaseDate()
    {
        return $this->format('Y-m-d');
    }

    /**
     * @return string
     */
    public function formatToDatabaseDatetime()
    {
        return $this->format('Y-m-d H:i:s');
    }

    /**
     * @return string
     */
    public function formatToRFC3339()
    {
        return $this->format(self::RFC3339);
    }

    /**
     * @return int
     */
    public function getDateInteger()
    {
        return (int) $this->format('Ymd');
    }

    /**
     * Get current date time format in Y-m-d H:i:s format
     * @return string
     */
    public static function getCurrentDateTime()
    {
        return date('Y-m-d H:i:s', self::getCurrentTimestamp());
    }

    /**
     * @return string
     */
    public static function getCurrentTime()
    {
        return date('H:i:s', self::getCurrentTimestamp());
    }

    /**
     * Get current date format in Y-m-d format
     * @return string
     */
    public static function getCurrentDate()
    {
        return date('Y-m-d', self::getCurrentTimestamp());
    }

    /**
     * @return int
     */
    public static function getCurrentTimestamp()
    {
        if (YII_DEBUG) {
            $params = \Yii::$app->params;
            if (isset($params['debug']) && isset($params['debug']['datetime'])) {
                return strtotime($params['debug']['datetime']);
            }
        }
        return time();
    }

    /**
     * @return static
     */
    public static function now()
    {
        $datetime = static::getCurrentDateTime();
        return new static($datetime);
    }
}
