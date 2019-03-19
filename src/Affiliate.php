<?php
/**
 * Affiliate plugin for Craft CMS 3.x
 *
 * Plugin to add affiliates to Craft Commerce
 *
 * @link      https://kurious.agency
 * @copyright Copyright (c) 2019 Kurious Agency
 */

namespace kuriousagency\affiliate;

use kuriousagency\affiliate\services\Users as UsersService;
use kuriousagency\affiliate\services\Vouchers as VouchersService;
use kuriousagency\affiliate\services\Invoices as InvoicesService;
use kuriousagency\affiliate\variables\AffiliateVariable;
use kuriousagency\affiliate\models\Settings;
use kuriousagency\affiliate\fields\AffiliateField as AffiliateFieldField;
use kuriousagency\affiliate\web\twig\CraftVariableBehavior;

use kuriousagency\affiliate\elements\Credit;
use verbb\giftvoucher\elements\Voucher;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\services\Elements;
use craft\services\Fields;
use craft\web\twig\variables\CraftVariable;

use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\elements\User;

use craft\commerce\elements\Order;
use craft\commerce\events\RefundTransactionEvent;
use craft\commerce\services\Payments;

use yii\base\Event;

/**
 * Class Affiliate
 *
 * @author    Kurious Agency
 * @package   Affiliate
 * @since     1.0.0
 *
 * @property  CreditsService $affiliateService
 */
class Affiliate extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var Affiliate
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
		self::$plugin = $this;

		$this->setComponents([
			'users' => UsersService::class,
			'vouchers' => VouchersService::class,
			'invoices' => InvoicesService::class,
		]);

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['siteActionTrigger1'] = 'affiliate/default';
            }
        );

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
				$event->rules['affiliate/invoices/<id:\d+>'] = 'affiliate/invoices/edit';
				$event->rules['affiliate/settings/general'] = 'affiliate/settings/edit';
            }
        );

        Event::on(
            Elements::class,
            Elements::EVENT_REGISTER_ELEMENT_TYPES,
            function (RegisterComponentTypesEvent $event) {
            }
        );

        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = AffiliateFieldField::class;
            }
        );

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
				$variable = $event->sender;
				$variable->attachBehavior('affiliate', CraftVariableBehavior::class);
                $variable->set('affiliate', AffiliateVariable::class);
            }
        );

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                }
            }
		);
		
		Event::on(Order::class, Order::EVENT_AFTER_COMPLETE_ORDER, function(Event $e){
			$order = $e->sender;

			// check order user is not the same as the current user

			//check if affilate order and store credits
			if(Craft::$app->session->get('userRef')) {

				// store tracking ref in order tracking table
				Affiliate::$plugin->users->saveOrderTracking($order->id,Craft::$app->session->get('userRef'));
					
				$affiliateUser = Affiliate::$plugin->users->getUserByTrackingRef(Craft::$app->session->get('userRef'));

				if($affiliateUser) {

					// check if affiliate
					if(Affiliate::$plugin->users->checkUserAffiliateGroup($affiliateUser)) {
						
						$orderTotal = $order->totalPrice;
						
						if($affiliateUser->currency != $order->currency) {
							// convert order total to affiliates currency
							if(array_key_exists($affiliateUser->currency,Affiliate::$plugin->getSettings()->exchangeRates)) {
								$conversionRate = Affiliate::$plugin->getSettings()->exchangeRates[$affiliateUser->currency];
								$orderTotal = $order->totalPrice/$conversionRate;
							}
						}

						$credit = new Credit;
						$credit->orderId = $order->id;
						$credit->userId = $affiliateUser->id;
						$credit->totalPrice = round($orderTotal * (Affiliate::$plugin->getSettings()->percentage/100),2);

						Craft::$app->getElements()->saveElement($credit, false);
					} 
					// referrer send gift voucher if this is new customers first order
					else {
						Affiliate::$plugin->voucher->referrerVoucher($affiliateUser,$order);
					}

				}
			}

		});

		Event::on(Elements::class,Elements::EVENT_AFTER_SAVE_ELEMENT,function(Event $e) {
			if ($e->element instanceof User) {
				
				// if($e->isNew) {
					$user = $e->element;
					Affiliate::$plugin->users->save($user);
				// }

			}
		});


		// check for refunds
		Event::on(Payments::class, Payments::EVENT_AFTER_REFUND_TRANSACTION, function(RefundTransactionEvent $e) {
			$transaction = $e->transaction;

			$order = $transaction->getOrder();
			
			$credit = Credit::find()
					->orderId($order->id)
					->invoiceId(null)
					// ->status('pending')
					->one();

			if($credit) {

				$orderTotal = $order->totalPrice;
				$affiliateUser = Affiliate::$plugin->users->getAffiliateUserByUserId($credit->userId);
						
				if($affiliateUser->currency != $order->currency) {
					// convert order total to affiliates currency
					if(array_key_exists($affiliateUser->currency,Affiliate::$plugin->getSettings()->exchangeRates)) {
						$conversionRate = Affiliate::$plugin->getSettings()->exchangeRates[$affiliateUser->currency];
						$orderTotal = $order->totalPrice/$conversionRate;
					}
				}
				
				$credit->totalPrice = round($orderTotal * (Affiliate::$plugin->getSettings()->percentage/100),2);
				Craft::$app->getElements()->saveElement($credit, false);

			}

		});

		if(Craft::$app->request->getQueryParam('affref')) {

			$userTrackingRef = Craft::$app->request->getQueryParam('affref');

			Craft::$app->session->set('userRef',$userTrackingRef);

			// TODO send to template path if referer
			$user = Affiliate::$plugin->users->getUserByTrackingRef($userTrackingRef);
			$currentGroups = [];
			$affiliateGroups = Affiliate::$plugin->getSettings()->affiliateUserGroup;
			foreach($user->getGroups() as $group) {
				$currentGroups[] = $group->id;	
			}
			
			// if not an affiliate redirect to new customer page
			if(!count(array_intersect($affiliateGroups, $currentGroups))) {

				$redirectUrl = Affiliate::$plugin->getSettings()->newCustomerTemplatePath ? Affiliate::$plugin->getSettings()->newCustomerTemplatePath : "/";

				Craft::$app->getResponse()->redirect($redirectUrl)->send();
				Craft::$app->end();
			}
		}

        Craft::info(
            Craft::t(
                'affiliate',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
	}
	
	public function getCpNavItem()
    {
        $ret = parent::getCpNavItem();

		$ret['label'] = $this->name;

		$ret['subnav']['invoices'] = [
			'label' => 'Invoices',
			'url'   => 'affiliate/invoices',
		];

		$ret['subnav']['credits'] = [
			'label' => 'Credits',
			'url'   => 'affiliate/credits',
		];

		
		$ret['subnav']['settings'] = [
			'label' => 'Settings',
			'url'   => 'affiliate/settings/general',
		];


       
        return $ret;
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'affiliate/settings',
            [
                'settings' => $this->getSettings()
            ]
        );
    }
}
