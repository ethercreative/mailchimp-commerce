<?php
/**
 * Mailchimp for Craft Commerce
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\mc\models;

use Craft;
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
	 * @var bool If true, all syncing will be disabled (useful for staging environments)
	 */
	public $disableSyncing = false;

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

	/**
	 * @var string The URL to redirect to after restoring an abandoned cart.
	 */
	public $abandonedCartRestoreUrl;

	/**
	 * @var string The error notice sent when an abandoned cart has expired.
	 */
	public $expiredCartError = 'Your cart has expired!';

	/**
	 * @var string The error notice sent when an abandoned cart that has
	 *             already been completed is attempted to be restored.
	 */
	public $completedCartError = 'You\'ve already completed this order!';

	/**
	 * @var string The success notice sent when an abandoned cart is restored.
	 */
	public $cartRestoredNotice = 'Your cart has been restored!';

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

		$parts = explode('-', Craft::parseEnv($this->apiKey));
		return end($parts);
	}

}
