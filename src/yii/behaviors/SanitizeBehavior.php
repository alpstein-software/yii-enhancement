<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\yii\behaviors;

use alpstein\services\Sanitizer;
use yii\base\Behavior;
use yii\db\BaseActiveRecord;
use Yii;

/**
 * Class SanitizeBehavior
 * @package backend\base\behaviors
 */
class SanitizeBehavior extends Behavior
{
    /**
     * @var array
     */
    public $purifyAttributes = [];
    /**
     * @var array
     */
    public $stripCleanAttributes = [];

    /**
     * {@inheritdoc}
     */
    public function events()
    {
        return [
            BaseActiveRecord::EVENT_BEFORE_INSERT => 'sanitize',
            BaseActiveRecord::EVENT_BEFORE_UPDATE => 'sanitize',
        ];
    }

    /**
     * purify or strip clean value
     */
    public function sanitize()
    {
        /** @var Sanitizer $sanitizer */
        $sanitizer = Yii::$app->get('sanitizer');

        foreach ($this->purifyAttributes as $attribute) {
            $this->owner->{$attribute} = $sanitizer->purify($this->owner->{$attribute});
        }

        foreach ($this->stripCleanAttributes as $attribute) {
            $this->owner->{$attribute} = $sanitizer->stripClean($this->owner->{$attribute});
        }
    }
}