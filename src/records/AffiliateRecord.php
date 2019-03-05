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

/**
 * @author    Kurious Agency
 * @package   Affiliate
 * @since     1.0.0
 */
class AffiliateRecord extends ActiveRecord
{
    // Public Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%affiliate_affiliaterecord}}';
    }
}
