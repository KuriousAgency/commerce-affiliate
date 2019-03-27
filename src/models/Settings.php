<?php
/**
 * Affiliate plugin for Craft CMS 3.x
 *
 * Plugin to add affiliates to Craft Commerce
 *
 * @link      https://kurious.agency
 * @copyright Copyright (c) 2019 Kurious Agency
 */

namespace kuriousagency\affiliate\models;

use kuriousagency\affiliate\Affiliate;

use Craft;
use craft\base\Model;

/**
 * @author    Kurious Agency
 * @package   Affiliate
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
	public $pendingDays = 0;
	
	public $affiliateUserGroup = 0;

	public $percentage = 0;

	public $exchangeRates = [];

	public $voucherExpiryMonths = 12;
	
	public $voucherEmailTemplate = "";	

	public $newCustomerPage = "";

	public $newCustomerDiscountCodeId = "";

	public $newCustomerEmailTemplate = "";

	public $invoiceAddress = "";

	public $businessTaxId = "";

	public $invoicePdfTemplatePath = "";

    // Public Methods
    // =========================================================================

    // /**
    //  * @inheritdoc
    //  */
    // public function rules()
    // {
    //     return [
    //         ['someAttribute', 'string'],
    //         ['someAttribute', 'default', 'value' => 'Some Default'],
    //     ];
    // }
}
