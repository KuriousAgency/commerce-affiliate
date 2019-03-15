<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace kuriousagency\affiliate\web\twig;

use Craft;
use kuriousagency\affiliate\elements\db\CreditQuery;
use kuriousagency\affiliate\elements\Credit;
use kuriousagency\affiliate\elements\db\InvoiceQuery;
use kuriousagency\affiliate\elements\Invoice;

// use craft\commerce\Plugin;
use yii\base\Behavior;

/**
 * Class CraftVariableBehavior
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 2.0
 */
class CraftVariableBehavior extends Behavior
{
    /**
     * @var Plugin
     */
    // public $commerce;

    public function init()
    {
        parent::init();

        // Point `craft.commerce` to the craft\commerce\Plugin instance
        // $this->commerce = Plugin::getInstance();
    }

   

    /**
     * Returns a new CreditQuery instance.
     *
     * @param mixed $criteria
     * @return CreditQuery
     */
    public function credits($criteria = null): CreditQuery
    {
        $query = Credit::find();
        if ($criteria) {
            Craft::configure($query, $criteria);
        }
        return $query;
	} 
	

	 /**
     * Returns a new CreditQuery instance.
     *
     * @param mixed $criteria
     * @return CreditQuery
     */
    public function invoices($criteria = null): InvoiceQuery
    {
        $query = Invoice::find();
        if ($criteria) {
            Craft::configure($query, $criteria);
        }
        return $query;
    } 

   
}
