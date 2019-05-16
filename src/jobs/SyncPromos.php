<?php
/**
 * Mailchimp for Craft Commerce
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\mc\jobs;

use craft\db\QueryAbortedException;
use craft\queue\BaseJob;
use craft\queue\QueueInterface;
use ether\mc\MailchimpCommerce;
use Throwable;
use yii\db\Exception;
use yii\queue\Queue;

/**
 * Class SyncPromos
 *
 * @author  Ether Creative
 * @package ether\mc\jobs
 */
class SyncPromos extends BaseJob
{

	// Properties
	// =========================================================================

	public $promoIds = [];

	// Methods
	// =========================================================================

	/**
	 * @param QueueInterface|Queue $queue
	 *
	 * @throws QueryAbortedException
	 * @throws Throwable
	 * @throws \yii\base\Exception
	 * @throws Exception
	 */
	public function execute ($queue)
	{
		$promos = MailchimpCommerce::$i->promos;
		$i = 0;
		$total = count($this->promoIds);

		foreach ($this->promoIds as $id)
		{
			if (!$promos->syncPromoById($id))
				throw new QueryAbortedException('Failed to sync promo');

			$this->setProgress($queue, $i++ / $total);
		}
	}

	protected function defaultDescription ()
	{
		return MailchimpCommerce::t('Syncing Promos to Mailchimp');
	}

}