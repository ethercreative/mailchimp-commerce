<?php
/**
 * Mailchimp for Craft Commerce
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\mc\services;

use craft\base\Component;
use ether\mc\MailchimpCommerce;

/**
 * Class ListsService
 *
 * @author  Ether Creative
 * @package ether\mc\services
 */
class ListsService extends Component
{

	public function all ()
	{
		list($success, $data) = MailchimpCommerce::$i->chimp->get('lists', [
			'fields' => 'lists.id,lists.name',
			'count' => 1000000,
		]);

		if (!$success)
			return [];

		return array_map(function ($item) {
			return [
				'label' => $item['name'],
				'value' => $item['id'],
			];
		}, $data['lists']);
	}

}