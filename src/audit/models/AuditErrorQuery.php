<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */


namespace alpstein\audit\models;

use alpstein\yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[AuditError]].
 *
 * @see AuditError
 */
class AuditErrorQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     * @return AuditError[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return AuditError|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
