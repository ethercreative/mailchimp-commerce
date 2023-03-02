<?php

/**
 * Mailchimp for Craft Commerce
 *
 * @link      https://crankdcreative.co.uk
 * @copyright Copyright (c) 2023 Crankd Creative
 */

namespace crankd\mc\controllers;

use Craft;
use Throwable;
use yii\web\Response;
use yii\base\Exception;
use craft\web\Controller;
use crankd\mc\MailchimpCommerce;
use yii\web\ForbiddenHttpException;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;
use craft\errors\SiteNotFoundException;
use craft\errors\ElementNotFoundException;

/**
 * Class StoreController
 *
 * @author  Crankd Creative
 * @package crankd\mc\controllers
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
	public function actionCreate()
	{
		$this->requireAdmin();
		$this->requirePostRequest();

		$listId = Craft::$app->getRequest()->getRequiredBodyParam('listId');

		// $listId = "192fc81395";

		// $id = MailchimpCommerce::$i->getSettings();
		// dd($id);

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
	public function actionDisconnect()
	{
		$this->requireAdmin();
		$this->requirePostRequest();

		MailchimpCommerce::$i->store->delete();

		return $this->redirect('mailchimp-commerce/connect');
	}
}
