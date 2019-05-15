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

/**
 * Class OrderController
 *
 * @author  Ether Creative
 * @package ether\mc\controllers
 */
class OrderController extends Controller
{

	public function actionRestore ()
	{
		$number = Craft::$app->getRequest()->getRequiredBodyParam('number');

		// TODO: Set order to be active cart, redirect to checkout
		//   (will need setting for checkout url)
	}

}