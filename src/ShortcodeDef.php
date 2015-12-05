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

        //$link = '/?' . CustomPostType::TAXONOMY . '=' . urlencode($category);
        $link = get_term_link($atts['category'], CustomPostType::TAXONOMY);
        if (is_wp_error($link)) {
            $msg = 'Invalid category ' . $atts['category'] . ': ' . $link->get_error_message();
            error_log($msg);
            return '<span style="display: none">' . $msg . '</span>';
        }
        if ($atts['category2']) {
            $link .= ',' . $atts['category2'];
        }
        if (strpos($link, '?') === false) {
            $link .= '?';
        } else {
            $link .= '&';
        }
        $link .= 'parent_id=' . get_the_ID();

        $esc_category = esc_attr($atts['category'] . ',' . $atts['category2']);
        $html = "<a class='social-pledge-button' href='$link' " .
            "data-pledge-categories='$esc_category'>Pledge</a>";
        wp_enqueue_script('awc-social-pledge-button');
        return $html;
    }

    public function renderSummary(/** @noinspection PhpUnusedParameterInspection */
        $atts)
    {
        wp_enqueue_script('awc-social-pledge-button');
        return '<div class="social-pledge-summary"></div>';
    }
}