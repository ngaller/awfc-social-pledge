<?php


namespace AWC\SocialPledge;

/**
 * SocialCampaignTaxonomy.php
 * This is used to define the campaign that a portfolio is associated with (custom Wordpress taxonomy)
 *
 * @package AWC\SocialPledge
 */
class SocialCampaignTaxonomy
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
            'hierarchical' => true,  // so it shows with checkboxes
            'show_in_menu' => true,
            'show_tagcloud' => false,
            'show_in_quick_edit' => true,
            'show_admin_column' => true,
            'query_var' => false,
            'description' => 'Used to associate portfolio items with a specific campaign'
        ]);
    }


    /**
     * Retrieve social campaign tag for the specified post (or null, if there isn't one)
     *
     * @param int $postId
     * @return \WP_Term
     */
    public static function getSocialCampaign($postId)
    {
        $terms = wp_get_object_terms($postId, self::TAXONOMY);
        if (!empty($terms)) {
            if (!is_wp_error($terms)) {
                return $terms[0];
            } else {
                error_log('Error retrieving terms: ' . $terms->get_error_message());
            }
        }
        return null;
    }

    /**
     * Given a term object (social campaign), this returns the associative array of key = value pairs defined
     * within the term's description.
     *
     * @see SharingMetaData::applyCampaignData
     * @param $term
     * @return array
     */
    public static function parseSocialCampaign($term)
    {
        $src = $term->description;
        $result = [];
        foreach (explode("\n", $src) as $line) {
            $parts = explode('=', $line);
            if (count($parts) == 2) {
                $result[rtrim($parts[0])] = ltrim($parts[1]);
            }
        }
        return $result;
    }
}