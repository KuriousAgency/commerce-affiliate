<?php
/**
 * Affiliate plugin for Craft CMS 3.x
 *
 * Plugin to add affiliates to Craft Commerce
 *
 * @link      https://kurious.agency
 * @copyright Copyright (c) 2019 Kurious Agency
 */

namespace kuriousagency\affiliate\controllers;

use kuriousagency\affiliate\Affiliate;

use Craft;
use craft\web\Controller;
use yii\web\Response;

use craft\commerce\Plugin as Commerce;

/**
 * @author    Kurious Agency
 * @package   Affiliate
 * @since     1.0.0
 */
class SettingsController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = [];

    // Public Methods
    // =========================================================================

	public function actionEdit(): Response
    {
		$settings = Affiliate::getInstance()->getSettings();
				
		$plugin = Affiliate::$plugin;

        $variables = [
            'settings' => $settings,
            'plugin'       => $plugin,
        ];

        return $this->renderTemplate('affiliate/settings/general', $variables);
    }
   
}
