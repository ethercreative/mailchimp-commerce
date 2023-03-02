<?php

/**
 * Mailchimp for Craft Commerce
 *
 * @link      https://crankdcreative.co.uk
 * @copyright Copyright (c) 2023 Crankd Creative
 */

namespace crankd\mc\base;

use Yii;

/**
 * Class MailchimpProduct
 *
 * @author  Crankd Creative
 * @package crankd\mc\base
 */
class MailchimpProduct
{

	// Properties
	// =========================================================================

	/**
	 * @var string What this "product" should be called (i.e. products, events)
	 */
	public $productName = 'Products';

	/**
	 * @var string What this "variant" should be called (i.e. variants, tickets)
	 */
	public $variantName = 'Variants';

	/**
	 * @var string A valid class reference to the product class
	 */
	public $productClass;

	/**
	 * @var string A valid class reference to the variant class (must be a Purchasable)
	 */
	public $variantClass;

	/**
	 * @var string The name of a method on the variant that can be called to get its parent product.
	 */
	public $variantToProductMethod;

	/**
	 * @var string The name of a method on the product to get its variants.
	 */
	public $productToVariantMethod;

	/**
	 * @var string The name of the property used to get a variants stock
	 */
	public $variantStockProperty;

	/**
	 * @var string|null The name of the property used to check whcrankd the
	 *   variant has unlimited stock
	 */
	public $variantUnlimitedStockProperty = null;

	/**
	 * @var string The method to use to get the type of the product
	 */
	public $productToTypeMethod;

	/**
	 * @var callable A function that returns all the available types for this
	 *   product
	 */
	public $getProductTypes;

	/**
	 * @var callable A function that will return an array of IDs based off the
	 *               type ID passed (can be an empty string)
	 */
	public $getProductIds;

	// Methods
	// =========================================================================

	public function __construct($config = [])
	{
		if (!empty($config))
			Yii::configure($this, $config);
	}
}
