<?php
/**
 * Mailchimp for Craft Commerce
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\mc\models;

use craft\base\Model;

/**
 * Class Settings
 *
 * @author  Ether Creative
 * @package ether\mc\models
 */
class Settings extends Model
{

	// Properties
	// =========================================================================

	/**
	 * @var string Your Mailchimp API key
	 * @see https://mailchimp.com/help/about-api-keys/
	 */
	public $apiKey;

	/**
	 * @var string A unique store ID, this is set on install. DO NOT MODIFY.
	 */
	public $storeId;

	/**
	 * Gets the data center from the api key
	 *
	 * @return string|null
	 */
	public function getDataCenter ()
	{
		if (!$this->apiKey)
			return null;

		$parts = explode('-', $this->apiKey);
		return end($parts);
	}

}