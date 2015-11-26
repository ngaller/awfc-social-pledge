<?php

namespace AWC\SocialPledge;

/**
 * Shortcode definition for "Social Pledge" button.
 *
 * @package AWC\SocialPledge
 */
class ShortcodeDef
{
    public function registerShortCode()
    {
        add_shortcode('awc_social_pledge_button', [$this, 'renderButton']);
        add_shortcode('awc_social_pledge_summary', [$this, 'renderSummary']);
        wp_register_script('awc-social-pledge-button', plugins_url('assets/js/awc_social_pledge.js', __DIR__),
            [], false, true);
    }

    public function renderButton($atts)
    {
        $atts = shortcode_atts(['category' => '', 'category2' => ''], $atts);

        $category = $atts['category'] . ',' . $atts['category2'];
        $link = '/?' . CustomPostType::TAXONOMY . '=' . urlencode($category);
        $html = "<a class='social-pledge-button' href='$link'>Pledge</a>";
        wp_enqueue_script('awc-social-pledge-button');
        return $html;
    }

    public function renderSummary(/** @noinspection PhpUnusedParameterInspection */ $atts)
    {
        wp_enqueue_script('awc-social-pledge-button');
        return '<div class="social-pledge-summary"></div>';
    }
}