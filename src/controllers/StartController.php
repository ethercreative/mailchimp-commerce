<?php
/**
 * Mailchimp for Craft Commerce
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\mc\controllers;

use Craft;
use craft\web\Controller;
use ether\mc\MailchimpCommerce;

/**
 * Class StartController
 *
 * @author  Ether Creative
 * @package ether\mc\controllers
 */
class StartController extends Controller
{

	public function actionIndex ()
	{
		return $this->renderTemplate('mailchimp-commerce/_start/index', [
			'settings' => MailchimpCommerce::$i->getSettings(),
		]);
	}

	public function actionFinish ()
	{
		return $this->renderTemplate('mailchimp-commerce/_start/finish', [
			'lists' => MailchimpCommerce::$i->lists->all(),
		]);
	}

	public function actionComplete ()
	{
		$this->requireAdmin();
		$this->requirePostRequest();

		$listId = Craft::$app->getRequest()->getRequiredBodyParam('listId');

		MailchimpCommerce::$i->store->create($listId);

		return 'show complete screen or error';
	}

}