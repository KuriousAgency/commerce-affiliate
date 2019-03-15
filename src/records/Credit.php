<?php
/**
 * Affiliate plugin for Craft CMS 3.x
 *
 * Plugin to add affiliates to Craft Commerce
 *
 * @link      https://kurious.agency
 * @copyright Copyright (c) 2019 Kurious Agency
 */

namespace kuriousagency\affiliate\records;

use kuriousagency\affiliate\Affiliate;

use Craft;
use craft\db\ActiveRecord;
use craft\records\User;
use craft\records\Element;
use yii\db\ActiveQueryInterface;

/**
 * @author    Kurious Agency
 * @package   Affiliate
 * @since     1.0.0
 */
class Credit extends ActiveRecord
{
    // Public Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%affiliate_credits}}';
	}
	
	 /**
     * Return the credit's user
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getUser(): ActiveQueryInterface
    {
        return $this->hasOne(User::class, ['userId' => 'id']);
	}

	 /**
     * @return ActiveQueryInterface
     */
    public function getElement(): ActiveQueryInterface
    {
        return $this->hasOne(Element::class, ['id' => 'id']);
    }
	
}
