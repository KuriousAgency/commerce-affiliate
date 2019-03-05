<?php
/**
 * Affiliate plugin for Craft CMS 3.x
 *
 * Plugin to add affiliates to Craft Commerce
 *
 * @link      https://kurious.agency
 * @copyright Copyright (c) 2019 Kurious Agency
 */

namespace kuriousagency\affiliate\assetbundles\Affiliate;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    Kurious Agency
 * @package   Affiliate
 * @since     1.0.0
 */
class AffiliateAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = "@kuriousagency/affiliate/assetbundles/affiliate/dist";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/Affiliate.js',
        ];

        $this->css = [
            'css/Affiliate.css',
        ];

        parent::init();
    }
}
