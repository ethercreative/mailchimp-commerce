<?php
/**
 * Mailchimp for Craft Commerce
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\mc\controllers;

use Craft;
use craft\errors\ElementNotFoundException;
use craft\errors\SiteNotFoundException;
use craft\web\Controller;
use ether\mc\MailchimpCommerce;
use Throwable;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

/**
 * Class StoreController
 *
 * @author  Ether Creative
 * @package ether\mc\controllers
 */
class StoreController extends Controller
{

	/**
	 * Creates the Mailchimp store
	 *
	 * @return Response|null
	 * @throws Throwable
	 * @throws ElementNotFoundException
	 * @throws SiteNotFoundException
	 * @throws Exception
	 * @throws InvalidConfigException
	 * @throws BadRequestHttpException
	 * @throws ForbiddenHttpException
	 */
	public function actionCreate ()
	{
		$this->requireAdmin();
		$this->requirePostRequest();

		$listId = Craft::$app->getRequest()->getRequiredBodyParam('listId');

		$success = MailchimpCommerce::$i->store->create($listId);

		if ($success)
			return $this->redirectToPostedUrl();

		Craft::$app->getSession()->setError('Unable to connect store, please check the logs');

		return null;
	}

	/**
	 * @return Response
	 * @throws BadRequestHttpException
	 * @throws ForbiddenHttpException
	 */
	public function actionDisconnect ()
	{
		$this->requireAdmin();
		$this->requirePostRequest();

		MailchimpCommerce::$i->store->delete();

		return $this->redirect('mailchimp-commerce/connect');
	}

}
