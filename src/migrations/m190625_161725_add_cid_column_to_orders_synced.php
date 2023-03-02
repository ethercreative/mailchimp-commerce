<?php

namespace crankd\mc\migrations;

use Craft;
use craft\db\Migration;

/**
 * m190625_161725_add_cid_column_to_orders_synced migration.
 */
class m190625_161725_add_cid_column_to_orders_synced extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%mc_orders_synced}}',
            'cid',
            $this->string()->null()
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn(
            '{{%mc_orders_synced}}',
            'cid'
        );
    }
}
