<?php
/**
 * Affiliate plugin for Craft CMS 3.x
 *
 * Plugin to add affiliates to Craft Commerce
 *
 * @link      https://kurious.agency
 * @copyright Copyright (c) 2019 Kurious Agency
 */

namespace kuriousagency\affiliate\elements\db;

use kuriousagency\affiliate\Affiliate;

use Craft;
use craft\elements\User;
use craft\elements\db\ElementQuery;
use craft\helpers\Db;

use kuriousagency\affiliate\elements\Invoice;

use DateTime;
use DateInterval;

class InvoiceQuery extends ElementQuery
{
	public $number;

	public $userId;
	
	public $firstName;

	public $lastName;

	public $address1;

	public $address2;

	public $city;

	public $zipCode;

	public $phone;

	public $alternativePhone;

	public $businessName;

	public $businessTaxId;

	public $businessId;

	public $stateName;

	public $countryId;

	public $toAddress;

	public $toBusinessTaxId;

	public $currency;

	public $totalPrice;

	public $paid;

	public $paymentEmail;


	
	 /**
     * @var array
     */
	// protected $defaultOrderBy = ['affiliate_invoices.dateCreated' => SORT_DESC];
	

	// Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    // public function __construct(string $elementType, array $config = [])
    // {
    //     // Default status
    //     if (!isset($config['status'])) {
    //         $config['status'] = Invoice::STATUS_UNPAID;
    //     }

    //     parent::__construct($elementType, $config);
	// }

	public function number($value)
    {
        $this->number = $value;

        return $this;
	}

	public function userId($value)
    {
        $this->userId = $value;

        return $this;
	}

	public function firstName($value)
    {
        $this->firstName = $value;

        return $this;
	}
	
	public function lastName($value)
    {
        $this->lastName = $value;

        return $this;
    }

    public function address1($value)
    {
        $this->address1 = $value;

        return $this;
	}
	
	public function address2($value)
    {
        $this->address2 = $value;

        return $this;
	}

	public function city($value)
    {
        $this->city = $value;

        return $this;
	}

	public function zipCode($value)
    {
        $this->zipCode = $value;

        return $this;
	}

	public function phone($value)
    {
        $this->phone = $value;

        return $this;
	}

	public function alternativePhone($value)
    {
        $this->alternativePhone = $value;

        return $this;
	}

	public function businessName($value)
    {
        $this->businessName = $value;

        return $this;
	}

	public function businessTaxId($value)
    {
        $this->businessTaxId = $value;

        return $this;
	}

	public function businessId($value)
    {
        $this->businessId = $value;

        return $this;
	}

	public function countryId($value)
    {
        $this->countryId = $value;

        return $this;
	}

	public function stateName($value)
    {
        $this->stateName = $value;

        return $this;
	}

	public function totalPrice($value)
    {
        $this->totalPrice = $value;

        return $this;
	}

	public function toAddress($value)
    {
        $this->toAddress = $value;

        return $this;
	}

	public function toBusinessTaxId($value)
    {
        $this->toBusinessTaxId = $value;

        return $this;
	}

	public function currency($value)
    {
        $this->currency = $value;

        return $this;
	}

	public function paid($value)
    {
		$this->paid = $value;

        return $this;
	}

	public function paymentEmail($value)
    {
        $this->paymentEmail = $value;

        return $this;
	}

	public function status($value)
    {
        return parent::status($value);
    }

    protected function beforePrepare(): bool
    {	
		
		$this->joinElementTable('affiliate_invoices');
		$this->subQuery->innerJoin('{{%affiliate_credits}} credits', '[[affiliate_invoices.id]] = [[credits.invoiceId]]');
		
		// select the columns
        $this->query->select([
			'affiliate_invoices.id',
			'affiliate_invoices.number',
			'affiliate_invoices.userId',
            'affiliate_invoices.firstName',
			'affiliate_invoices.lastName',
			'affiliate_invoices.address1',
			'affiliate_invoices.address2',
			'affiliate_invoices.city',
			'affiliate_invoices.zipCode',
			'affiliate_invoices.phone',
			'affiliate_invoices.alternativePhone',
			'affiliate_invoices.businessName',
			'affiliate_invoices.businessTaxId',
			'affiliate_invoices.businessId',
			'affiliate_invoices.stateName',
			'affiliate_invoices.countryId',
			'affiliate_invoices.toAddress',
			'affiliate_invoices.toBusinessTaxId',
			'affiliate_invoices.totalPrice',
			'affiliate_invoices.currency',
			'affiliate_invoices.paid',
			'affiliate_invoices.paymentEmail',
        ]);

        if ($this->firstName) {
            $this->subQuery->andWhere(Db::parseParam('affiliate_invoices.firstName', $this->firstName));
		}

		if ($this->lastName) {
            $this->subQuery->andWhere(Db::parseParam('affiliate_invoices.lastName', $this->lastName));
		}

		if ($this->number) {
            $this->subQuery->andWhere(Db::parseParam('affiliate_invoices.number', $this->number));
		}

        if ($this->zipCode) {
            $this->subQuery->andWhere(Db::parseParam('affiliate_invoices.zipCode', $this->zipCode));
		}

		if ($this->paymentEmail) {
            $this->subQuery->andWhere(Db::parseParam('affiliate_invoices.paymentEmail', $this->paymentEmail));
		}

		if ($this->paid) {
            $this->subQuery->andWhere(Db::parseParam('affiliate_invoices.paid', $this->paid));
		}

        return parent::beforePrepare();
	}

	/**
     * @inheritdoc
     */
    protected function statusCondition(string $status)
    {		
		switch ($status) {
            case Invoice::STATUS_PAID:
                return [
                    'affiliate_invoices.paid' => '1',
                ];
            case Invoice::STATUS_UNPAID:
                return [
                    'affiliate_invoices.paid' => '0',
                ];
            default:
                return parent::statusCondition($status);
        }
    }
	
}
