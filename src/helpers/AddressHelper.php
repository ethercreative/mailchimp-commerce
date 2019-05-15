<?php
/**
 * Mailchimp for Craft Commerce
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\mc\helpers;

use craft\commerce\models\Address;

/**
 * Class AddressHelper
 *
 * @author  Ether Creative
 * @package ether\mc\helpers
 */
abstract class AddressHelper
{

	public static function asArray (Address $address = null)
	{
		if ($address === null)
		{
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

		return [
			'address1'     => $address->address1,
			'address2'     => $address->address2,
			'city'         => $address->city,
			'province'     => $address->stateText,
			'postal_code'  => $address->zipCode,
			'country'      => $address->countryText,
			'country_code' => $address->country->iso,
		];
	}

}