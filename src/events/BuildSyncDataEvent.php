<?php
/**
 * Mailchimp for Craft Commerce
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2020 Ether Creative
 */

namespace ether\mc\events;

use craft\base\Element;
use yii\base\Event;

/**
 * Class BuildSyncDataEvent
 *
 * @author  Ether Creative
 * @package ether\mc\events
 */
class BuildSyncDataEvent extends Event
{

	/** @var Element */
	public $element;

	/** @var array */
	public $syncData;

}
