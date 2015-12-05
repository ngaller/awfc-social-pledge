<?php


namespace AWC\SocialPledge;

/**
 * SocialCampaign.php
 * This is used to define the campaign that a portfolio is associated with (custom Wordpress taxonomy)
 *
 * @package AWC\SocialPledge
 */
class SocialCampaign
{
    const TAXONOMY = "social_campaign";
    const PORTFOLIO_POST_TYPE = "portfolio";

    /**
     * Register custom taxonomy with WP and associate with Portfolio custom post type.
     */
    public function register()
    {
        $this->registerTaxonomy();
    }

    private function registerTaxonomy()
    {
        register_taxonomy(self::TAXONOMY, self::PORTFOLIO_POST_TYPE, [
            'labels' => [
                'name' => 'Social Campaigns',
                'singular_name' => 'Social Campaign',
                'add_new_item' => 'Add New Campaign (Name = Hashtag, Description = Instructions)'
            ],
            'public' => false,
            'rewrite' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_tagcloud' => false,
            'show_in_quick_edit' => true,
            'show_admin_column' => true,
            'query_var' => false,
            'description' => 'Used to associate portfolio items with a specific campaign'
        ]);
    }
}