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

use craft\commerce\Plugin as Commerce;

/**
 * @author    Kurious Agency
 * @package   Affiliate
 * @since     1.0.0
 */
class OrderTracking extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
	public $id;
	
	public $orderId;

	public $trackingId;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['orderId', 'trackingId'], 'required'],
        ];
	}

	public function getOrder(): array
    {
        return $this->orderId ? Commerce::getInstance()->getOrders()->getOrderById($this->orderId) : [];
    }
	
}
