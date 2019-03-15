<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace kuriousagency\affiliate\elements\actions;

use kuriousagency\affiliate\Affiliate;

use Craft;
use craft\base\ElementAction;
use craft\elements\db\ElementQueryInterface;

/**
 * Delete Element Action
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 2.0
 */
class InvoicePaid extends ElementAction
{
	
	  // Properties
    // =========================================================================

    /**
     * @var string|null The message that should be shown when an order is marked as paid
     */
    public $successMessage;

    /**
     * @var string|null The message that should be shown if the order could not be marked as paid
     */
	public $failMessage;
	
	public $confirmationMessage;
	
	// Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getTriggerLabel(): string
    {
        return Craft::t('app', 'Mark as Paid');
	}
	
	 /**
     * @inheritdoc
     */
    public function getTriggerHtml()
    {
        return '<div class="btn formsubmit">' . $this->getTriggerLabel() . '</div>';
    }

    // /**
    //  * @inheritdoc
    //  */
    // public static function isDestructive(): bool
    // {
    //     return true;
    // }

    /**
     * @inheritdoc
     */
    public function getConfirmationMessage()
    {
        return $this->confirmationMessage;
    }

    /**
     * @inheritdoc
     */
    public function performAction(ElementQueryInterface $query): bool
    {
       
        foreach ($query->all() as $element) {
			Affiliate::$plugin->invoices->markAsPaid($element->id);
            // $elementsService->deleteElement($element);
		}

        $this->setMessage($this->successMessage);

        return true;
    }
}
