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
use kuriousagency\affiliate\elements\Invoice;

use Craft;
use craft\web\Controller;
use yii\web\HttpException;
use yii\web\Response;

use craft\commerce\Plugin as Commerce;

/**
 * @author    Kurious Agency
 * @package   Affiliate
 * @since     1.0.0
 */
class InvoicesController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     * The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = [];

    // Public Methods
    // =========================================================================

    public function actionCreate()
	{
	
		$this->requirePostRequest();

		$validCreditIds = [];
		$invoiceTotal = 0;

		$user = Craft::$app->getUser()->getIdentity();
		$affiliateUser = Affiliate::$plugin->users->getAffiliateUserByUserId($user->id);

		$credits = Credit::find()
					->userId($user->id)
					->invoiceId(null)
					->status('approved')
					->all();

		// Craft::dd($credits);

		if($credits) {

			foreach($credits as $credit) {
				$invoiceTotal += $credit->totalPrice;
			}

			// get primary billing address
			$customer = Commerce::getInstance()->getCustomers()->getCustomerByUserId($user->id);
			$billingAddress = $customer->getPrimaryBillingAddress();

			if(!$billingAddress) {
				return false;
			}

			if(isset($user->currency)) {
				$currency = $user->currency;
			} else {
				$currency = Commerce::getInstance()->getPaymentCurrencies()->getPrimaryPaymentCurrencyIso();
			}

			$invoice = new Invoice();
			$invoice->number = Affiliate::$plugin->invoices->generateCartNumber();
			$invoice->userId = $user->id;
			$invoice->firstName = $billingAddress->firstName;
			$invoice->lastName = $billingAddress->lastName;
			$invoice->address1 = $billingAddress->address1;
			$invoice->address2 = $billingAddress->address2;
			$invoice->city = $billingAddress->city;
			$invoice->zipCode = $billingAddress->zipCode;
			$invoice->phone = $billingAddress->phone;
			$invoice->alternativePhone = $billingAddress->alternativePhone;
			$invoice->businessName = $billingAddress->businessName;
			$invoice->businessTaxId = $billingAddress->businessTaxId;
			$invoice->businessId = $billingAddress->businessId;
			$invoice->stateName = $billingAddress->stateName;
			$invoice->countryId = $billingAddress->countryId;
			$invoice->toAddress = Affiliate::$plugin->getSettings()->invoiceAddress;
			$invoice->toBusinessTaxId = Affiliate::$plugin->getSettings()->businessTaxId;
			$invoice->totalPrice = $invoiceTotal;
			$invoice->currency = $currency;
			$invoice->paid = 0;
			$invoice->paymentEmail = $affiliateUser['paymentEmail'];

			Craft::$app->getElements()->saveElement($invoice, false);

			foreach($credits as $credit)
			{
				$credit->invoiceId = $invoice->id;
				Craft::$app->getElements()->saveElement($credit, false);
			}
		}

		$this->redirectToPostedUrl();

	}

	public function actionEdit(int $id = null)
	{
		// get invoice 
		$invoice = Invoice::find()
					->id($id)
					->one();

		$credits = Credit::find()
			->invoiceId($id)
			->status(null)
			->all();

		$variables = [
			'invoice'=>$invoice,
			'credits'=>$credits,

		];
		
		return $this->renderTemplate('affiliate/invoices/edit', $variables);
	}

	public function actionSave()
	{
		$this->requirePostRequest();

        $request = Craft::$app->getRequest();
		$id = $request->post('id');
		
		Craft::$app->getElements()->saveElement($credit, false);
	}

	public function actionPdf(): Response
    {
        $number = Craft::$app->getRequest()->getQueryParam('number','1');
        $option = Craft::$app->getRequest()->getQueryParam('option', '');
		// $order = Plugin::getInstance()->getOrders()->getOrderByNumber($number);
		$invoice = Invoice::find()
							->number($number)
							->one();

        if (!$invoice) {
            throw new HttpException('No Invoice Found');
        }

		$pdf = Affiliate::$plugin->invoices->renderPdfForInvoice($invoice, $option);

	    $fileName = 'Invoice-' . $invoice->number;

        return Craft::$app->getResponse()->sendContentAsFile($pdf, $fileName . '.pdf', [
            'mimeType' => 'application/pdf'
        ]);
	}
	
}
