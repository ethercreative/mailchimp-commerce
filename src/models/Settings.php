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

	/**
	 * @var string The UID of the field to use for opt-in. MUST be a lightswitch.
	 */
	public $optInField;

	// Commerce
	// -------------------------------------------------------------------------

	/**
	 * @var string The handle of the status meaning the order has been shipped
	 */
	public $shippedStatusHandle = 'shipped';

	/**
	 * @var string The transform UID to use when transforming thumbnails
	 */
	public $thumbnailTransform;

	/**
	 * @var string The transform UID to use when transforming images
	 */
	public $imageTransform;

	/**
	 * @var string The URL to use for promo redemption's, i.e.
	 *             `/cart?discount={code}`, defaults to site index.
	 */
	public $promoRedemptionUrl;

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