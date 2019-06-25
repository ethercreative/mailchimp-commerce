<?php
/**
 * Mailchimp for Craft Commerce
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

return [
	'Mailchimp Commerce' => 'Mailchimp Commerce',
	'Never' => 'Never',
	'Last Synced to Mailchimp' => 'Last Synced to Mailchimp',
	'Mailchimp syncing is disabled.' => 'Mailchimp syncing is disabled.',

	// Settings
	// =========================================================================

	'Connect' => 'Connect',
	'List Settings' => 'List Settings',
	'Sync' => 'Sync',
	'Field Mappings' => 'Field Mappings',

	'Mailchimp API Key' => 'Mailchimp API Key',
	'Your Mailchimp API key. [About API Keys]({aboutUrl})' => 'Your Mailchimp API key. [About API Keys]({aboutUrl})',

	'Store List' => 'Store List',
	'Select the Mailchimp list you want this store to be attached to.' => 'Select the Mailchimp list you want this store to be attached to.',

	'Product Vendor' => 'Product Vendor',
	'Will fallback to the store name if not set.' => 'Will fallback to the store name if not set.',

	'Product Type' => 'Product Type',
	'Field' => 'Field',
	'None' => 'None',

	'Product Description' => 'Product Description',
	'Product Thumbnail' => 'Product Thumbnail',
	'Product Images' => 'Product Images',
	'Variant Thumbnail' => 'Variant Thumbnail',
	'Variant Images' => 'Variant Images',

	'Shipped Order Status' => 'Shipped Order Status',
	'The statues that is used to define when an order has been shipped.' => 'The statues that is used to define when an order has been shipped.',

	'Opt-in Field' => 'Opt-in Field',
	'The field to use to check if the customer has opted in to marketing emails. This can be on the order or user. Must be a lightswitch.' =>
		'The field to use to check if the customer has opted in to marketing emails. This can be on the order or user. Must be a lightswitch.',

	'Thumbnail Transform' => 'Thumbnail Transform',
	'What transform to use when transforming the product thumbnails. Defaults to 1000px square.' =>
		'What transform to use when transforming the product thumbnails. Defaults to 1000px square.',

	'Image Transform' => 'Image Transform',
	'What transform to use when transforming the product images. Defaults to 1000px wide, dynamic height.' =>
		'What transform to use when transforming the product images. Defaults to 1000px wide, dynamic height.',

	'Promo Redemption URL' => 'Promo Redemption URL',
	'The URL that will be used for redeeming a promo code.' => 'The URL that will be used for redeeming a promo code.',

	'Abandoned Cart Restore URL' => 'Abandoned Cart Restore URL',
	'The URL that will be redirected to after an abandoned cart is restored.' =>
		'The URL that will be redirected to after an abandoned cart is restored.',

	// Jobs
	// =========================================================================

	'Syncing Products to Mailchimp' => 'Syncing Products to Mailchimp',
	'Syncing Orders to Mailchimp' => 'Syncing Orders to Mailchimp',
	'Syncing Promos to Mailchimp' => 'Syncing Promos to Mailchimp',

	// Sync
	// =========================================================================

	'You have already synced your store. You can\'t change the list ID after a store has been synced.' =>
		'You have already synced your store. You can\'t change the list ID after a store has been synced.',
	'Manually sync the store if your domain or other details have changed.' => 'Manually sync the store if your domain or other details have changed.',
	'Sync Store' => 'Sync Store',
	'Store Synced.' => 'Store Synced.',

	'{synced} of {total, plural, =1{1 product} other{# products}} have been synced.' => '{synced} of {total, plural, =1{1 product} other{# products}} have been synced.',
	'Sync Products' => 'Sync Products',

	'{synced} of {total, plural, =1{1 cart} other{# carts}} have been synced.' => '{synced} of {total, plural, =1{1 cart} other{# carts}} have been synced.',
	'Some carts may not appear to sync. This is due to them not having either an email or any line items.' =>
		'Some carts may not appear to sync. This is due to them not having either an email or any line items.',
	'Sync All Carts' => 'Sync All Carts',

	'{synced} of {total, plural, =1{1 order} other{# orders}} have been synced.' => '{synced} of {total, plural, =1{1 order} other{# orders}} have been synced.',
	'Sync All Orders' => 'Sync All Orders',

	'{synced} of {total, plural, =1{1 promo} other{# promos}} have been synced.' => '{synced} of {total, plural, =1{1 promo} other{# promos}} have been synced.',
	'Sync All Promos' => 'Sync All Promos',

];
