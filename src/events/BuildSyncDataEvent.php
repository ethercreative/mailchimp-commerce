<?php

/**
 * Mailchimp for Craft Commerce
 *
 * @link      https://crankdcreative.co.uk
 * @copyright Copyright (c) 2020 Crankd Creative
 */

namespace crankd\mc\events;

use craft\base\Element;
use yii\base\Event;

/**
 * Class BuildSyncDataEvent
 *
 * @author  Crankd Creative
 * @package crankd\mc\events
 */
class BuildSyncDataEvent extends Event
{

	/** @var Element */
	public $element;

	/** @var array */
	public $syncData;
}
