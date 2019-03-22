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
use kuriousagency\affiliate\elements\Credit;
use kuriousagency\affiliate\elements\Invoice;

use kuriousagency\affiliate\models\UserTracking as UserTrackingModel;
use kuriousagency\affiliate\records\UserTracking as UserTrackingRecord;
use kuriousagency\affiliate\records\OrderTracking as OrderTrackingRecord;

use Craft;
use craft\base\Component;
use craft\db\Query;
use craft\helpers\stringhelper;
use craft\mail\Message;
use craft\commerce\Plugin as Commerce;

use yii\base\Exception;

/**
 * @author    Kurious Agency
 * @package   Affiliate
 * @since     1.0.0
 */
class Users extends Component
{
    // Public Methods
    // =========================================================================

    /*
     * @return mixed
     */
    public function getUserByInvoiceId($id)
    {
		if (!$id) {
			return null;
		}

		$query = Credit::find();
		$query->invoiceId($id);

		return $query->one();	
	}

	public function getInvoiceNumber()
	{
		return md5(uniqid(mt_rand(), true));
	}

	public function getUserByTrackingRef($ref)
	{
		$row = $this->_createUserTrackingQuery()
		->where(['trackingRef' => $ref])
		->one();

		return $row ? Craft::$app->getUsers()->getUserById($row['userId']) : [];
	}

	public function getAffiliateUserByUserId($userId)
	{
		$row = $this->_createUserTrackingQuery()
		->where(['userId' => $userId])
		->one();

		return $row ? $row : [];
	}

	public function checkUserAffiliateGroup($user)
	{
		$currentGroupIds = [];
		$affiliateGroup = str_replace("_","",Affiliate::$plugin->getSettings()->affiliateUserGroup);
		
		foreach($user->getGroups() as $group) {
			$currentGroupIds[] = $group->id;	
		}

		if(in_array($affiliateGroup,$currentGroupIds)) {
			return true;
		}

		return false; 
	}

	public function save($user,$paymentEmail="")
	{
		$record = UserTrackingRecord::find()->where([
			'userId' => $user->id,
		])->one();

		if (!$record) {
			$record = new UserTrackingRecord();
			$record->userId = $user->id;
			$record->trackingRef = $this->_createTrackingRef();
		} elseif($paymentEmail) {
			$record->paymentEmail = $paymentEmail;
		}

		$db = Craft::$app->getDb();
		$transaction = $db->beginTransaction();

		try {
			// Save it
			$record->save(false);

			$transaction->commit();
		} catch (\Exception $e) {
			$transaction->rollBack();

			throw $e;
		}
		
		return true;
	}

	public function saveOrderTracking($orderId,$trackingRef)
	{

		$record = OrderTrackingRecord::find()->where([
			'orderId' => $orderId,
		])->one();

		if (!$record) {
			$record = new OrderTrackingRecord();

			$record->orderId = $orderId;
			$record->trackingRef = $trackingRef;

			$db = Craft::$app->getDb();
			$transaction = $db->beginTransaction();

			try {
				// Save it
				$record->save(false);

				$transaction->commit();
			} catch (\Exception $e) {
				$transaction->rollBack();

				throw $e;
			}
		}
		
		return true;
	}


	public function sendNewCustomerEmail($email)
	{
		$user =  Craft::$app->getUser()->getIdentity();
		$affiliateUser = $this->getAffiliateUserByUserId($user->id);

		$discountCodeId = str_replace('_','',Affiliate::$plugin->getSettings()->newCustomerDiscountCodeId);
		
		// get selected discount code
		$discount = Commerce::getInstance()->getDiscounts()->getDiscountById($discountCodeId);

		$customerCode = $affiliateUser['trackingRef'] . "-" . $discount->code;

		$templatePath = "";
		$renderVariables = [
		   'customerCode' => $customerCode,
		   'handle' => 'newCustomerDiscount'
        ];

		$originalLanguage = Craft::$app->language;

		$templatePath = Affiliate::$plugin->getSettings()->newCustomerEmailTemplate;

		$view = Craft::$app->getView();
		$oldTemplateMode = $view->getTemplateMode();

		Craft::$app->language = $originalLanguage;
		$view->setTemplateMode($view::TEMPLATE_MODE_SITE);

		if($view->doesTemplateExist($templatePath)) {

			$newEmail = new Message();
			$newEmail->setTo($email);
			$newEmail->setFrom(Craft::$app->systemSettings->getEmailSettings()->fromEmail);
			$newEmail->setSubject('');
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
				'code' => $customerCode
			]);

			Craft::error($error, __METHOD__);
		}
		
		Craft::$app->language = $originalLanguage;
		$view->setTemplateMode($oldTemplateMode);

		return true;

	}

	public function getPaymentEmail()
	{
		$user = Craft::$app->getUser()->getIdentity();
		
		$row = $this->_createUserTrackingQuery()
		->where(['userId' => $user->id])
		->one();

		return $row['paymentEmail'];
	}

	public function getTrackingRef()
	{
		$user = Craft::$app->getUser()->getIdentity();
		
		$row = $this->_createUserTrackingQuery()
		->where(['userId' => $user->id])
		->one();

		return $row['trackingRef'];
	}

	private function _createTrackingRef()
    {
		$code = StringHelper::randomString(8);
		
		$row = $this->_createUserTrackingQuery()
		->where(['trackingRef' => $code])
		->one();

        if(!$row) {
            return $code;
        } else {
            return $this->_createTrackingRef();
        }
    }

	private function _createUserTrackingQuery()
    {
        return (new Query())
            ->select([
				'id',
                'userId',
                'trackingRef',
                'paymentEmail',
            ])
            ->from(['{{%affiliate_user}}']);
    }
	
}
