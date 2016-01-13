<?php

namespace AWC\SocialPledge;

/**
 * Shortcode definition for "Social Pledge" button.
 *
 * @package AWC\SocialPledge
 */
class ShortcodeDef
{
    private $cachedCampaignData = null;

    public function registerShortCode()
    {
        add_shortcode('awc_social_pledge_button', [$this, 'renderButton']);
        add_shortcode('awc_social_pledge_summary', [$this, 'renderSummary']);
        wp_register_script('awc-social-pledge-button', plugins_url('assets/js/awc_social_pledge.js', __DIR__),
            [], false, true);
    }

    public function renderButton($atts)
    {
        $atts = shortcode_atts(['category' => '', 'category2' => '', 'image_override' => ''], $atts);

        //$link = '/?' . CustomPostType::TAXONOMY . '=' . urlencode($category);
        $link = get_term_link($atts['category'], PledgePostType::TAXONOMY);
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

        // get image override, if specified
        $img_override = '';
        if (!empty($atts['image_override'])) {
            $img_override = " data-image-override='" . esc_attr($atts['image_override']) . "'";
        }

        // get pledge button image specified at campaign level
        $style = '';
        $campaignName = '';
        $campaign = $this->getCampaignData(get_the_ID());
        if ($campaign) {
            $campaignName = $campaign['name'];
            if (!empty($campaign['pledge-button'])) {
                $style = 'style="background-image: url(\'' . esc_attr($campaign['pledge-button']) . '\')"';
            }
        }

        $html = "<a class='social-pledge-button $campaignName' href='$link' " .
            "data-pledge-categories='$esc_category' $img_override $style>Pledge</a>";

        wp_enqueue_script('awc-social-pledge-button');
        return $html;
    }

    public function renderSummary($atts)
    {
        $atts = shortcode_atts(['image_override' => ''], $atts);
        wp_enqueue_script('awc-social-pledge-button');
        $image_override = '';
        if (!empty($atts['image_override'])) {
            $image_override = ' data-image-override="' . esc_attr($atts['image_override']) . '"';
        }
        return '<div class="social-pledge-summary"' . $image_override . '></div>';
    }

    private function getCampaignData($postId)
    {
        if ($this->cachedCampaignData !== null)
            return $this->cachedCampaignData;
        $campaign = SocialCampaignTaxonomy::getSocialCampaign($postId);
        if ($campaign) {
            $this->cachedCampaignData = SocialCampaignTaxonomy::parseSocialCampaign($campaign);
        } else {
            $this->cachedCampaignData = false;
        }
        return $this->cachedCampaignData;
    }
}