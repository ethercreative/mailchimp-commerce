<?php
/**
 * Mailchimp for Craft Commerce
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\mc;

use Craft;
use craft\base\Element;
use craft\base\Plugin;
use craft\commerce\elements\Order;
use craft\commerce\elements\Product;
use craft\commerce\events\AddressEvent;
use craft\commerce\records\Discount;
use craft\commerce\services\Addresses;
use craft\errors\ElementNotFoundException;
use craft\errors\SiteNotFoundException;
use craft\events\RegisterCpAlertsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\Cp;
use craft\helpers\UrlHelper;
use craft\web\UrlManager;
use ether\mc\jobs\SyncOrders;
use ether\mc\jobs\SyncProducts;
use ether\mc\jobs\SyncPromos;
use ether\mc\models\Settings;
use ether\mc\services\ChimpService;
use ether\mc\services\FieldsService;
use ether\mc\services\ListsService;
use ether\mc\services\OrdersService;
use ether\mc\services\ProductsService;
use ether\mc\services\PromosService;
use ether\mc\services\StoreService;
use Throwable;
use yii\base\Event;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\base\ModelEvent;

/**
 * Class MailchimpCommerce
 *
 * @author  Ether Creative
 * @package ether\mc
 * @property ChimpService $chimp
 * @property ListsService $lists
 * @property FieldsService $fields
 * @property StoreService $store
 * @property ProductsService $products
 * @property OrdersService $orders
 * @property PromosService $promos
 */
class MailchimpCommerce extends Plugin
{

	// Properties
	// =========================================================================

	/** @var self */
	public static $i;

	public $hasCpSettings = true;
	public $hasCpSection  = true;

	// Craft
	// =========================================================================

	public function init ()
	{
		parent::init();
		self::$i = $this;

		$this->setComponents([
			'chimp' => ChimpService::class,
			'lists' => ListsService::class,
			'fields' => FieldsService::class,
			'store' => StoreService::class,
			'products' => ProductsService::class,
			'orders' => OrdersService::class,
			'promos' => PromosService::class,
		]);

		// Events
		// ---------------------------------------------------------------------

		Event::on(
			UrlManager::class,
			UrlManager::EVENT_REGISTER_CP_URL_RULES,
			[$this, 'onRegisterCpUrlRules']
		);

		Event::on(
			Addresses::class,
			Addresses::EVENT_AFTER_SAVE_ADDRESS,
			[$this, 'onAfterSaveAddress']
		);

		Event::on(
			Cp::class,
			Cp::EVENT_REGISTER_ALERTS,
			[$this, 'onRegisterAlerts']
		);

		// Events: Products
		// ---------------------------------------------------------------------

		foreach ($this->chimp->getProducts() as $product)
		{
			Event::on(
				$product->productClass,
				Element::EVENT_AFTER_SAVE,
				[$this, 'onProductSave']
			);

			Event::on(
				$product->productClass,
				Element::EVENT_BEFORE_RESTORE,
				[$this, 'onProductSave']
			);

			Event::on(
				$product->productClass,
				Element::EVENT_BEFORE_DELETE,
				[$this, 'onProductDelete']
			);
		}

		// Events: Orders
		// ---------------------------------------------------------------------

		Event::on(
			Order::class,
			Order::EVENT_AFTER_SAVE,
			[$this, 'onOrderSave']
		);

		Event::on(
			Order::class,
			Order::EVENT_BEFORE_RESTORE,
			[$this, 'onOrderSave']
		);

		Event::on(
			Order::class,
			Order::EVENT_AFTER_COMPLETE_ORDER,
			[$this, 'onOrderComplete']
		);

		Event::on(
			Order::class,
			Order::EVENT_BEFORE_DELETE,
			[$this, 'onOrderDelete']
		);

		// Events: Promos
		// ---------------------------------------------------------------------

		Event::on(
			Discount::class,
			Discount::EVENT_AFTER_INSERT,
			[$this, 'onDiscountSave']
		);

		Event::on(
			Discount::class,
			Discount::EVENT_AFTER_UPDATE,
			[$this, 'onDiscountSave']
		);

		Event::on(
			Discount::class,
			Discount::EVENT_BEFORE_DELETE,
			[$this, 'onDiscountDelete']
		);

		// Hooks
		// ---------------------------------------------------------------------

		Craft::$app->getView()->hook(
			'cp.commerce.product.edit.details',
			[$this, 'hookProductMeta']
		);

	}

	// Settings
	// =========================================================================

	protected function createSettingsModel ()
	{
		return new Settings();
	}

	public function getSettingsResponse ()
	{
		return Craft::$app->controller->redirect(
			UrlHelper::cpUrl('mailchimp-commerce/connect')
		);
	}

	/**
	 * @return bool|Settings|null
	 */
	public function getSettings ()
	{
		return parent::getSettings();
	}

	// Events
	// =========================================================================

	// Events: Craft
	// -------------------------------------------------------------------------

	/**
	 * @throws Exception
	 */
	protected function afterInstall ()
	{
		$this->store->setStoreId();

		Craft::$app->getPlugins()->enablePlugin('mailchimp-commerce');

		if (Craft::$app->getRequest()->getIsCpRequest())
		{
			Craft::$app->getResponse()->redirect(
				UrlHelper::cpUrl('mailchimp-commerce/connect')
			)->send();
		}
	}

	protected function afterUninstall ()
	{
		$this->store->delete();
	}

	public function onRegisterCpUrlRules (RegisterUrlRulesEvent $event)
	{
		$event->rules['mailchimp-commerce'] = 'mailchimp-commerce/cp/index';
		$event->rules['mailchimp-commerce/sync'] = 'mailchimp-commerce/cp/sync';

		if (Craft::$app->getConfig()->getGeneral()->allowAdminChanges)
		{
			$event->rules['mailchimp-commerce/connect']  = 'mailchimp-commerce/cp/connect';
			$event->rules['mailchimp-commerce/list'] = 'mailchimp-commerce/cp/list';
			$event->rules['mailchimp-commerce/mappings'] = 'mailchimp-commerce/cp/mappings';
			$event->rules['mailchimp-commerce/settings'] = 'mailchimp-commerce/cp/settings';
		}
	}

	public function onRegisterAlerts (RegisterCpAlertsEvent $event)
	{
		if (
			strpos(Craft::$app->getRequest()->getFullPath(), 'mailchimp-commerce') === false ||
			!$this->getSettings()->disableSyncing
		) return;

		$event->alerts[] = self::t('Mailchimp syncing is disabled.');
	}

	// Events: Commerce
	// -------------------------------------------------------------------------

	/**
	 * @param AddressEvent $event
	 *
	 * @throws Exception
	 * @throws Throwable
	 * @throws ElementNotFoundException
	 * @throws SiteNotFoundException
	 * @throws InvalidConfigException
	 */
	public function onAfterSaveAddress (AddressEvent $event)
	{
		if (!$event->address->isStoreLocation)
			return;

		$this->store->update();
	}

	// Events: Products
	// -------------------------------------------------------------------------

	public function onProductSave (ModelEvent $event)
	{
		/** @var Product $product */
		$product = $event->sender;

		Craft::$app->getQueue()->push(new SyncProducts([
			'productIds' => [$product->id],
		]));
	}

	/**
	 * @param ModelEvent $event
	 *
	 * @throws \yii\db\Exception
	 */
	public function onProductDelete (ModelEvent $event)
	{
		/** @var Product $product */
		$product = $event->sender;

		$this->products->deleteProductById($product->id);
	}

	// Events: Orders
	// -------------------------------------------------------------------------

	public function onOrderSave (ModelEvent $event)
	{
		/** @var Order $order */
		$order = $event->sender;

		Craft::$app->getQueue()->push(new SyncOrders([
			'orderIds' => [$order->id],
		]));
	}

	/**
	 * @param Event $event
	 *
	 * @throws Exception
	 * @throws InvalidConfigException
	 * @throws Throwable
	 * @throws \yii\db\Exception
	 */
	public function onOrderComplete (Event $event)
	{
		/** @var Order $order */
		$order = $event->sender;

		$this->orders->deleteOrderById($order->id, true);
		$this->orders->syncOrderById($order->id);
	}

	/**
	 * @param ModelEvent $event
	 *
	 * @throws \yii\db\Exception
	 */
	public function onOrderDelete (ModelEvent $event)
	{
		/** @var Order $order */
		$order = $event->sender;

		$this->orders->deleteOrderById($order->id);
	}

	// Events: Promos
	// -------------------------------------------------------------------------

	public function onDiscountSave (Event $event)
	{
		/** @var Discount $discount */
		$discount = $event->sender;

		Craft::$app->getQueue()->push(new SyncPromos([
			'promoIds' => [$discount->id],
		]));
	}

	/**
	 * @param ModelEvent $event
	 *
	 * @throws \yii\db\Exception
	 */
	public function onDiscountDelete (ModelEvent $event)
	{
		/** @var Discount $discount */
		$discount = $event->sender;

		$this->promos->deletePromoById($discount->id);
	}

	// Hooks
	// =========================================================================

	/**
	 * @param array $context
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function hookProductMeta (array &$context)
	{
		/** @var Product $product */
		$product = $context['product'];

		$heading = MailchimpCommerce::t('Last Synced to Mailchimp');
		$date = $this->products->getLastSyncedById($product->id);

		return <<<HTML
<div class="data">
    <h5 class="heading">{$heading}</h5>
    <div class="value">{$date}</div>
</div>
HTML;
	}

	// Helpers
	// =========================================================================

	public static function t ($message, $params = [])
	{
		return Craft::t('mailchimp-commerce', $message, $params);
	}

}
