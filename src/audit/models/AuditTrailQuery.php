<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\audit\models;

use alpstein\yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[AuditTrail]].
 *
 * @see AuditTrail
 */
class AuditTrailQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     * @return AuditTrail[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return AuditTrail|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
