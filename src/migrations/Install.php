<?php
/**
 * Mailchimp for Craft Commerce
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\mc\migrations;

use craft\db\Migration;

/**
 * Class Install
 *
 * @author  Ether Creative
 * @package ether\mc\migrations
 */
class Install extends Migration
{

	public function safeUp ()
	{
		$this->_upProductsSynced();
		$this->_upOrdersSynced();
		$this->_upPromosSynced();
	}

	public function safeDown ()
	{
		$this->_downProductsSynced();
		$this->_downOrdersSynced();
		$this->_downPromosSynced();
	}

	// Products Synced
	// =========================================================================

	private function _upProductsSynced ()
	{
		$this->createTable('{{%mc_products_synced}}', [
			'productId' => $this->integer(),
			'lastSynced' => $this->dateTime(),
		]);

		$this->addPrimaryKey(
			'productId',
			'{{%mc_products_synced}}',
			['productId']
		);

		$this->addForeignKey(
			null,
			'{{%mc_products_synced}}',
			['productId'],
			'{{%commerce_products}}',
			['id'],
			'CASCADE'
		);
	}

	private function _downProductsSynced ()
	{
		$this->dropTableIfExists('{{%mc_products_synced}}');
	}

	// Orders Synced
	// =========================================================================

	private function _upOrdersSynced ()
	{
		$this->createTable('{{%mc_orders_synced}}', [
			'orderId' => $this->integer(),
			'isCart' => $this->boolean(),
			'cid' => $this->string()->null(),
			'lastSynced' => $this->dateTime(),
		]);

		$this->addPrimaryKey(
			'orderId',
			'{{%mc_orders_synced}}',
			['orderId']
		);

		$this->addForeignKey(
			null,
			'{{%mc_orders_synced}}',
			['orderId'],
			'{{%commerce_orders}}',
			['id'],
			'CASCADE'
		);
	}

	private function _downOrdersSynced ()
	{
		$this->dropTableIfExists('{{%mc_orders_synced}}');
	}

	// Promos Synced
	// =========================================================================

	private function _upPromosSynced ()
	{
		$this->createTable('{{%mc_promos_synced}}', [
			'promoId' => $this->integer(),
			'lastSynced' => $this->dateTime(),
		]);

		$this->addPrimaryKey(
			'promoId',
			'{{%mc_promos_synced}}',
			['promoId']
		);

		$this->addForeignKey(
			null,
			'{{%mc_promos_synced}}',
			['promoId'],
			'{{%commerce_discounts}}',
			['id'],
			'CASCADE'
		);
	}

	private function _downPromosSynced ()
	{
		$this->dropTableIfExists('{{%mc_promos_synced}}');
	}

}
