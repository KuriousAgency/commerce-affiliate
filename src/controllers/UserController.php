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

/**
 * @author    Kurious Agency
 * @package   Affiliate
 * @since     1.0.0
 */
class UserController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = [];

    // Public Methods
    // =========================================================================

    /**
	 * send email to customer with referrer link and voucher code
     */
    public function actionNewCustomerEmail()
    {
		
		$this->requirePostRequest();
		$email = Craft::$app->getRequest()->getRequiredBodyParam('email');

		Affiliate::$plugin->users->sendNewCustomerEmail($email);

		$this->redirectToPostedUrl();
	}

	public function actionSavePaymentEmail()
	{
		$this->requirePostRequest();
		$paymentEmail = Craft::$app->getRequest()->getRequiredBodyParam('paymentEmail');
		$user =  Craft::$app->getUser()->getIdentity();

		Affiliate::$plugin->users->save($user,$paymentEmail);

		$this->redirectToPostedUrl();
	}
   
}
