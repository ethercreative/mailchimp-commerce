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
use craft\helpers\Json;
use craft\helpers\UrlHelper;
use ether\mc\MailchimpCommerce;
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

	// Properties
	// =========================================================================

	private static $_client;

	// Methods
	// =========================================================================

	// Public
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

	// Private
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
			return [
				false, // Success
				null, // Data
				$e->getResponse()->getBody()->getContents(), // Error

				'success' => false,
				'data' => null,
				'error'   => $e->getResponse()->getBody()->getContents(),
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
