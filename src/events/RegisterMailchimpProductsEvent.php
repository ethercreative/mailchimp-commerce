<?php
/**
 * Mailchimp for Craft Commerce
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\mc\events;

use ether\mc\base\MailchimpProduct;
use yii\base\Event;

/**
 * Class RegisterMailchimpProductsEvent
 *
 * @author  Ether Creative
 * @package ether\mc\events
 */
class RegisterMailchimpProductsEvent extends Event
{

	/**
	 * @var MailchimpProduct[] An array of mailchimp products
	 */
	public $products = [];

}
