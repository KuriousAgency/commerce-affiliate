<?php
/**
 * Affiliate plugin for Craft CMS 3.x
 *
 * Plugin to add affiliates to Craft Commerce
 *
 * @link      https://kurious.agency
 * @copyright Copyright (c) 2019 Kurious Agency
 */

namespace kuriousagency\affiliate\services;

use kuriousagency\affiliate\Affiliate;

use verbb\giftvoucher\elements\Code;
use verbb\giftvoucher\elements\Voucher;
use craft\db\Query;
use craft\commerce\Plugin as Commerce;

use Craft;
use craft\base\Component;
use craft\helpers\stringhelper;
use craft\helpers\DateTimeHelper;
use craft\mail\Message;
use \DateTime;
use \DateInterval;

use yii\base\Exception;



/**
 * @author    Kurious Agency
 * @package   Affiliate
 * @since     1.0.0
 */
class Vouchers extends Component
{

	
	public function referrerVoucher($affiliateUser,$order)
	{
		
		// check this is new customers first order
		$newCustomerOrders = Commerce::getInstance()->getOrders()->getOrdersByEmail($order->email);

		if(count($newCustomerOrders) == 1) {

			// create voucher code
			$code = $this->createGiftVoucher($affiliateUser);

			// send email
			$this->sendEmail($affiliateUser->email,$code);

		}

		return false;

	}
	
	
	public function createGiftVoucher($user)
	{
		
		// get or create voucher
		if(isset($user->currency)) {
			$voucherSku = $user->currency;
		} else {
			$voucherSku = Commerce::getInstance()->getPaymentCurrencies()->getPrimaryPaymentCurrencyIso();
		}

		$row = (new Query())
				->select([
					'id',
					'price',
					'sku',
				])
				->from('{{%giftvoucher_vouchers}}')
				->where(['sku' => $voucherSku])
				->one();

		if($row) {
			
			if(Affiliate::$plugin->getSettings()->voucherExpiryMonths) {
				$expiryDate = new DateTime();
				$expiryDate->add(new DateInterval('P'.Affiliate::$plugin->getSettings()->voucherExpiryMonths.'M'));
			}
			
			$code = new Code();
			$code->voucherId = $row['id'];
			$code->originalAmount = $row['price'];
			$code->currentAmount = $row['price'];
			$code->expiryDate = $expiryDate ? DateTimeHelper::toIso8601($expiryDate) : null;
	
			if(Craft::$app->getElements()->saveElement($code)) {
				return $code;
			}

		}

		return false;
	}

	public function sendEmail($email,$code)
	{
		$templatePath = "";
		$renderVariables = [
           'voucherCode' => $code->codeKey,
		   'expiryDate' => $code->expiryDate,
		   'handle' => 'referralVoucher'
        ];

		$originalLanguage = Craft::$app->language;

		$templatePath = Affiliate::$plugin->getSettings()->voucherEmailTemplate;

		$view = Craft::$app->getView();
		$oldTemplateMode = $view->getTemplateMode();

		Craft::$app->language = $originalLanguage;
		$view->setTemplateMode($view::TEMPLATE_MODE_SITE);

		if($view->doesTemplateExist($templatePath)) {

			$newEmail = new Message();
			$newEmail->setTo($email);
			$newEmail->setFrom(Craft::$app->systemSettings->getEmailSettings()->fromEmail);
			$newEmail->setSubject('Your Voucher Code');
			$newEmail->variables = $renderVariables;
			$body = $view->renderTemplate($templatePath, $renderVariables);
			$newEmail->setHtmlBody($body);

			if (!Craft::$app->getMailer()->send($newEmail)) {
			
				$error = Craft::t('Affiliate', 'Email Error');
	
				Craft::error($error, __METHOD__);
				
				Craft::$app->language = $originalLanguage;
				$view->setTemplateMode($oldTemplateMode);
	
				return false;
			}

		} else {
			$error = Craft::t('Affiliate', 'Template not found “{code}”.', [
				'code' => $code->codeKey
			]);

			Craft::error($error, __METHOD__);
		}
		
		Craft::$app->language = $originalLanguage;
		$view->setTemplateMode($oldTemplateMode);

		return true;

	}


}