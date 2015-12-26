<?php
/**
 * Init.php
 * Created By: nico
 * Created On: 11/24/2015
 */

namespace AWC\SocialPledge;

/**
 * Top level initialization
 *
 * @package AWC\SocialPledge
 */
class Init
{
    /**
     * Called on init - this will bootstrap the rest of the plugin.
     */
    function initialize()
    {
        // order matters!
        (new PledgePostType())->register();
        (new SocialSharePostType())->register();
        (new SocialCampaignTaxonomy())->register();
    }

    /**
     * Called on wp_loaded - for functions that depend on WP being loaded
     */
    function onWpLoaded()
    {
        (new ShortcodeDef())->registerShortCode();
        (new Editor())->integrateWithVC();
        (new OptionPage())->registerOptionPage();
        (new TwitterLogin())->onWpLoaded();
        (new CampaignReport())->onWpLoaded();
    }
}