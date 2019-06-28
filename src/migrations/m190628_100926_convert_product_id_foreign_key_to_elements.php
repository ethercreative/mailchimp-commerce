<?php

namespace ether\mc\migrations;

use craft\db\Migration;

/**
 * m190628_100926_convert_product_id_foreign_key_to_elements migration.
 */
class m190628_100926_convert_product_id_foreign_key_to_elements extends Migration
{

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->dropForeignKey(
        	$this->getDb()->getForeignKeyName(
		        '{{%mc_products_synced}}',
		        ['productId']
	        ),
	        '{{%mc_products_synced}}'
        );

        $this->addForeignKey(
	        null,
	        '{{%mc_products_synced}}',
	        ['productId'],
	        '{{%elements}}',
	        ['id'],
	        'CASCADE'
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m190628_100926_convert_product_id_foreign_key_to_elements cannot be reverted.\n";
        return false;
    }

}
