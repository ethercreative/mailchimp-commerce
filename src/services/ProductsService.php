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
use craft\base\Element;
use craft\commerce\elements\Product;
use craft\commerce\elements\Variant;
use craft\commerce\Plugin as Commerce;
use craft\db\Query;
use craft\elements\Asset;
use craft\errors\SiteNotFoundException;
use craft\helpers\Db;
use DateTime;
use ether\mc\MailchimpCommerce;
use yii\db\Exception;

/**
 * Class ProductsService
 *
 * @author  Ether Creative
 * @package ether\mc\services
 */
class ProductsService extends Component
{

	// Public
	// =========================================================================

	/**
	 * Syncs the product from the given ID to Mailchimp
	 *
	 * @param $productId
	 *
	 * @return bool
	 * @throws Exception
	 * @throws SiteNotFoundException
	 */
	public function syncProductById ($productId)
	{
		$hasBeenSynced = $this->_hasProductBeenSynced($productId);
		$data = $this->_buildProductData($productId);

		if ($hasBeenSynced)
			return $this->_updateProduct($productId, $data);
		else
			return $this->_createProduct($productId, $data);
	}

	/**
	 * Delete the product from Mailchimp from the given product ID
	 *
	 * @param $productId
	 *
	 * @return bool|void
	 * @throws Exception
	 */
	public function deleteProductById ($productId)
	{
		if (!$this->_hasProductBeenSynced($productId))
			return;

		$storeId = MailchimpCommerce::$i->getSettings()->storeId;

		list($success, $data, $error) = MailchimpCommerce::$i->chimp->delete(
			'ecommerce/stores/' . $storeId . '/products/' . $productId
		);

		if (!$success)
		{
			Craft::error($error, 'mailchimp-commerce');
			return false;
		}

		Craft::$app->getDb()->createCommand()
			->delete('{{%mc_products_synced}}', [
				'productId' => $productId,
			])->execute();

		return true;
	}

	/**
	 * Returns the total number of products synced
	 *
	 * @return int|string
	 */
	public function getTotalProductsSynced ()
	{
		return (new Query())
			->from('{{%mc_products_synced}}')
			->count();
	}

	// Private
	// =========================================================================

	/**
	 * Creates the product in Mailchimp
	 *
	 * @param $productId
	 * @param $data
	 *
	 * @return bool
	 * @throws Exception
	 */
	private function _createProduct ($productId, $data)
	{
		$storeId = MailchimpCommerce::$i->getSettings()->storeId;

		list($success, $data, $error) = MailchimpCommerce::$i->chimp->post(
			'ecommerce/stores/' . $storeId . '/products',
			$data
		);

		if (!$success)
		{
			Craft::error($error, 'mailchimp-commerce');
			return false;
		}

		Craft::$app->getDb()->createCommand()
			->insert(
				'{{%mc_products_synced}}',
				[
					'productId' => $productId,
					'lastSynced' => Db::prepareDateForDb(new DateTime())
				],
				false
			)->execute();

		return true;
	}

	/**
	 * Updates the product in Mailchimp
	 *
	 * @param $productId
	 * @param $data
	 *
	 * @return bool
	 * @throws Exception
	 */
	private function _updateProduct ($productId, $data)
	{
		$storeId = MailchimpCommerce::$i->getSettings()->storeId;

		list($success, $data, $error) = MailchimpCommerce::$i->chimp->patch(
			'ecommerce/stores/' . $storeId . '/products/' . $productId,
			$data
		);

		if (!$success)
		{
			Craft::error($error, 'mailchimp-commerce');
			return false;
		}

		Craft::$app->getDb()->createCommand()
			->update(
				'{{%mc_products_synced}}',
				[ 'lastSynced' => Db::prepareDateForDb(new DateTime()) ],
				[ 'productId' => $productId ],
				[],
				false
			)->execute();

		return true;
	}

	// Helpers
	// =========================================================================

	/**
	 * Checks if the given product ID has been synced
	 *
	 * @param $productId
	 *
	 * @return bool
	 */
	private function _hasProductBeenSynced ($productId)
	{
		return (new Query())
			->from('{{%mc_products_synced}}')
			->where(['productId' => $productId])
			->exists();
	}

	/**
	 * Builds the product data from the given product ID
	 *
	 * @param $productId
	 *
	 * @return array
	 * @throws SiteNotFoundException
	 */
	private function _buildProductData ($productId)
	{
		$product = Commerce::getInstance()->getProducts()->getProductById($productId);

		$data = [
			'id' => (string) $product->id,
			'title' => $product->title,
			'handle' => $product->slug,
			'url' => $product->url,
			'description' => $this->_getProductDescription($product),
			'type' => $product->type->name,
			'vendor' => $this->_getProductVendor($product),
			'image_url' => $this->_getThumbnail($product),
			'variants' => [],
			'images' => $this->_getProductImages($product),
			'published_at_foreign' => Db::prepareDateForDb($product->postDate),
		];

		foreach ($product->variants as $variant)
		{
			$data['variants'][] = [
				'id' => (string) $variant->id,
				'title' => $variant->title,
				'url' => $variant->url ?: $product->url,
				'sku' => $variant->sku,
				'price' => $variant->price,
				'inventory_quantity' => $variant->hasUnlimitedStock ? PHP_INT_MAX : $variant->stock,
				'image_url' => $this->_getThumbnail($variant, $product),
				'visibility' => (string) $variant->enabled,
			];
		}

		return $data;
	}

	/**
	 * @param Product $product
	 *
	 * @return string|null
	 * @throws SiteNotFoundException
	 */
	private function _getProductVendor (Product $product)
	{
		return MailchimpCommerce::$i->fields->getMappedFieldValue(
			'productVendorFields',
			$product,
			$product->type->uid,
			MailchimpCommerce::$i->store->getStoreName()
		);
	}

	private function _getProductDescription (Product $product)
	{
		return MailchimpCommerce::$i->fields->getMappedFieldValue(
			'productDescriptionFields',
			$product,
			$product->type->uid,
			''
		);
	}

	private function _getProductImages (Product $product)
	{
		$images = $this->_getImages($product);

		foreach ($product->variants as $variant)
			$images = array_merge($images, $this->_getImages($variant));

		return $images;
	}

	/**
	 * Gets the thumbnail for the given element
	 *
	 * @param Element      $element
	 * @param Product|null $fallback
	 *
	 * @return string|null
	 */
	private function _getThumbnail (Element $element = null, Product $fallback = null)
	{
		if ($element === null)
			return '';

		$isVariant = $element instanceof Variant;
		$field = MailchimpCommerce::$i->fields->getMappedFieldRelation(
			$isVariant ? 'variantThumbnailFields' : 'productThumbnailFields',
			$element,
			$isVariant ? $element->product->type->uid : $element->type->uid
		);

		if (!$field)
			return $this->_getThumbnail($fallback);

		/** @var Asset $thumbnail */
		$thumbnail = $field->one();

		if ($thumbnail)
		{
			// TODO: Allow this to use a custom Craft image transform
			return $thumbnail->getUrl([
				'width'  => 1000,
				'height' => 1000,
			]);
		}

		return $this->_getThumbnail($fallback);
	}

	/**
	 * Gets the images from the given element
	 *
	 * @param Element $element
	 *
	 * @return array
	 */
	private function _getImages (Element $element)
	{
		$isVariant = $element instanceof Variant;
		$field = MailchimpCommerce::$i->fields->getMappedFieldRelation(
			$isVariant ? 'variantImageFields' : 'productImageFields',
			$element,
			$isVariant ? $element->product->type->uid : $element->type->uid
		);

		if (!$field)
			return [];

		return array_map(function (Asset $asset) use ($isVariant, $element) {
			return [
				'id' => (string) $asset->id,
				// TODO: Make this size customizable
				'url' => $asset->getUrl(['width' => 1000]),
				'variant_ids' => $isVariant ? [$element->id] : [],
			];
		}, $field->all());
	}

}