<?php

/**
 * Mailchimp for Craft Commerce
 *
 * @link      https://crankdcreative.co.uk
 * @copyright Copyright (c) 2023 Crankd Creative
 */

namespace crankd\mc\controllers;

use Craft;
use craft\errors\MissingComponentException;
use craft\web\Controller;
use crankd\mc\MailchimpCommerce;
use yii\web\Response;

/**
 * Class SyncedController
 *
 * @author  Crankd Creative
 * @package crankd\mc\controllers
 */
class SyncedController extends Controller
{

	/**
	 * @return Response
	 * @throws MissingComponentException
	 */
	public function actionProducts()
	{
		$offset = Craft::$app->getRequest()->getQueryParam('offset', 0);

		$data = MailchimpCommerce::$i->products->getSyncedFromMailchimp($offset);

		return $this->renderTemplate('mailchimp-commerce/_synced/products', [
			'settings' => MailchimpCommerce::$i->getSettings(),
			'offsetLimit' => MailchimpCommerce::OFFSET_LIMIT,
			'items' => $data['items'],
			'offset' => $offset,
			'total' => $data['total'],
		]);
	}
}
