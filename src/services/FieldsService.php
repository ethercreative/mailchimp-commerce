<?php

/**
 * Mailchimp for Craft Commerce
 *
 * @link      https://crankdcreative.co.uk
 * @copyright Copyright (c) 2023 Crankd Creative
 */

namespace crankd\mc\services;

use Craft;
use craft\base\Component;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\fields\BaseRelationField;
use crankd\mc\MailchimpCommerce;

/**
 * Class FieldsService
 *
 * @author  Crankd Creative
 * @package crankd\mc\services
 */
class FieldsService extends Component
{

	/**
	 * Gets the value of the mapped field for the given element
	 *
	 * @param string  $setting
	 * @param Element $element
	 * @param string  $typeUid
	 * @param mixed   $fallback
	 *
	 * @return string|null
	 */
	public function getMappedFieldValue($setting, Element $element, $typeUid, $fallback = null)
	{
		$mappings = MailchimpCommerce::$i->getSettings()->{$setting};

		if (!array_key_exists($typeUid, $mappings) || !$mappings[$typeUid])
			return $fallback;

		/** @var Field $field */
		$field = Craft::$app->getFields()->getFieldByUid($mappings[$typeUid]);

		if ($field instanceof BaseRelationField)
			return $element->{$field->handle}->one()->title;

		return (string) $element->{$field->handle};
	}

	/**
	 * Gets the mapped relational field
	 *
	 * @param         $setting
	 * @param Element $element
	 * @param         $typeUid
	 *
	 * @return ElementInterface[]|mixed|string|null
	 */
	public function getMappedFieldRelation($setting, Element $element, $typeUid)
	{
		$mappings = MailchimpCommerce::$i->getSettings()->{$setting};

		if (!array_key_exists($typeUid, $mappings) || !$mappings[$typeUid])
			return null;

		/** @var Field $field */
		$field = Craft::$app->getFields()->getFieldByUid($mappings[$typeUid]);

		return $element->{$field->handle};
	}
}
