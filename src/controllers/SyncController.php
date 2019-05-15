<?php
/**
 * Mailchimp for Craft Commerce
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\mc\controllers;

use Craft;
use craft\db\Query;
use craft\web\Controller;
use ether\mc\jobs\SyncProducts;

/**
 * Class SyncController
 *
 * @author  Ether Creative
 * @package ether\mc\controllers
 */
class SyncController extends Controller
{

	public function actionAllProducts ()
	{
		$typeId = Craft::$app->getRequest()->getBodyParam('type');
		$productIdsQuery = (new Query())
			->select('id')
			->from('{{%commerce_products}}');

		if ($typeId)
			$productIdsQuery->where(['typeId' => $typeId]);

		Craft::$app->getQueue()->push(
			new SyncProducts([
				'productIds' => $productIdsQuery->column(),
			])
		);
	}

}