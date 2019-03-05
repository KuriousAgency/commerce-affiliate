<?php
/**
 * Affiliate plugin for Craft CMS 3.x
 *
 * Plugin to add affiliates to Craft Commerce
 *
 * @link      https://kurious.agency
 * @copyright Copyright (c) 2019 Kurious Agency
 */

namespace kuriousagency\affiliate\services;

use kuriousagency\affiliate\Affiliate;

use Craft;
use craft\base\Component;

/**
 * @author    Kurious Agency
 * @package   Affiliate
 * @since     1.0.0
 */
class AffiliateService extends Component
{
    // Public Methods
    // =========================================================================

    /*
     * @return mixed
     */
    public function exampleService()
    {
        $result = 'something';
        // Check our Plugin's settings for `someAttribute`
        if (Affiliate::$plugin->getSettings()->someAttribute) {
        }

        return $result;
    }
}
