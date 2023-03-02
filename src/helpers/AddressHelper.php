<?php

/**
 * Mailchimp for Craft Commerce
 *
 * @link      https://crankdcreative.co.uk
 * @copyright Copyright (c) 2023 Crankd Creative
 */

namespace crankd\mc\helpers;

use Craft;
use craft\elements\Address;

/**
 * Class AddressHelper
 *
 * @author  Crankd Creative
 * @package crankd\mc\helpers
 */
abstract class AddressHelper
{

	public static function asArray(Address $address = null)
	{
		if ($address === null) {
			return [
				'address1'     => '',
				'address2'     => '',
				'city'         => '',
				'province'     => '',
				'postal_code'  => '',
				'country'      => '',
				'country_code' => '',
			];
		}

		$country = Craft::$app->getAddresses()->countryRepository->get($address->countryCode);

		return [
			'address1'     => $address->addressLine1,
			'address2'     => $address->addressLine2,
			'city'         => $address->locality,
			'province'     => $address->dependentLocality,
			'postal_code'  => $address->postalCode,
			'country'      => $country->getName(),
			'country_code' => $address->countryCode,
		];
	}
}
