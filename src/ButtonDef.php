<?php

namespace AWC\SocialPledge;

/**
 * Shortcode definition for "Social Pledge" button.
 *
 * @package AWC\SocialPledge
 */
class ButtonDef
{
    public function registerShortCode()
    {
        add_shortcode('awc_social_pledge_button', [$this, 'render']);
        wp_register_script('awc-social-pledge-button', plugins_url('assets/js/awc_social_pledge.js', __DIR__),
            [], false, true);
    }

    public function render($atts)
    {
        $atts = shortcode_atts(['category' => '', 'category2' => ''], $atts);

        $category = $atts['category'] . ',' . $atts['category2'];
        $link = get_term_link($category, CustomPostType::TAXONOMY);
        $html = "<a class='social-pledge-button' href='$link'>Pledge</a>";
//        $html = "<button class='social-pledge-button' data-pledge-category='$category'>Pledge</button>";

        wp_enqueue_script('awc-social-pledge-button');
        return $html;
    }
}