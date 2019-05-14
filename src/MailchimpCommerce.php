<?php
/**
 * Mailchimp for Craft Commerce
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\mc;

use Craft;
use craft\base\Plugin;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\UrlHelper;
use craft\web\UrlManager;
use ether\mc\models\Settings;
use ether\mc\services\ChimpService;
use ether\mc\services\ListsService;
use ether\mc\services\StoreService;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Event;
use yii\base\Exception;

/**
 * Class MailchimpCommerce
 *
 * @author  Ether Creative
 * @package ether\mc
 * @property ChimpService $chimp
 * @property ListsService $lists
 * @property StoreService $store
 */
class MailchimpCommerce extends Plugin
{

	// Properties
	// =========================================================================

	/** @var self */
	public static $i;

	public $hasCpSettings = true;

	// Craft
	// =========================================================================

	public function init ()
	{
		parent::init();
		self::$i = $this;

		$this->setComponents([
			'chimp' => ChimpService::class,
			'lists' => ListsService::class,
			'store' => StoreService::class,
		]);

		// Events
		// ---------------------------------------------------------------------

		Event::on(
			UrlManager::class,
			UrlManager::EVENT_REGISTER_CP_URL_RULES,
			[$this, 'onRegisterCpUrlRules']
		);

	}

	// Settings
	// =========================================================================

	protected function createSettingsModel ()
	{
		return new Settings();
	}

	/**
	 * @return string|null
	 * @throws LoaderError
	 * @throws RuntimeError
	 * @throws SyntaxError
	 */
	protected function settingsHtml ()
	{
		return Craft::$app->getView()->renderTemplate(
			'mailchimp-commerce/_settings',
			[
				'settings' => $this->getSettings(),
				'lists' => $this->lists->all(),
			]
		);
	}

	/**
	 * @return bool|Settings|null
	 */
	public function getSettings ()
	{
		return parent::getSettings();
	}

	// Events
	// =========================================================================

	/**
	 * @throws Exception
	 */
	protected function afterInstall ()
	{
		$this->setStoreId();

		Craft::$app->getResponse()->redirect(
			UrlHelper::cpUrl('mailchimp-commerce/start')
		)->send();
	}

	public function onRegisterCpUrlRules (RegisterUrlRulesEvent $event)
	{
		$event->rules['mailchimp-commerce/start'] = 'mailchimp-commerce/start/index';
		$event->rules['mailchimp-commerce/finish'] = 'mailchimp-commerce/start/finish';
		$event->rules['mailchimp-commerce/complete'] = 'mailchimp-commerce/start/complete';
	}

	// Helpers
	// =========================================================================

	public static function t ($message, $params = [])
	{
		return Craft::t('mailchimp-commerce', $message, $params);
	}

	/**
	 * Generates a unique store ID and saves it in the plugin settings
	 * @throws Exception
	 */
	private function setStoreId ()
	{
		if ($this->getSettings()->storeId)
			return;

		Craft::$app->getPlugins()->savePluginSettings($this, [
			'storeId' => Craft::$app->getSecurity()->generateRandomString(),
		]);

		Craft::$app->getPlugins()->enablePlugin('mailchimp-commerce');
	}

}