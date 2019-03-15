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
class UserTracking extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
	public $id;
	
	public $userId;

	public $trackingId;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['userId', 'trackingId'], 'required'],
        ];
	}

	public function getUser(): array
    {
        return $this->userId ? Craft::$app->getUsers()->getUserById($this->userId) : [];
	}

	
}
