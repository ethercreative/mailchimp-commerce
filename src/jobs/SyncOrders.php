<?php

/**
 * Mailchimp for Craft Commerce
 *
 * @link      https://crankdcreative.co.uk
 * @copyright Copyright (c) 2023 Crankd Creative
 */

namespace crankd\mc\jobs;

use Craft;
use craft\db\QueryAbortedException;
use craft\queue\BaseJob;
use craft\queue\QueueInterface;
use crankd\mc\MailchimpCommerce;
use Throwable;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\queue\Queue;

/**
 * Class SyncOrders
 *
 * @author  Crankd Creative
 * @package crankd\mc\jobs
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
	public function execute($queue): void
	{
		$orders = MailchimpCommerce::$i->orders;
		$i = 0;
		$total = count($this->orderIds);

		$hasFailure = false;

		foreach ($this->orderIds as $id) {
			if (!$orders->syncOrderById($id)) {
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

	protected function defaultDescription(): ?string
	{
		return MailchimpCommerce::t('Syncing Orders to Mailchimp');
	}
}
