<?php
/**
 * Affiliate plugin for Craft CMS 3.x
 *
 * Plugin to add affiliates to Craft Commerce
 *
 * @link      https://kurious.agency
 * @copyright Copyright (c) 2019 Kurious Agency
 */

namespace kuriousagency\affiliate\variables;

use kuriousagency\affiliate\Affiliate;
use kuriousagency\affiliate\services\Users as UsersService;

use yii\di\ServiceLocator;

use Craft;

/**
 * @author    Kurious Agency
 * @package   Affiliate
 * @since     1.0.0
 */
class AffiliateVariable extends ServiceLocator
{
    // Public Methods
    // =========================================================================

	
	public function __construct($config = [])
    {
        $components = [
            'users' => UsersService::class,
        ];
        
        $config['components'] = $components;

        parent::__construct($config);
    }
	
	// /**
    //  * @param null $optional
    //  * @return string
    //  */
    // public function exampleVariable($optional = null)
    // {
    //     $result = "And away we go to the Twig template...";
    //     if ($optional) {
    //         $result = "I'm feeling optional today...";
    //     }
    //     return $result;
	// }
	
}
