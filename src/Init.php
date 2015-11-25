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
     * Called on WP load - this will bootstrap the rest of the plugin.
     */
    function initialize()
    {
        // order matters!
        (new CustomPostType())->register();
        (new ButtonDef())->registerShortCode();
        (new Editor())->integrateWithVC();
    }
}