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
	}

	public function safeDown ()
	{
		$this->_downProductsSynced();
		$this->_downOrdersSynced();
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

}