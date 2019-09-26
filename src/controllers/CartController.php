<?php
/**
 * Affiliate plugin for Craft CMS 3.x
 *
 * Plugin to add affiliates to Craft Commerce
 *
 * @link      https://kurious.agency
 * @copyright Copyright (c) 2019 Kurious Agency
 */

namespace kuriousagency\affiliate\controllers;

use kuriousagency\affiliate\Affiliate;
use kuriousagency\affiliate\elements\Credit;

use Craft;
use craft\web\Controller;

use craft\commerce\Plugin as Commerce;
use verbb\giftvoucher\GiftVoucher;

/**
 * @author    Kurious Agency
 * @package   Affiliate
 * @since     1.0.0
 */
class CartController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = ['coupon'];

    // Public Methods
    // =========================================================================

    /**
     * @return mixed
     */
    public function actionCoupon()
    {
		
		$this->requirePostRequest();

		$couponCode = Craft::$app->getRequest()->getRequiredBodyParam('couponCode');	

		// check referrer discount code isn't used on it's own without the referrer link
		$discountCodeId = str_replace('_','',Affiliate::$plugin->getSettings()->newCustomerDiscountCodeId);
		$discount = Commerce::getInstance()->getDiscounts()->getDiscountById($discountCodeId);

		if($discount && (strcasecmp($couponCode, $discount->code) == 0) ) {
			
			$error = Craft::t('affiliate', 'Code is not valid');
			Craft::$app->getSession()->setError($error);
			return $this->redirectToPostedUrl();
		}

		// Check to see if this is a Gift Voucher code
		$error = '';
		if(GiftVoucher::$plugin->getCodes()->matchCode($couponCode,$error)) {

			$params = [];

			foreach(Craft::$app->getRequest()->getBodyParams() as $key=>$value) {
				$params[$key] = $value;

				if($key == 'couponCode') {
					$params['voucherCode'] = $couponCode;
				}
			}

			Craft::$app->getRequest()->setBodyParams($params);
			$this->run('/gift-voucher/cart/add-code');
			
			return $this->redirectToPostedUrl();
		}

		// check if voucher code is affiliateRef-coupon
		if(strpos($couponCode,"-") !== false) {

			$couponCodeParts = explode("-",$couponCode);

			$userTrackingRef = $couponCodeParts[0];
			$couponCode = $couponCodeParts[1];

			// check if this is from a valid referred user
			if(Affiliate::$plugin->users->getUserByTrackingRef($userTrackingRef)) {

				// see session to record in order tracking table
				Craft::$app->session->set('userRef',$userTrackingRef);

				$params = [];

				foreach(Craft::$app->getRequest()->getBodyParams() as $key=>$value) {
					$params[$key] = $value;

					if($key == 'couponCode') {
						$params['couponCode'] = $couponCode;
					}
				}

				Craft::$app->getRequest()->setBodyParams($params);
			}
		}

		// check standard commerce coupon code
		$this->run('/commerce/cart/update-cart');

	}
	
	// public function actionTestVoucher()
	// {
		
		
	// 	$affiliateUser = Craft::$app->users->getUserById(150);

	// 	// Affiliate::$plugin->vouchers->createGiftVoucher($user);
	// 	$order = Commerce::getInstance()->getOrders()->getOrderById(398);

	// 	Affiliate::$plugin->vouchers->referrerVoucher($affiliateUser,$order);

	// 	Craft::$app->end();

	// 	return true;
	// }

	// public function actionTestCredits()
	// {
	// 	$order = Commerce::getInstance()->getOrders()->getOrderById(398);

	// 	if(Craft::$app->session->get('userRef')) {

	// 		Affiliate::$plugin->users->saveOrderTracking($order->id,Craft::$app->session->get('userRef'));
				
	// 		$affiliateUser = Affiliate::$plugin->users->getUserByTrackingRef(Craft::$app->session->get('userRef'));

	// 		if($affiliateUser) {

	// 			// affiliate
	// 			if(Affiliate::$plugin->users->checkUserAffiliateGroup($affiliateUser)) {
					
	// 				$orderTotal = $order->totalPrice;

	// 				// currency conversion
	// 				if(isset($affiliateUser->currency)) {
	// 					if($affiliateUser->currency != $order->currency) {
							
	// 						// convert order total to affiliates currency
	// 						if(array_key_exists($affiliateUser->currency,Affiliate::$plugin->getSettings()->exchangeRates)) {
	// 							$conversionRate = Affiliate::$plugin->getSettings()->exchangeRates[$affiliateUser->currency];
	// 							$orderTotal = $order->totalPrice/$conversionRate;
	// 						}
	// 					}
	// 				}

	// 				$credit = new Credit;
	// 				$credit->orderId = $order->id;
	// 				$credit->userId = $affiliateUser->id;
	// 				$credit->totalPrice = round($orderTotal * (Affiliate::$plugin->getSettings()->percentage/100),2);

	// 				Craft::$app->getElements()->saveElement($credit, false);
	// 			} 

	// 			// referrer send gift voucher if this is new customers first order
	// 			else {
	// 				Affiliate::$plugin->voucher->referrerVoucher($affiliateUser,$order);
	// 			}

	// 		}
	// 	}

	// 	Craft::$app->end();

	// }


   
}
