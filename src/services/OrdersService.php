<?php

/**
 * Mailchimp for Craft Commerce
 *
 * @link      https://crankdcreative.co.uk
 * @copyright Copyright (c) 2023 Crankd Creative
 */

namespace crankd\mc\services;

use Craft;
use craft\base\Component;
use craft\base\Field;
use craft\commerce\elements\Order;
use craft\commerce\elements\Product;
use craft\commerce\elements\Variant;
use craft\elements\Address;
use craft\commerce\Plugin as Commerce;
use craft\helpers\Db;
use craft\helpers\UrlHelper;
use DateTime;
use crankd\mc\helpers\AddressHelper;
use crankd\mc\MailchimpCommerce;
use Throwable;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\db\Query;

/**
 * Class OrdersService
 *
 * @author  Crankd Creative
 * @package crankd\mc\services
 */
class OrdersService extends Component
{

	// Public
	// =========================================================================

	/**
	 * @param $orderId
	 *
	 * @return bool
	 * @throws InvalidConfigException
	 * @throws Throwable
	 * @throws \yii\base\Exception
	 */
	public function syncOrderById($orderId)
	{
		if (MailchimpCommerce::getInstance()->getSettings()->disableSyncing)
			return true;

		$hasBeenSynced = $this->_hasOrderBeenSynced($orderId);
		list($order, $data) = $this->_buildOrderData($orderId);

		if ($data === null)
			return true;

		if ($hasBeenSynced)
			return $this->_updateOrder($order, $data);
		else
			return $this->_createOrder($order, $data);
	}

	/**
	 * Deletes the order from Mailchimp
	 *
	 * @param      $orderId
	 * @param bool $asCart
	 *
	 * @return bool|void
	 * @throws Exception
	 */
	public function deleteOrderById($orderId, $asCart = false)
	{
		if (MailchimpCommerce::getInstance()->getSettings()->disableSyncing)
			return;

		if (!$this->_hasOrderBeenSynced($orderId))
			return;

		$storeId = MailchimpCommerce::$i->getSettings()->storeId;
		$order = Commerce::getInstance()->getOrders()->getOrderById($orderId);
		$type = $asCart || !$order->isCompleted ? 'carts' : 'orders';

		list($success, $data, $error) = MailchimpCommerce::$i->chimp->delete(
			'ecommerce/stores/' . $storeId . '/'  . $type . '/' . $orderId
		);

		if (!$success) {
			Craft::error($error, 'mailchimp-commerce');
			return false;
		}

		Craft::$app->getDb()->createCommand()
			->delete('{{%mc_orders_synced}}', [
				'orderId' => $orderId,
			])->execute();

		return true;
	}

	/**
	 * Returns the total number of orders synced
	 *
	 * @param bool $getCarts
	 *
	 * @return int|string
	 */
	public function getTotalOrdersSynced($getCarts = false)
	{
		return (new Query())
			->from('{{%mc_orders_synced}}')
			->where(['isCart' => $getCarts])
			->count();
	}

	// Private
	// =========================================================================

	/**
	 * Creates a new cart/order in Mailchimp
	 *
	 * @param Order $order
	 * @param       $data
	 *
	 * @return bool
	 * @throws Exception
	 */
	private function _createOrder(Order $order, $data)
	{
		$storeId = MailchimpCommerce::$i->getSettings()->storeId;
		$type = $order->isCompleted ? 'orders' : 'carts';

		list($success, $data, $error) = MailchimpCommerce::$i->chimp->post(
			'ecommerce/stores/' . $storeId . '/' . $type,
			$data
		);

		if (!$success) {
			Craft::error('Create: ' . $error, 'mailchimp-commerce');
			return false;
		}

		Craft::$app->getDb()->createCommand()
			->insert(
				'{{%mc_orders_synced}}',
				[
					'orderId'    => $order->id,
					'isCart'     => !$order->isCompleted,
					'lastSynced' => Db::prepareDateForDb(new DateTime()),
				],
				false
			)->execute();

		return true;
	}

	/**
	 * Updates the given cart/order in Mailchimp
	 *
	 * @param $order
	 * @param $data
	 *
	 * @return bool
	 * @throws Exception
	 */
	private function _updateOrder($order, $data)
	{
		$storeId = MailchimpCommerce::$i->getSettings()->storeId;
		$type    = $order->isCompleted ? 'orders' : 'carts';

		list($success, $data, $error) = MailchimpCommerce::$i->chimp->patch(
			'ecommerce/stores/' . $storeId . '/' . $type . '/' . $order->id,
			$data
		);

		if (!$success) {
			Craft::error('Update: ' . $error, 'mailchimp-commerce');
			return false;
		}

		Craft::$app->getDb()->createCommand()
			->update(
				'{{%mc_orders_synced}}',
				[
					'isCart'     => !$order->isCompleted,
					'lastSynced' => Db::prepareDateForDb(new DateTime()),
				],
				['orderId' => $order->id],
				[],
				false
			)->execute();

		return true;
	}

	// Helpers
	// =========================================================================

	/**
	 * Checks if the given order ID has been synced
	 *
	 * @param $orderId
	 *
	 * @return bool
	 */
	private function _hasOrderBeenSynced($orderId)
	{
		return (new Query())
			->from('{{%mc_orders_synced}}')
			->where(['orderId' => $orderId])
			->exists();
	}

	/**
	 * Build the order data
	 *
	 * @param $orderId
	 *
	 * @return array
	 * @throws Throwable
	 * @throws \yii\base\Exception
	 * @throws InvalidConfigException
	 */
	private function _buildOrderData($orderId)
	{
		$order = Commerce::getInstance()->getOrders()->getOrderById($orderId);

		if (!$order || !$order->email || empty($order->getLineItems()))
			return [$order, null];

		$data = [
			'id' => (string) $order->id,
			'currency_code' => $order->getPaymentCurrency(),
			'order_total' => (float) $order->getTotalPrice(),
			'tax_total' => (float) $order->getTotalTax(),
			'lines' => [],
			'promos' => [],
			'customer' => [
				'id' => (string) $order->customer->id,
				'email_address' => $order->customer->email ?: $order->email,
				'opt_in_status' => $this->_hasOptedIn($order),
				'first_name' => $order->customer->firstName ? $order->customer->firstName : '',
				'last_name' => $order->customer->lastName ? $order->customer->lastName : '',
				'orders_count' => (int) Order::find()->customer($order->customer)->isCompleted()->count(),
				'total_spent' => (float) Order::find()->customer($order->customer)->isCompleted()->sum('[[commerce_orders.totalPaid]]') ?: 0,
			],
		];


		if ($order->billingAddress)
			$data['customer']['address'] = self::_address($order->billingAddress);

		$cid = (new Query())
			->select('cid')
			->from('{{%mc_orders_synced}}')
			->where(['orderId' => $order->id])
			->scalar();

		if ($cid)
			$data['campaign_id'] = $cid;

		foreach ($order->lineItems as $item) {
			if (!$item->purchasable)
				continue;

			$li = [
				'id' => (string)$item->id,
				'product_id' => (string)$this->_getProduct($item->purchasable)->id,
				'product_variant_id' => (string)$item->purchasable->id,
				'quantity' => (int)$item->qty,
				'price' => (float)$item->price,
			];

			if ($order->isCompleted)
				$li['discount'] = (float)$item->getDiscount();

			$data['lines'][] = $li;
		}

		// Don't sync the order if there are no line items
		if (count($data['lines']) === 0)
			return [$order, null];

		if ($order->isCompleted) {
			$completeData = [
				'financial_status' => $order->lastTransaction ? $order->lastTransaction->status : 'paid',
				'discount_total' => (float) $order->getTotalDiscount(),
				'tax_total' => (float) $order->getTotalTax(),
				'shipping_total' => (float) $order->getTotalShippingCost(),
				'processed_at_foreign' => $order->dateOrdered->format('c'),
				'updated_at_foreign' => $order->dateUpdated->format('c'),
			];

			if ($order->shippingAddress)
				$completeData['shipping_address'] = self::_address($order->shippingAddress);

			if ($order->billingAddress)
				$completeData['billing_address'] = self::_address($order->billingAddress);

			$data = array_merge($data, $completeData);

			if ($order->returnUrl)
				$data['order_url'] = UrlHelper::siteUrl($order->returnUrl);

			if ($this->_isOrderShipped($order))
				$data['fulfillment_status'] = 'shipped';

			$promo =
				$order->couponCode
				? Commerce::getInstance()->getDiscounts()->getDiscountByCode($order->couponCode)
				: null;

			foreach ($order->getAdjustments() as $adjustment) {
				$isPromoCode = $promo && $promo->name === $adjustment->name;

				$data['promos'][] = [
					'code'              => $isPromoCode ? $order->couponCode : $adjustment->name,
					'amount_discounted' => (float) $adjustment->amount,
					'type'              => 'fixed',
				];
			}
		} else {
			$data['checkout_url'] = UrlHelper::siteUrl(
				Craft::$app->getConfig()->getGeneral()->actionTrigger . '/mailchimp-commerce/order/restore',
				['number' => $order->number]
			);
		}

		return [$order, $data];
	}

	/**
	 * Checks if the given order has been shipped
	 *
	 * @param Order $order
	 *
	 * @return bool
	 */
	private function _isOrderShipped(Order $order)
	{
		return $order->orderStatus->handle === MailchimpCommerce::$i->getSettings()->shippedStatusHandle;
	}

	/**
	 * Check if the customer has opted in for marketing emails
	 *
	 * @param Order $order
	 *
	 * @return bool
	 */
	private function _hasOptedIn(Order $order)
	{
		$fieldUid = MailchimpCommerce::$i->getSettings()->optInField;

		if (!$fieldUid)
			return false;

		/** @var Field $field */
		$field = Craft::$app->getFields()->getFieldByUid($fieldUid);

		if (!$field)
			return false;

		try {
			if (
				$order->getCustomer() &&
				$order->getCustomer()->getUser() &&
				$order->getCustomer()->getUser()->{$field->handle}
			) return true;
		} catch (\Exception $e) {
		}

		if ($order->{$field->handle})
			return true;

		return false;
	}

	/**
	 * Gets the product for the given purchasable
	 *
	 * @param $purchasable
	 *
	 * @return Product|null
	 * @throws InvalidConfigException
	 */
	private function _getProduct($purchasable)
	{
		$mailchimpProducts = MailchimpCommerce::getInstance()->chimp->getProducts();

		foreach ($mailchimpProducts as $product) {
			if ($purchasable instanceof $product->variantClass) {
				$callable = [$purchasable, $product->variantToProductMethod];

				return $callable();
			}
		}

		/** @var Variant $purchasable */
		return $purchasable->getProduct();
	}

	/**
	 * Converts an address to an array
	 *
	 * @param Address $address
	 *
	 * @return array
	 */
	private static function _address(Address $address)
	{
		return array_filter(@AddressHelper::asArray($address));
	}
}
