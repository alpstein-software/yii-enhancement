<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */
namespace alpstein\yii\db;

use alpstein\yii\DateTime;
use yii\base\NotSupportedException;

/**
 * Class Migration
 * @package alpstein\yii\db
 */
class Migration extends \yii\db\Migration
{
    /**
     * make sure is MySQL 5.7, due to using st_distance_sphere function
     */
    public function init()
    {
        parent::init();

        if ($this->db->driverName !== 'mysql') {
            throw new NotSupportedException('This application only support MySQL v5.7');
        }

        $version = $this->db->serverVersion;
        if (strpos($version, '5.7') === false) {
            throw new NotSupportedException('This application only support MySQL v5.7');
        }
    }

    /**
     * @param string $name
     * @param string $table
     * @param string $columns
     * @throws \yii\db\Exception
     */
    public function createSpatialIndex($name, $table, $columns)
    {
        $time = $this->beginCommand('create' . " spatial index $name on $table (" . implode(',', (array) $columns) . ')');
        $sql = 'ALTER TABLE ' . $this->db->quoteTableName($table)
            . ' ADD SPATIAL ' . $this->db->quoteTableName($name)
            . ' (' . $this->db->getQueryBuilder()->buildColumns($columns) . ')';

        $this->db->createCommand($sql)->execute();
        $this->endCommand($time);
    }

    /**
     * @return string|null
     */
    protected function getTableOptions()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        return $tableOptions;
    }

    /**
     * @return array
     */
    protected function generateBaseData()
    {
        $now = DateTime::getCurrentDateTime();
        $data = [
            'created_by' => -1,
            'created_at' => $now,
            'updated_by' => -1,
            'updated_at' => $now,
        ];

        return $data;
    }
}
