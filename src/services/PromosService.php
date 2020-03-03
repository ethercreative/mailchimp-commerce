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
use craft\commerce\Plugin as Commerce;
use craft\db\Query;
use craft\helpers\Db;
use craft\helpers\UrlHelper;
use DateTime;
use ether\mc\MailchimpCommerce;
use Throwable;
use yii\db\Exception;

/**
 * Class PromosService
 *
 * @author  Ether Creative
 * @package ether\mc\services
 */
class PromosService extends Component
{

	// Public
	// =========================================================================

	/**
	 * @param $promoId
	 *
	 * @return bool
	 * @throws Exception
	 * @throws Throwable
	 * @throws \yii\base\Exception
	 */
	public function syncPromoById ($promoId)
	{
		if (MailchimpCommerce::getInstance()->getSettings()->disableSyncing)
			return true;

		$hasBeenSynced = $this->_hasPromoBeenSynced($promoId);
		list($data, $code) = $this->_buildPromoData($promoId);

		if (!$data)
			return true;

		if ($hasBeenSynced)
			return $this->_updatePromo($promoId, $data, $code);
		else
			return $this->_createPromo($promoId, $data, $code);
	}

	/**
	 * Delete the promo from Mailchimp with the given promo ID
	 *
	 * @param $promoId
	 *
	 * @return bool|void
	 * @throws Exception
	 */
	public function deletePromoById ($promoId)
	{
		if (MailchimpCommerce::getInstance()->getSettings()->disableSyncing)
			return;

		if (!$this->_hasPromoBeenSynced($promoId))
			return;

		$storeId = MailchimpCommerce::$i->getSettings()->storeId;

		list($success, $data, $error) = MailchimpCommerce::$i->chimp->delete(
			'ecommerce/stores/' . $storeId . '/promo-rules/' . $promoId
		);

		if (!$success)
		{
			Craft::error($error, 'mailchimp-commerce');
			return false;
		}

		Craft::$app->getDb()->createCommand()
			->delete('{{%mc_promos_synced}}', [
				'promoId' => $promoId,
			])->execute();

		return true;
	}

	/**
	 * Returns the total number of promos synced
	 *
	 * @return int|string
	 */
	public function getTotalPromosSynced ()
	{
		return (new Query())
			->from('{{%mc_promos_synced}}')
			->count();
	}

	// Private
	// =========================================================================

	/**
	 * Create a promo in Mailchimp
	 *
	 * @param $promoId
	 * @param $data
	 * @param $code
	 *
	 * @return bool
	 * @throws Exception
	 */
	private function _createPromo ($promoId, $data, $code)
	{
		$storeId = MailchimpCommerce::$i->getSettings()->storeId;

		list($success, $data, $error) = MailchimpCommerce::$i->chimp->post(
			'ecommerce/stores/' . $storeId . '/promo-rules',
			$data
		);

		if (!$success)
		{
			Craft::error($error, 'mailchimp-commerce');
			return false;
		}

		list($success, $data, $error) = MailchimpCommerce::$i->chimp->post(
			'ecommerce/stores/' . $storeId . '/promo-rules/' . $promoId . '/promo-codes',
			$code
		);

		if (!$success)
		{
			Craft::error($error, 'mailchimp-commerce');
			return false;
		}

		Craft::$app->getDb()->createCommand()
			->insert(
				'{{%mc_promos_synced}}',
				[
					'promoId' => $promoId,
					'lastSynced' => Db::prepareDateForDb(new DateTime()),
				],
				false
			)->execute();

		return true;
	}

	/**
	 * Update promo code
	 *
	 * @param $promoId
	 * @param $data
	 * @param $code
	 *
	 * @return bool
	 * @throws Exception
	 */
	private function _updatePromo ($promoId, $data, $code)
	{
		$storeId = MailchimpCommerce::$i->getSettings()->storeId;

		list($success, $data, $error) = MailchimpCommerce::$i->chimp->patch(
			'ecommerce/stores/' . $storeId . '/promo-rules/' . $promoId,
			$data
		);

		if (!$success)
		{
			Craft::error($error, 'mailchimp-commerce');
			return false;
		}

		list($success, $data, $error) = MailchimpCommerce::$i->chimp->patch(
			'ecommerce/stores/' . $storeId . '/promo-rules/' . $promoId . '/promo-codes/' . $promoId,
			$code
		);

		if (!$success)
		{
			Craft::error($error, 'mailchimp-commerce');
			return false;
		}

		Craft::$app->getDb()->createCommand()
			->update(
				'{{%mc_promos_synced}}',
				[ 'lastSynced' => Db::prepareDateForDb(new DateTime()) ],
				[ 'promoId' => $promoId ],
				[],
				false
			)->execute();

		return true;
	}

	// Helpers
	// =========================================================================

	/**
	 * Checks if the given promo ID has been synced
	 *
	 * @param $promoId
	 *
	 * @return bool
	 */
	private function _hasPromoBeenSynced ($promoId)
	{
		return (new Query())
			->from('{{%mc_promos_synced}}')
			->where(['promoId' => $promoId])
			->exists();
	}

	/**
	 * Build promo data
	 *
	 * @param $promoId
	 *
	 * @return array
	 * @throws Throwable
	 * @throws \yii\base\Exception
	 */
	private function _buildPromoData ($promoId)
	{
		$promo = Commerce::getInstance()->getDiscounts()->getDiscountById($promoId);

		$amount = null;
		$type = null;
		$target = null;

		if ($promo->baseDiscount != 0)
		{
			$amount = $promo->baseDiscount * -1;
			$type = 'fixed';
			$target = 'total';
		}
		else if ($promo->perItemDiscount != 0)
		{
			$amount = $promo->perItemDiscount * -1;
			$type = 'fixed';
			$target = 'per_item';
		}
		else if ($promo->percentDiscount != 0)
		{
			$amount = $promo->percentDiscount * -1;
			$type = 'percentage';
			$target = 'per_item';
		}

		if (!$amount)
			return [null, null];

		$data = [
			'id' => (string) $promo->id,
			'title' => $promo->name,
			'description' => $promo->description ?: $promo->name,
			'amount' => (float) $amount,
			'type' => $type,
			'target' => $target,
			'enabled' => $promo->enabled,
			'created_at_foreign' => $promo->dateCreated->format('c'),
			'updated_at_foreign' => $promo->dateUpdated->format('c'),
		];

		if ($promo->dateFrom)
			$data['starts_at'] = $promo->dateFrom->format('c');

		if ($promo->dateTo)
			$data['ends_at'] = $promo->dateTo->format('c');

		$useLimitEnabled =
			$promo->totalDiscountUseLimit > 0
				? $promo->totalDiscountUses < $promo->totalDiscountUseLimit
				: true;

		$redemptionUrl = Craft::$app->getView()->renderObjectTemplate(
			MailchimpCommerce::$i->getSettings()->promoRedemptionUrl ?: '/',
			$promo
		);

		$code = [
			'id' => (string) $promo->id,
			'code' => $promo->code,
			'redemption_url' => UrlHelper::siteUrl($redemptionUrl),
			'usage_count' => (int) $promo->totalDiscountUses,
			'enabled' => $promo->enabled && $useLimitEnabled,
			'created_at_foreign' => $promo->dateCreated->format('c'),
			'updated_at_foreign' => $promo->dateUpdated->format('c'),
		];

		return [$data, $code];
	}

}
