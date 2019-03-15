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
use kuriousagency\affiliate\elements\Invoice;

use Craft;
use craft\base\Component;
use craft\db\Query;
use craft\helpers\stringhelper;

use yii\base\Exception;

/**
 * @author    Kurious Agency
 * @package   Affiliate
 * @since     1.0.0
 */
class Invoices extends Component
{

	public function generateCartNumber(): string
    {
        return md5(uniqid(mt_rand(), true));
	}
	
	// public function createInvoice()
	// {
		
	// }

	public function markAsPaid($id)
	{
		
		$invoice = Invoice::findOne($id);
		$invoice->paid = 1;

		Craft::$app->getElements()->saveElement($invoice, false);

		return true;
	}

}