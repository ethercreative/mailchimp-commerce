<?php

/**
 * Mailchimp for Craft Commerce
 *
 * @link      https://crankdcreative.co.uk
 * @copyright Copyright (c) 2023 Crankd Creative
 */

namespace crankd\mc\services;

use craft\base\Component;
use crankd\mc\MailchimpCommerce;

/**
 * Class ListsService
 *
 * @author  Crankd Creative
 * @package crankd\mc\services
 */
class ListsService extends Component
{
    /**
     * Get all available mailchimp lists, formatted for Select fields
     *
     * @return array
     */
    public function all()
    {
        [$success, $data] = MailchimpCommerce::$i->chimp->get('lists', [
            'fields' => 'lists.id,lists.name',
            'count' => 1000000,
        ]);

        if (!$success) {
            return [];
        }

        return array_map(function ($item) {
            return [
                'label' => $item['name'],
                'value' => $item['id'],
            ];
        }, $data['lists']);
    }
}
