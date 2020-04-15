<?php
/**
 * Mailchimp for Craft Commerce
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\mc\jobs;

use Craft;
use craft\db\QueryAbortedException;
use craft\queue\BaseJob;
use craft\queue\QueueInterface;
use ether\mc\MailchimpCommerce;
use Throwable;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\queue\Queue;

/**
 * Class SyncOrders
 *
 * @author  Ether Creative
 * @package ether\mc\jobs
 */
class SyncOrders extends BaseJob
{

	// Properties
	// =========================================================================

	public $orderIds = [];

	// Methods
	// =========================================================================

	/**
	 * @param QueueInterface|Queue $queue
	 *
	 * @throws QueryAbortedException
	 * @throws Throwable
	 * @throws Exception
	 * @throws InvalidConfigException
	 */
	public function execute ($queue)
	{
		$orders = MailchimpCommerce::$i->orders;
		$i = 0;
		$total = count($this->orderIds);

		$hasFailure = false;

		foreach ($this->orderIds as $id)
		{
			if (!$orders->syncOrderById($id))
			{
				$hasFailure = true;
				Craft::error(
					'Failed to sync order ' . $id,
					'mailchimp-commerce'
				);
			}

			$this->setProgress($queue, $i++ / $total);
		}

		if ($hasFailure)
			throw new QueryAbortedException('Failed to sync order');
	}

	protected function defaultDescription ()
	{
		return MailchimpCommerce::t('Syncing Orders to Mailchimp');
	}

}
