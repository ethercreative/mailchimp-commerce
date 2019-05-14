<?php
/**
 * Mailchimp for Craft Commerce
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\mc\services;

use Craft;
use craft\base\Component;
use craft\commerce\Plugin as Commerce;
use craft\helpers\App;
use ether\mc\MailchimpCommerce;

/**
 * Class StoreService
 *
 * @author  Ether Creative
 * @package ether\mc\services
 */
class StoreService extends Component
{

	public function create ($listId)
	{
		$commerceSettings = Commerce::getInstance()->getSettings();
		$primarySite = Craft::$app->getSites()->getPrimarySite();

		$id = MailchimpCommerce::$i->getSettings()->storeId;

		$storeData = [
			'id' => $id,
			'list_id' => $listId,
			'name' => $this->_getStoreName(),
			// TODO: Update domain if changes (i.e. moving to production)
			'domain' => $primarySite->getBaseUrl(),
			'email' => $this->_getStoreEmail(),
			'currency_code' => $commerceSettings->getPaymentCurrency(), // FIXME
		];

		Craft::dd($storeData);

		list($success, $data) = MailchimpCommerce::$i->chimp->post(
			'ecommerce/stores'
		);
	}

	// Helpers
	// =========================================================================

	private function _getStoreName ()
	{
		$commerceSettings = Commerce::getInstance()->getSettings();
		$primarySite = Craft::$app->getSites()->getPrimarySite();

		return $commerceSettings->emailSenderName
			?: $commerceSettings->emailSenderNamePlaceholder
			?: $primarySite->name;
	}

	private function _getStoreEmail ()
	{
		$commerceSettings = Commerce::getInstance()->getSettings();

		return $commerceSettings->emailSenderAddress
			?: $commerceSettings->emailSenderAddressPlaceholder
			?: App::mailSettings()->fromEmail;
	}

}