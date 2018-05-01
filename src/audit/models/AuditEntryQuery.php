<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\audit\models;

use alpstein\yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[AuditEntry]].
 *
 * @see AuditEntry
 */
class AuditEntryQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     * @return AuditEntry[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return AuditEntry|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
