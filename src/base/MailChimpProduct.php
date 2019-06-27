<?php
/**
 * Mailchimp for Craft Commerce
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\mc\base;

use Yii;

/**
 * Class MailchimpProduct
 *
 * @author  Ether Creative
 * @package ether\mc\base
 */
class MailchimpProduct
{

	// Properties
	// =========================================================================

	/**
	 * @var string What this "product" should be called (i.e. product, event)
	 */
	public $productName = 'Product';

	/**
	 * @var string What this "variant" should be called (i.e. variant, ticket)
	 */
	public $variantName = 'Variant';

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

	// TODO: How do we get product types?
	// TODO: What happens if the purchasable IS the product?

	// Methods
	// =========================================================================

	public function __construct ($config = [])
	{
		if (!empty($config))
			Yii::configure($this, $config);
	}

}
