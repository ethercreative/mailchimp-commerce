<?php

/**
 * Mailchimp for Craft Commerce
 *
 * @link      https://crankdcreative.co.uk
 * @copyright Copyright (c) 2023 Crankd Creative
 */

namespace crankd\mc\events;

use crankd\mc\base\MailchimpProduct;
use yii\base\Event;

/**
 * Class RegisterMailchimpProductsEvent
 *
 * @author  Crankd Creative
 * @package crankd\mc\events
 */
class RegisterMailchimpProductsEvent extends Event
{

	/**
	 * @var MailchimpProduct[] An array of mailchimp products
	 */
	public $products = [];
}
