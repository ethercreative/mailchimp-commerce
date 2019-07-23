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
use craft\db\Query;
use craft\elements\Asset;
use craft\errors\MissingComponentException;
use craft\errors\SiteNotFoundException;
use craft\helpers\Db;
use craft\helpers\UrlHelper;
use DateTime;
use ether\mc\MailchimpCommerce;
use yii\base\InvalidConfigException;
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
	 * @throws InvalidConfigException
	 */
	public function syncProductById ($productId)
	{
		if (MailchimpCommerce::getInstance()->getSettings()->disableSyncing)
			return true;

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
		if (MailchimpCommerce::getInstance()->getSettings()->disableSyncing)
			return;

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

	/**
	 * Gets the last synced date of the given product
	 *
	 * @param $productId
	 *
	 * @return DateTime|string
	 * @throws \Exception
	 */
	public function getLastSyncedById ($productId)
	{
		$date = (new Query())
			->select('lastSynced')
			->from('{{%mc_products_synced}}')
			->where(['productId' => $productId])
			->scalar();

		if ($date)
			return Craft::$app->getFormatter()->asDatetime($date, 'short');

		return MailchimpCommerce::t('Never');
	}

	/**
	 * Will return the products from Mailchimp
	 *
	 * @param int $offset
	 *
	 * @return array
	 * @throws MissingComponentException
	 */
	public function getSyncedFromMailchimp ($offset = 0)
	{
		$storeId = MailchimpCommerce::$i->getSettings()->storeId;

		list($success, $data, $error) = MailchimpCommerce::$i->chimp->get(
			'ecommerce/stores/' . $storeId . '/products',
			[
				'count' => MailchimpCommerce::OFFSET_LIMIT,
				'offset' => $offset,
			]
		);

		if (!$success)
		{
			Craft::error($error, 'mailchimp-commerce');
			Craft::$app->getSession()->setError('An error occurred, please check the log');
			return [
				'items' => [],
				'total' => 0,
			];
		}

		return [
			'items' => $data['products'],
			'total' => $data['total_items'],
		];
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
	 * @throws InvalidConfigException
	 * @throws \Exception
	 */
	private function _buildProductData ($productId)
	{
		/** @var Element $product */
		$product = Craft::$app->getElements()->getElementById($productId);

		if (!$product)
			throw new \Exception('Unable to find element with ID: ' . $productId);

		// TODO: Tidy up all helper functions by getting and storing the correct
		//  product and variant types, and using them later (rather that
		//  re-checking in every function for every variant).

		$data = [
			'id' => (string) $product->id,
			'title' => $product->title,
			'handle' => $product->slug,
			'url' => $product->url,
			'description' => $this->_getProductDescription($product),
			'type' => $this->_getType($product)->name,
			'vendor' => $this->_getProductVendor($product),
			'image_url' => $this->_getThumbnail($product),
			'variants' => [],
			'images' => $this->_getProductImages($product),
			'published_at_foreign' => $product->dateCreated->format('c'),
		];

		foreach ($this->_getVariants($product) as $variant)
		{
			$unlimited = $this->_getUnlimitedStock($variant);
			$stock = $this->_getStock($variant);

			$data['variants'][] = [
				'id' => (string) $variant->id,
				'title' => $variant->title,
				'url' => $variant->url ?: $product->url,
				'sku' => $variant->sku,
				'price' => (float) $variant->price,
				'inventory_quantity' => (int) ($unlimited ? PHP_INT_MAX : $stock),
				'image_url' => $this->_getThumbnail($variant, $product),
				'visibility' => (string) $variant->enabled,
			];
		}

		return $data;
	}

	/**
	 * @param Element $product
	 *
	 * @return string|null
	 * @throws SiteNotFoundException
	 * @throws InvalidConfigException
	 */
	private function _getProductVendor (Element $product)
	{
		return MailchimpCommerce::$i->fields->getMappedFieldValue(
			'productVendorFields',
			$product,
			$this->_getType($product)->uid,
			MailchimpCommerce::$i->store->getStoreName()
		);
	}

	/**
	 * @param Element $product
	 *
	 * @return string|null
	 * @throws InvalidConfigException
	 */
	private function _getProductDescription (Element $product)
	{
		return MailchimpCommerce::$i->fields->getMappedFieldValue(
			'productDescriptionFields',
			$product,
			$this->_getType($product)->uid,
			''
		);
	}

	/**
	 * @param Element $product
	 *
	 * @return array
	 * @throws InvalidConfigException
	 */
	private function _getProductImages (Element $product)
	{
		$images = $this->_getImages($product);

		foreach ($this->_getVariants($product) as $variant)
			$images = array_merge($images, $this->_getImages($variant));

		return $images;
	}

	/**
	 * Gets the thumbnail for the given element
	 *
	 * @param Element      $element
	 * @param Element|null $fallback
	 *
	 * @return string|null
	 * @throws InvalidConfigException
	 * @throws \yii\base\Exception
	 */
	private function _getThumbnail (Element $element = null, Element $fallback = null)
	{
		if ($element === null)
			return '';

		$isVariant = $this->_getIsVariant($element);
		$field = MailchimpCommerce::$i->fields->getMappedFieldRelation(
			$isVariant ? 'variantThumbnailFields' : 'productThumbnailFields',
			$element,
			$this->_getType($element, $isVariant)->uid
		);

		if (!$field)
			return $this->_getThumbnail($fallback);

		/** @var Asset $thumbnail */
		$thumbnail = $field->one();

		if ($thumbnail)
		{
			$transform = MailchimpCommerce::$i->getSettings()->thumbnailTransform;

			if ($transform)
				$transform = Craft::$app->getAssetTransforms()->getTransformByUid($transform);

			if (!$transform)
				$transform = ['width'  => 1000, 'height' => 1000];

			return UrlHelper::siteUrl($thumbnail->getUrl($transform));
		}

		return $this->_getThumbnail($fallback);
	}

	/**
	 * Gets the images from the given element
	 *
	 * @param Element $element
	 *
	 * @return array
	 * @throws InvalidConfigException
	 */
	private function _getImages (Element $element)
	{
		$isVariant = $this->_getIsVariant($element);
		$field = MailchimpCommerce::$i->fields->getMappedFieldRelation(
			$isVariant ? 'variantImageFields' : 'productImageFields',
			$element,
			$this->_getType($element, $isVariant)->uid
		);

		if (!$field)
			return [];

		$transform = MailchimpCommerce::$i->getSettings()->thumbnailTransform;

		if ($transform)
			$transform = Craft::$app->getAssetTransforms()->getTransformByUid($transform);

		if (!$transform)
			$transform = ['width' => 1000, 'mode' => 'fit'];

		return array_map(function (Asset $asset) use ($isVariant, $element, $transform) {
			return [
				'id' => (string) $asset->id,
				'url' => UrlHelper::siteUrl($asset->getUrl($transform)),
				'variant_ids' => $isVariant ? [$element->id] : [],
			];
		}, $field->all());
	}

	/**
	 * @param $product
	 *
	 * @return array|Variant[]
	 */
	private function _getVariants ($product)
	{
		$mailchimpProducts = MailchimpCommerce::getInstance()->chimp->getProducts();

		foreach ($mailchimpProducts as $mcProduct)
		{
			if ($product instanceof $mcProduct->productClass)
			{
				$callable = [$product, $mcProduct->productToVariantMethod];

				return $callable();
			}
		}

		/** @var Product $product */
		return [];
	}

	/**
	 * @param $purchasable
	 *
	 * @return Product|null
	 * @throws InvalidConfigException
	 */
	private function _getProductFromVariant ($purchasable)
	{
		$mailchimpProducts = MailchimpCommerce::getInstance()->chimp->getProducts();

		foreach ($mailchimpProducts as $product)
		{
			if ($purchasable instanceof $product->variantClass)
			{
				$callable = [$purchasable, $product->variantToProductMethod];

				return $callable();
			}
		}

		/** @var Variant $purchasable */
		return $purchasable->getProduct();
	}

	/**
	 * @param $variant
	 *
	 * @return int
	 */
	private function _getStock ($variant)
	{
		$mailchimpProducts = MailchimpCommerce::getInstance()->chimp->getProducts();

		foreach ($mailchimpProducts as $mcProduct)
			if ($variant instanceof $mcProduct->variantClass)
				return $variant->{$mcProduct->variantStockProperty};

		return 0;
	}

	/**
	 * @param $variant
	 *
	 * @return int
	 */
	private function _getUnlimitedStock ($variant)
	{
		$mailchimpProducts = MailchimpCommerce::getInstance()->chimp->getProducts();

		foreach ($mailchimpProducts as $mcProduct)
			if ($variant instanceof $mcProduct->variantClass)
				if ($mcProduct->variantUnlimitedStockProperty !== null)
					return $variant->{$mcProduct->variantUnlimitedStockProperty};

		return false;
	}

	/**
	 * @param $element
	 *
	 * @return bool
	 */
	private function _getIsVariant ($element)
	{
		$mailchimpProducts = MailchimpCommerce::getInstance()->chimp->getProducts();

		foreach ($mailchimpProducts as $product)
			if ($element instanceof $product->variantClass)
				return true;

		return false;
	}

	/**
	 * @param $element
	 * @param $isVariant
	 *
	 * @return mixed
	 * @throws InvalidConfigException
	 */
	private function _getType ($element, $isVariant = false)
	{
		if ($isVariant)
			$element = $this->_getProductFromVariant($element);

		$mailchimpProducts = MailchimpCommerce::getInstance()->chimp->getProducts();

		foreach ($mailchimpProducts as $product)
		{
			if ($element instanceof $product->variantClass)
			{
				$callable = [$element, $product->productToTypeMethod];

				return $callable();
			}
		}

		/** @var Product $element */
		return $element->getType();
	}

}
