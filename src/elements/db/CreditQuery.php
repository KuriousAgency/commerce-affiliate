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

use kuriousagency\affiliate\elements\Credit;

use DateTime;
use DateInterval;

class CreditQuery extends ElementQuery
{
	public $userId;
	
	public $status;

	public $orderId;

	public $invoiceId;

	public $credits;
	
	 /**
     * @var array
     */
	// protected $defaultOrderBy = ['affiliate_credits.dateCreated' => SORT_DESC];
	

	// Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function __construct(string $elementType, array $config = [])
    {
        // Default status
        if (!isset($config['status'])) {
            $config['status'] = Credit::STATUS_PENDING;
        }

        parent::__construct($elementType, $config);
	}

	public function userId($value)
    {
        $this->userId = $value;

        return $this;
	}
	
	public function orderId($value)
    {
        $this->orderId = $value;

        return $this;
    }

    public function invoiceId($value)
    {
        $this->invoiceId = $value;

        return $this;
	}
	
	public function totalPrice($value)
    {
        $this->totalPrice = $value;

        return $this;
	}

    protected function beforePrepare(): bool
    {
		
		$this->joinElementTable('affiliate_credits');
		$this->subQuery->innerJoin('{{%users}} users', '[[affiliate_credits.userId]] = [[users.id]]');
		
		// $this->joinElementTable('affiliate_credits');
        $this->subQuery->innerJoin('{{%commerce_orders}} orders', '[[affiliate_credits.orderId]] = [[orders.id]]');
		
		
		// select the columns
        $this->query->select([
            'affiliate_credits.id',
            'affiliate_credits.userId',
            'affiliate_credits.orderId',
			'affiliate_credits.totalPrice',
			'affiliate_credits.invoiceId',
            'affiliate_credits.dateCreated',
        ]);

        if ($this->userId) {
            $this->subQuery->andWhere(Db::parseParam('affiliate_credits.userId', $this->userId));
		}

        if ($this->orderId) {
            $this->subQuery->andWhere(Db::parseParam('affiliate_credits.orderId', $this->orderId));
		}
		
		if ($this->invoiceId) {
            $this->subQuery->andWhere(Db::parseParam('affiliate_credits.invoiceId', $this->invoiceId));
        }

        return parent::beforePrepare();
	}

	 /**
     * @inheritdoc
     */
    protected function statusCondition(string $status)
    {
		// $currentTimeDb = Db::prepareDateForDb(new \DateTime());
		$pendingDate = new DateTime();
		$pendingDate->sub(new DateInterval('P'.Affiliate::getInstance()->getSettings()->pendingDays.'D'));

		$pendingDateDb = Db::prepareDateForDb($pendingDate);

        switch ($status) {
            case Credit::STATUS_PENDING:
                return [
					'and',
                    ['>', 'affiliate_credits.dateCreated', $pendingDateDb]
				];
			case Credit::STATUS_APPROVED:
                return [
					'and',
                    ['<=', 'affiliate_credits.dateCreated', $pendingDateDb]
				];
			case Credit::STATUS_INVOICED:
                return [
					'and',
                    ['>', 'affiliate_credits.invoiceId', 0]
                ];
           
            default:
                return parent::statusCondition($status);
        }
    }
	
}
