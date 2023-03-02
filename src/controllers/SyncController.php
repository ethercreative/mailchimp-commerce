<?php

/**
 * Mailchimp for Craft Commerce
 *
 * @link      https://crankdcreative.co.uk
 * @copyright Copyright (c) 2023 Crankd Creative
 */

namespace crankd\mc\controllers;

use Craft;
use craft\commerce\elements\Order;
use craft\commerce\records\Discount;
use craft\db\Query;
use craft\errors\ElementNotFoundException;
use craft\errors\SiteNotFoundException;
use craft\web\Controller;
use crankd\mc\jobs\SyncOrders;
use crankd\mc\jobs\SyncProducts;
use crankd\mc\jobs\SyncPromos;
use crankd\mc\MailchimpCommerce;
use Throwable;
use yii\base\Exception;
use yii\base\InvalidConfigException;

/**
 * Class SyncController
 *
 * @author  Crankd Creative
 * @package crankd\mc\controllers
 */
class SyncController extends Controller
{

	/**
	 * @throws Throwable
	 * @throws ElementNotFoundException
	 * @throws SiteNotFoundException
	 * @throws Exception
	 * @throws InvalidConfigException
	 */
	public function actionStore()
	{
		MailchimpCommerce::$i->store->update();

		Craft::$app->getSession()->setNotice(
			MailchimpCommerce::t('Store Synced.')
		);
	}

	public function actionAllProducts()
	{
		$productClass = Craft::$app->getRequest()->getRequiredBodyParam('class');
		$typeId = Craft::$app->getRequest()->getBodyParam('type');

		$mailchimpProducts = MailchimpCommerce::getInstance()->chimp->getProducts();
		$productIds = [];
		$productName = 'Products';

		foreach ($mailchimpProducts as $product) {
			if ($product->productClass !== $productClass)
				continue;

			$callable = $product->getProductIds;
			$productIds = $callable($typeId);
			$productName = $product->productName;
		}

		Craft::$app->getQueue()->push(
			new SyncProducts(compact('productIds', 'productName'))
		);
	}

	public function actionAllCarts()
	{
		Craft::$app->getQueue()->push(
			new SyncOrders([
				'orderIds' => Order::find()->isCompleted(false)->ids()
			])
		);
	}

	public function actionAllOrders()
	{
		Craft::$app->getQueue()->push(
			new SyncOrders([
				'orderIds' => Order::find()->isCompleted(true)->ids()
			])
		);
	}

	public function actionAllPromos()
	{
		Craft::$app->getQueue()->push(
			new SyncPromos([
				'promoIds' => Discount::find()->select('id')->column(),
			])
		);
	}
}
