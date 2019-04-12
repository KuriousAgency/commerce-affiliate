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

use kuriousagency\affiliate\records\Credit as CreditRecord;
use kuriousagency\affiliate\elements\db\CreditQuery;
use craft\commerce\Plugin as Commerce;
use craft\commerce\helpers\Currency;
use craft\elements\User;
use craft\helpers\UrlHelper;

use DateTime;
use DateInterval;


/**
 * @author    Kurious Agency
 * @package   Affiliate
 * @since     1.0.0
 */
class Credit extends Element
{
	
	// Constants
    // =========================================================================

    /**
     * @var string
     */
    const STATUS_APPROVED = 'approved';

    /**
     * @var string
     */
	const STATUS_PENDING = 'pending';

	 /**
     * @var string
     */
	const STATUS_INVOICED = 'invoiced';
	
	
	// Public Properties
    // =========================================================================

    /**
     * @var string
     */
	public $userId;

	public $orderId;

	public $totalPrice;

	public $invoiceId;

	private $_user;

	private $_order;

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
        return new CreditQuery(get_called_class());
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
            ]
		];

        return $sources;
    }

    // Public Methods
	// =========================================================================
	
	/**
     * @return null|string
     */
    public function __toString()
    {
        return $this->id;
    }

	
	 /**
     * Returns the User
     *
     * @return User
     */
    public function getUser()
    {
        if ( (null === $this->_user) && ($this->userId) ) {
            $this->_user = Craft::$app->getUsers()->getUserById($this->userId);
		} else {
			$this->_user = null;
		}

        return $this->_user;
	}
	
	  /**
     * Returns the order that included this subscription, if any.
     *
     * @return null|Order
     */
    public function getOrder()
    {
        if ($this->_order) {
            return $this->_order;
        }

        if ($this->orderId) {
            return $this->_order = Commerce::getInstance()->getOrders()->getOrderById($this->orderId);
        }

        return null;
	}

	public function getDateApproved()
	{
		$approvedDate = $this->dateCreated;
		$approvedDate->add(new DateInterval('P'.Affiliate::$plugin->getSettings()->pendingDays.'D'));

		return $approvedDate;
	}
	
	 /**
     * Returns the link for editing the order that purchased this license.
     *
     * @return string
     */
    public function getOrderEditUrl(): string
    {
        if ($this->orderId) {
            return UrlHelper::cpUrl('commerce/orders/' . $this->orderId);
        }

        return '';
    }
	
	
	/**
     * @inheritdoc
     */
    public function rules()
    {
		$rules = parent::rules();

        $rules[] = [['userId', 'orderId'], 'required'];

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function getIsEditable(): bool
    {
        return true;
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

		if($this->invoiceId > 0) {
			return self::STATUS_INVOICED;
		}

		$pendingDate = new DateTime();
		$pendingDate->sub(new DateInterval('P'.Affiliate::$plugin->getSettings()->pendingDays.'D'));

		return $this->dateCreated < $pendingDate ? self::STATUS_APPROVED : self::STATUS_PENDING;
	}
	
	 /**
     * @inheritdocs
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING => Craft::t('affiliate', 'Pending'),
            self::STATUS_APPROVED => Craft::t('affiliate', 'Approved'),
            self::STATUS_INVOICED => Craft::t('affiliate', 'Invoiced'),
        ];
	}


	protected static function defineTableAttributes(): array
    {
        return [
            'title' => ['label' => Craft::t('affiliate', 'Name')],
			'user' => ['label' => Craft::t('affiliate', 'User')],
			'orderLink' => ['label' => Craft::t('affiliate', 'Order')],
			'totalPrice' => ['label' => Craft::t('affiliate', 'Credits')],
			'status' => ['label' => Craft::t('affiliate', 'Status')],
			'invoiceId' => ['label' => Craft::t('affiliate', 'Invoice')],
			'dateCreated' => ['label' => Craft::t('affiliate', 'Date')],
		];

	}
	
	/**
     * @inheritdoc
     */
    protected function tableAttributeHtml(string $attribute): string
    {
        switch ($attribute) {

			case 'user':
				{
					$user = $this->getUser();
					$url = $user ? $user->getCpEditUrl() : '';

					return '<a href="' . $url . '">' . $user . '</a>';
				}
			case 'orderLink':
				{
					$url = $this->getOrderEditUrl();

					return $url ? '<a href="' . $url . '">' . Craft::t('affiliate', 'View Order') . '</a>' : '';
				}
			case 'status':
				{	
					return ucwords($this->status);
				}
			case 'totalPrice':
				{
					$totalPrice = Currency::round($this->totalPrice);

					if( (isset($this->getUser()->currency)) && ($this->getUser()->currency) ) {
						$currency = $this->getUser()->currency;
					} else {
						$currency = Commerce::getInstance()->getPaymentCurrencies()->getPrimaryPaymentCurrencyIso();
					}

					return Craft::$app->getFormatter()->asCurrency($totalPrice, $currency);
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
                'orderBy' => 'affiliate_credits.id',
                'attribute' => 'dateCreated'
            ],
        ];
    }

	
    // /**
    //  * @inheritdoc
    //  */
    // public function getFieldLayout()
    // {
    //     $tagGroup = $this->getGroup();

    //     if ($tagGroup) {
    //         return $tagGroup->getFieldLayout();
    //     }

    //     return null;
    // }

    // /**
    //  * @inheritdoc
    //  */
    // public function getGroup()
    // {
    //     if ($this->groupId === null) {
    //         throw new InvalidConfigException('Tag is missing its group ID');
    //     }

    //     if (($group = Craft::$app->getTags()->getTagGroupById($this->groupId)) === null) {
    //         throw new InvalidConfigException('Invalid tag group ID: '.$this->groupId);
    //     }

    //     return $group;
    // }

    // Indexes, etc.
    // -------------------------------------------------------------------------

    // /**
    //  * @inheritdoc
    //  */
    // public function getEditorHtml(): string
    // {
    //     $html = Craft::$app->getView()->renderTemplateMacro('_includes/forms', 'textField', [
    //         [
    //             'label' => Craft::t('app', 'Title'),
    //             'siteId' => $this->siteId,
    //             'id' => 'title',
    //             'name' => 'title',
    //             'value' => $this->title,
    //             'errors' => $this->getErrors('title'),
    //             'first' => true,
    //             'autofocus' => true,
    //             'required' => true
    //         ]
    //     ]);

    //     $html .= parent::getEditorHtml();

    //     return $html;
    // }

    // Events
    // -------------------------------------------------------------------------

    // /**
    //  * @inheritdoc
    //  */
    // public function beforeSave(bool $isNew): bool
    // {
    //     return true;
    // }

    /**
     * @inheritdoc
     */
    public function afterSave(bool $isNew)
    {
		if (!$isNew) {
            $creditRecord = CreditRecord::findOne($this->id);

            if (!$creditRecord) {
                throw new InvalidConfigException('Invalid credit id: ' . $this->id);
            }
        } else {
            $creditRecord = new CreditRecord();
            $creditRecord->id = $this->id;
        }

        $creditRecord->userId = $this->userId;
		$creditRecord->orderId = $this->orderId;
		$creditRecord->totalPrice = $this->totalPrice;
        $creditRecord->invoiceId = $this->invoiceId;

        $creditRecord->save(false);

        parent::afterSave($isNew);

	}
	




    // /**
    //  * @inheritdoc
    //  */
    // public function beforeDelete(): bool
    // {
    //     return true;
    // }

    // /**
    //  * @inheritdoc
    //  */
    // public function afterDelete()
    // {
    // }

    // /**
    //  * @inheritdoc
    //  */
    // public function beforeMoveInStructure(int $structureId): bool
    // {
    //     return true;
    // }

    // /**
    //  * @inheritdoc
    //  */
    // public function afterMoveInStructure(int $structureId)
    // {
	// }
	

	 // Protected methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected static function prepElementQueryForTableAttribute(ElementQueryInterface $elementQuery, string $attribute)
    {
        /** @var ElementQuery $elementQuery */
        if ($attribute === 'user') {
            $with = $elementQuery->with ?: [];
            $with[] = 'user';
            $elementQuery->with = $with;
            return;
        }

        if ($attribute === 'orderLink') {
            $with = $elementQuery->with ?: [];
            $with[] = 'order';
            $elementQuery->with = $with;
            return;
        }

        parent::prepElementQueryForTableAttribute($elementQuery, $attribute);
	}
	
}
