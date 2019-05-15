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

	// Mailchimp
	// -------------------------------------------------------------------------

	/**
	 * @var string Your Mailchimp API key
	 * @see https://mailchimp.com/help/about-api-keys/
	 */
	public $apiKey;

	/**
	 * @var string A unique store ID
	 * @internal Set by the plugin on install, DO NOT MODIFY
	 */
	public $storeId;

	/**
	 * @var string The Mailchimp list ID the store is set to
	 * @internal Set on initial sync, DO NOT MODIFY.
	 */
	public $listId;

	// Products
	// -------------------------------------------------------------------------

	/**
	 * @var array An array of [productTypeUid => vendorFieldUid]
	 */
	public $productVendorFields = [];

	/**
	 * @var array An array of [productTypeUid => descriptionFieldUid]
	 */
	public $productDescriptionFields = [];

	/**
	 * @var array An array of [productTypeUid => thumbnailFieldUid]
	 */
	public $productThumbnailFields = [];

	/**
	 * @var array An array of [productTypeUid => imageFieldUid]
	 */
	public $productImageFields = [];

	/**
	 * @var array An array of [productTypeUid => thumbnailFieldUid]
	 */
	public $variantThumbnailFields = [];

	/**
	 * @var array An array of [productTypeUid => imageFieldUid]
	 */
	public $variantImageFields = [];

	// Methods
	// =========================================================================

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