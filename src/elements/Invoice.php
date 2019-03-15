<?php
/**
 * Affiliate plugin for Craft CMS 3.x
 *
 * Plugin to add affiliates to Craft Commerce
 *
 * @link      https://kurious.agency
 * @copyright Copyright (c) 2019 Kurious Agency
 */

namespace kuriousagency\affiliate\elements;

use kuriousagency\affiliate\Affiliate;

use Craft;
use craft\base\Element;
use craft\elements\db\ElementQuery;
use craft\elements\db\ElementQueryInterface;

use kuriousagency\affiliate\records\Invoice as InvoiceRecord;
use kuriousagency\affiliate\elements\db\InvoiceQuery;
use kuriousagency\affiliate\AffiliateService;
use kuriousagency\affiliate\elements\actions\InvoicePaid;
use craft\commerce\Plugin as Commerce;
use craft\helpers\UrlHelper;
use craft\commerce\helpers\Currency;

use DateTime;
use DateInterval;


/**
 * @author    Kurious Agency
 * @package   Affiliate
 * @since     1.0.0
 */
class Invoice extends Element
{
	
	// Constants
    // =========================================================================

    /**
     * @var string
     */
    const STATUS_PAID = 'paid';

    /**
     * @var string
     */
	const STATUS_UNPAID = 'unpaid';
	
	
	// Public Properties
    // =========================================================================

    /**
     * @var string
     */

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

	public $currency;

	public $totalPrice;

	public $paid;

	public $paymentEmail;

	
    // Static Methods
    // =========================================================================

	/**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('affiliate', '');
    }

    /**
     * @inheritdoc
     */
    public static function hasContent(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function hasTitles(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function isLocalized(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function find(): ElementQueryInterface
    {
        return new InvoiceQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    protected static function defineSources(string $context = null): array
    {
        $sources = [

            '*' => [
                'key' => '*',
                'label' => Craft::t('affiliate', 'All'),
                'defaultSort' => ['dateCreated', 'desc']
			],

			'paid' => [
                'key' => 'paid',
				'label' => Craft::t('affiliate', 'Paid'),
				'criteria' => ['paid' => true],
                'defaultSort' => ['dateCreated', 'desc']
			],

			'unpaid' => [
                'key' => 'unpaid',
				'label' => Craft::t('affiliate', 'Unpaid'),
				'criteria' => ['paid' => 'not 1'],
				'defaultSort' => ['dateCreated', 'desc']
			],

		];

        return $sources;
    }

    // Public Methods
	// =========================================================================
	
	 /**
     * @inheritdoc
     */
    public function __toString()
    {
        return $this->getShortNumber();
	}
	
	/**
     * @inheritdoc
     */
    public function rules()
    {
		$rules = parent::rules();

        $rules[] = [['userId'], 'required'];

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function getIsEditable(): bool
    {
        return true;
	}

	/**
     * @return string
     */
    public function getShortNumber(): string
    {
        return substr($this->number, 0, 7);
    }
	
	public static function hasStatuses(): bool
    {
        return true;
	}

	/**
    * @inheritdoc
    */
    public function getStatus()
    {
		return $this->paid ? self::STATUS_PAID : self::STATUS_UNPAID;
	}


    /**
     * Paid status represented as HTML
     *
     * @return string
     */
    public function getPaidStatusHtml(): string
    {
        switch ($this->getStatus()) {
            case self::STATUS_PAID:
                {
                    return '<span class="invoiceStatusLabel"><span class="status green"></span> ' . Craft::t('affiliate', 'Paid') . '</span>';
                }
            case self::STATUS_UNPAID:
                {
                    return '<span class="invoiceStatusLabel"><span class="status red"></span> ' . Craft::t('affiliate', 'Unpaid') . '</span>';
                }
        }

        return '';
    }

	 /**
     * @inheritdocs
     */
    public static function statuses(): array
    {
        // return [
        //     self::STATUS_PAID => Craft::t('affiliate', 'Paid'),
        //     self::STATUS_UNPAID => Craft::t('affiliate', 'Unpaid'),
		// ];
		
		return [];
	}

	 /**
     * @inheritdoc
     */
    public function getCpEditUrl(): string
    {
        return UrlHelper::cpUrl('affiliate/invoices/' . $this->id);
    }

	protected static function defineTableAttributes(): array
    {
        return [
			'title' => ['label' => Craft::t('affiliate', 'Reference')],
			'name' => ['label' => Craft::t('affiliate', 'Name')],
			'paymentEmail' => ['label' => Craft::t('affiliate', 'Payment Email')],
			'totalPrice' => ['label' => Craft::t('affiliate', 'Total Price')],
			'paidStatus' => ['label' => Craft::t('affiliate', 'Paid Status')],
		];

	}

	protected static function defineActions(string $source = null): array
    {
	
		$actions = parent::defineActions($source);
		
		// Only allow actions for for unpaid orders
		$isStatus = strpos($source, 'unpaid');
		
		if ($isStatus === 0) {

			$actions[] = Craft::$app->getElements()->createAction([
				'type' => InvoicePaid::class,
				'confirmationMessage' => Craft::t('affiliate', 'Are you sure you want to mark the orders as paid'),
				'successMessage' => Craft::t('affiliate', 'Invoices marked as paid.'),
				'failMessage' => Craft::t('affiliate', 'Error.'),
			]);
		}

		return $actions;
	}
	
	
	/**
     * @inheritdoc
     */
    protected function tableAttributeHtml(string $attribute): string
    {
        switch ($attribute) {
			case 'name':
				{
					$url = UrlHelper::cpUrl('users/' . $this->userId);
					return $url ? '<a href="' . $url . '">' . $this->firstName . ' ' . $this->lastName . '</a>' : '';
				}
			case 'paidStatus':
                {
                    return $this->getPaidStatusHtml();
				}
			case 'totalPrice':
				{
				$totalPrice = Currency::round($this->totalPrice);

				return Craft::$app->getFormatter()->asCurrency($totalPrice, $this->currency);
				}
            default:
                {
                    return parent::tableAttributeHtml($attribute);
                }
        }
	}
	
	 /**
     * @inheritdoc
     */
    protected static function defineSortOptions(): array
    {
        return [
            [
                'label' => Craft::t('affiliate', 'Date Created'),
                'orderBy' => 'affiliate_invoices.dateCreated',
                'attribute' => 'dateCreated'
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function afterSave(bool $isNew)
    {
		if (!$isNew) {
            $invoiceRecord = InvoiceRecord::findOne($this->id);

            if (!$invoiceRecord) {
                throw new InvalidConfigException('Invalid invoice id: ' . $this->id);
            }
        } else {
            $invoiceRecord = new InvoiceRecord();
            $invoiceRecord->id = $this->id;
        }

        $invoiceRecord->number = $this->number;
        $invoiceRecord->userId = $this->userId;
        $invoiceRecord->firstName = $this->firstName;
        $invoiceRecord->lastName = $this->lastName;
        $invoiceRecord->address1 = $this->address1;
        $invoiceRecord->address2 = $this->address2;
        $invoiceRecord->city = $this->city;
        $invoiceRecord->zipCode = $this->zipCode;
        $invoiceRecord->phone = $this->phone;
        $invoiceRecord->alternativePhone = $this->alternativePhone;
        $invoiceRecord->businessName = $this->businessName;
        $invoiceRecord->businessTaxId = $this->businessTaxId;
        $invoiceRecord->businessId = $this->businessId;
        $invoiceRecord->stateName = $this->stateName;
        $invoiceRecord->countryId = $this->countryId;
        $invoiceRecord->totalPrice = $this->totalPrice;
        $invoiceRecord->currency = $this->currency;
        $invoiceRecord->paid = $this->paid;	
        $invoiceRecord->paymentEmail = $this->paymentEmail;

        $invoiceRecord->save(false);

        parent::afterSave($isNew);

	}
	
}
