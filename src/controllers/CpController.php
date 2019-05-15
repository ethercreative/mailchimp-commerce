<?php
/**
 * Mailchimp for Craft Commerce
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\mc\controllers;

use Craft;
use craft\base\Field;
use craft\commerce\Plugin as Commerce;
use craft\fields\Assets;
use craft\models\FieldGroup;
use craft\web\Controller;
use ether\mc\MailchimpCommerce;

/**
 * Class CpController
 *
 * @author  Ether Creative
 * @package ether\mc\controllers
 */
class CpController extends Controller
{

	public function actionIndex ()
	{
		$settings = MailchimpCommerce::$i->getSettings();

		if ($settings->apiKey && $settings->listId)
			return $this->redirect('mailchimp-commerce/sync');

		return $this->redirect('mailchimp-commerce/connect');
	}

	public function actionConnect ()
	{
		return $this->renderTemplate('mailchimp-commerce/_connect', [
			'settings' => MailchimpCommerce::$i->getSettings(),
		]);
	}

	public function actionList ()
	{
		return $this->renderTemplate('mailchimp-commerce/_list', [
			'settings' => MailchimpCommerce::$i->getSettings(),
			'lists' => MailchimpCommerce::$i->lists->all(),
		]);
	}

	public function actionSync ()
	{
		$i = MailchimpCommerce::$i;

		return $this->renderTemplate('mailchimp-commerce/_sync', [
			'settings' => $i->getSettings(),
			'totalProductsSynced' => $i->products->getTotalProductsSynced(),
		]);
	}

	public function actionMappings ()
	{
		$productTypes = Commerce::getInstance()->getProductTypes()->getAllProductTypes();
		$fields = array_reduce(
			Craft::$app->getFields()->getAllGroups(),
			function (array $a, FieldGroup $group) {
				$a[] = [
					'optgroup' => $group->name,
				];

				/** @var Field $field */
				foreach ($group->getFields() as $field)
				{
					$a[] = [
						'label' => $field->name,
						'value' => $field->uid,
					];
				}

				return $a;
			},
			[
				['label' => MailchimpCommerce::t('None'), 'value' => ''],
			]
		);
		$assetFields = array_reduce(
			Craft::$app->getFields()->getAllGroups(),
			function (array $a, FieldGroup $group) {
				$fields = [];

				/** @var Field $field */
				foreach ($group->getFields() as $field)
				{
					if (!($field instanceof Assets))
						continue;

					$fields[] = [
						'label' => $field->name,
						'value' => $field->uid,
					];
				}

				if (empty($fields))
					return $a;

				$a[] = [
					'optgroup' => $group->name,
				];

				return array_merge($a, $fields);
			},
			[
				['label' => MailchimpCommerce::t('None'), 'value' => ''],
			]
		);

		return $this->renderTemplate('mailchimp-commerce/_mappings', [
			'settings' => MailchimpCommerce::$i->getSettings(),
			'productTypes' => $productTypes,
			'fields' => $fields,
			'assetFields' => $assetFields,
		]);
	}

}