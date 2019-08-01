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
use craft\commerce\elements\Product;
use craft\commerce\elements\Variant;
use craft\commerce\Plugin as Commerce;
use craft\db\Query;
use craft\helpers\Json;
use craft\helpers\UrlHelper;
use ether\mc\base\MailchimpProduct;
use ether\mc\events\RegisterMailchimpProductsEvent;
use ether\mc\MailchimpCommerce;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

/**
 * Class ChimpService
 *
 * @author  Ether Creative
 * @package ether\mc\services
 */
class ChimpService extends Component
{

	// Events
	// =========================================================================

	// Constants
	// -------------------------------------------------------------------------

	/**
	 * @event RegisterMailchimpProductsEvent The event that is triggered when
	 *   registering new Mailchimp products
	 *
	 * Event::on(
	 *     \ether\mc\services\ChimpService::class,
	 *     \ether\mc\services\ChimpService::EVENT_REGISTER_MAILCHIMP_PRODUCTS,
	 *     function (RegisterMailchimpProductsEvent $event) {
	 *         $event->products[] = new \ether\mc\base\MailchimpProduct([
	 *             // See ChimpService::getProducts() for examples
	 *         ]);
	 *     }
	 * );
	 */
	const EVENT_REGISTER_MAILCHIMP_PRODUCTS = 'mcRegisterProducts';

	// Methods
	// -------------------------------------------------------------------------

	public function getProducts ()
	{
		static $products;

		if ($products)
			return $products;

		$products = [];

		$plugins = Craft::$app->getPlugins();

		try
		{
			if (class_exists(Commerce::class) && $plugins->isPluginEnabled('commerce'))
			{
				$products[] = new MailchimpProduct([
					'productName'                   => Craft::t('commerce', 'Products'),
					'variantName'                   => Craft::t('commerce', 'Variants'),
					'productClass'                  => Product::class,
					'variantClass'                  => Variant::class,
					'variantToProductMethod'        => 'getProduct',
					'productToVariantMethod'        => 'getVariants',
					'variantStockProperty'          => 'stock',
					'variantUnlimitedStockProperty' => 'hasUnlimitedStock',
					'productToTypeMethod'           => 'getType',
					'getProductTypes'               => function () {
						return Commerce::getInstance()->getProductTypes()
						               ->getAllProductTypes();
					},
					'getProductIds'                 => function ($typeId) {
						$productIdsQuery = (new Query())
							->select('[[p.id]]')
							->leftJoin(
								'{{%elements}} e', '[[e.id]] = [[p.id]]'
							)
							->where(['e.dateDeleted' => null])
							->from('{{%commerce_products}} p');

						if ($typeId)
							$productIdsQuery->andWhere(
								['p.typeId' => $typeId]
							);

						return $productIdsQuery->column();
					},
				]);
			}

			/** @noinspection PhpFullyQualifiedNameUsageInspection */
			if (class_exists(\verbb\events\elements\Event::class) && $plugins->isPluginEnabled('events'))
			{
				/** @noinspection PhpFullyQualifiedNameUsageInspection */
				$products[] = new MailchimpProduct([
					'productName'                   => Craft::t('events', 'Events'),
					'variantName'                   => Craft::t('events', 'Tickets'),
					'productClass'                  => \verbb\events\elements\Event::class,
					'variantClass'                  => \verbb\events\elements\Ticket::class,
					'variantToProductMethod'        => 'getEvent',
					'productToVariantMethod'        => 'getTickets',
					'variantStockProperty'          => 'quantity',
					'variantUnlimitedStockProperty' => null,
					'productToTypeMethod'           => 'getType',
					'getProductTypes'               => function () {
						/** @noinspection PhpFullyQualifiedNameUsageInspection */
						/** @var \verbb\events\services\EventTypes $service */
						$service = \verbb\events\Events::getInstance()
						                               ->getEventTypes();

						return $service->getAllEventTypes();
					},
					'getProductIds'                 => function ($typeId) {
							$productIdsQuery = (new Query())
								->select('[[p.id]]')
								->leftJoin(
									'{{%elements}} e', '[[e.id]] = [[p.id]]'
								)
								->where(['e.dateDeleted' => null])
								->from('{{%events_events}} p');

							if ($typeId)
								$productIdsQuery->where(
									['p.typeId' => $typeId]
								);

							return $productIdsQuery->column();
						},
				]);
			}
		} catch (Exception $e) {
			Craft::error($e->getMessage(), 'mailchimp-commerce');
		}

		$event = new RegisterMailchimpProductsEvent([
			'products' => $products,
		]);
		$this->trigger(self::EVENT_REGISTER_MAILCHIMP_PRODUCTS, $event);

		return $products = $event->products;
	}

	// Request
	// =========================================================================

	// Properties
	// -------------------------------------------------------------------------

	private static $_client;

	// Methods
	// -------------------------------------------------------------------------

	// Methods: Public
	// -------------------------------------------------------------------------

	public function get ($uri, $params = [])
	{
		$uri = UrlHelper::urlWithParams($uri, $params);
		return $this->request('GET', $uri);
	}

	public function post ($uri, $body = [])
	{
		return $this->request('POST', $uri, $body);
	}

	public function patch ($uri, $body = [])
	{
		return $this->request('PATCH', $uri, $body);
	}

	public function put ($uri, $body = [])
	{
		return $this->request('PUT', $uri, $body);
	}

	public function delete ($uri)
	{
		return $this->request('DELETE', $uri);
	}

	// Methods: Private
	// -------------------------------------------------------------------------

	private function request ($method, $uri, $body = [])
	{
		$client = $this->client();

		if (!$client)
		{
			return [
				false, // Success
				null, // Data
				'Missing API key', // Error

				'success' => false,
				'data'    => null,
				'error'   => 'Missing API key',
			];
		}

		try {
			/** @noinspection PhpUnhandledExceptionInspection */
			$res = $client->request($method, $uri, [
				'json' => $body,
			]);

			$data = Json::decodeIfJson($res->getBody(), true);

			return [
				true, // Success
				$data, // Data
				null, // Error

				'success' => true,
				'data' => $data,
				'error' => null,
			];
		} catch (ClientException $e) {
			$err = $e->getResponse()->getBody()->getContents();

			Craft::debug(compact('method', 'uri', 'body'));

			return [
				false, // Success
				null, // Data
				$err, // Error

				'success' => false,
				'data' => null,
				'error'   => $err,
			];
		}
	}

	private function client ()
	{
		if (self::$_client)
			return self::$_client;

		$settings = MailchimpCommerce::$i->getSettings();

		$apiKey     = Craft::parseEnv($settings->apiKey);
		$dataCenter = $settings->getDataCenter();

		if (!$apiKey)
			return null;

		return self::$_client = new Client([
			'base_uri' => 'https://' . $dataCenter . '.api.mailchimp.com/3.0/',
			'auth' => ['MailchimpCommerce', $apiKey],
		]);
	}

}
