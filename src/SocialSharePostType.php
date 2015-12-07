<?php
/**
 * SocialSharePostType.php
 * Created By: nico
 * Created On: 12/6/2015
 */

namespace AWC\SocialPledge;

/**
 * Define post type used to track social shares.
 *
 * @package AWC\SocialPledge
 */
class SocialSharePostType
{
    const POST_TYPE = 'social_share';

    public function register()
    {
        $this->registerPostType();
        $this->registerTemplate();
    }

    /**
     * Create a "Social Share" post associated with the specified image, campaign, selected pledges.
     * Return the completed metadata.
     *
     * @return SharingMetaData
     */
    public static function createSocialShare($imgUrl, $shareType, $parentId, $selectedPledgeIds)
    {
        $shareData = new SharingMetaData();
        $shareData->description = PledgePostType::getSelectedPledgeText($selectedPledgeIds);
        $shareData->title = 'Site title?  Or somethign else?';
        $shareData->imageId = Utils::getAttachmentId($imgUrl);
        $shareData->shareType = $shareType;
        $campaign = SocialCampaignTaxonomy::getSocialCampaign($parentId);
        if ($campaign) {
            // strip hash just in case they included them
            $shareData->hashtags = str_replace('#', '', $campaign->name);
            $shareData->campaignId = $campaign->term_id;
        }
        $postId = wp_insert_post([
            'post_type' => self::POST_TYPE,
            'post_content' => urlencode(serialize($shareData)),
            'post_status' => 'publish'
        ]);
        $shareData->permalink = get_permalink($postId);
//        update_post_meta($postId, 'selected_pledges', $selectedPledgeIds);
//        update_post_meta($postId, 'share_type', )

        return $shareData;
    }

    /**
     * Uses the current post's metadata to generate a SharingMetaData object, that can be used to
     * output the meta tags used by social network crawlers.
     *
     * @param int $postId ID of social share post (or 0 to use current post)
     * @return SharingMetaData
     */
    public static function getSocialMetaData($postId = null)
    {
        /** @var \WP_Post $post */
        $post = get_post($postId);
        $serialized = $post->post_content;
        if (!$serialized) {
            die("Empty sharing data");
        }
        $serialized = urldecode($serialized);
        $shareData = unserialize($serialized);
        if (!$shareData) {
            die("Invalid sharing data");
        }
        $shareData->permalink = get_permalink($post->ID);

        return $shareData;
    }

    private function registerPostType()
    {
        register_post_type(self::POST_TYPE,
            [
                'labels' => [
                    'name' => __(sprintf('%ss', ucwords(str_replace("_", " ", self::POST_TYPE)))),
                    'singular_name' => __(sprintf('%s', ucwords(str_replace("_", " ", self::POST_TYPE))))
                ],
                'public' => false,
                'publicly_queryable' => true,  // let them access these posts on the front end
                'has_archive' => false,
                'hierarchical' => false,
                'description' => __(sprintf('%s', ucwords(str_replace("_", " ", self::POST_TYPE)))),
                'supports' => []
            ]);

    }

    /**
     * Using filters, register a custom template for the "Social Share" post type.
     * This template will be used both to track opens and to provide the metadata for the social networks.
     */
    private function registerTemplate()
    {
        add_filter('single_template', [$this, 'getSingleTemplateFilter']);
    }

    public function getSingleTemplateFilter($template)
    {
        global $post;

        if ($post->post_type == self::POST_TYPE) {
            if ($this->isCrawler()) {
                $this->trackCrawled();
                return __DIR__ . '/templates/social_share.php';
            } else if (@$_GET['return'] == '1') {
                return __DIR__ . '/templates/close_me.php';
            } else {
                $this->trackOpen();
                $this->redirect();
            }
        }
        return $template;
    }

    private function isCrawler()
    {
        $ua = $_SERVER['HTTP_USER_AGENT'];
        return stripos($ua, 'facebookexternalhit') !== false ||
        stripos($ua, 'twitterbot') !== false ||
        stripos($ua, 'linkedinbot') !== false ||
        stripos($ua, 'Google (+https://developers.google.com/+/web/snippet/)') !== false;

        // not sure what to use for tumblr
    }

    /**
     * Mark the current post as open.
     */
    private function trackOpen()
    {
        $opens = get_post_meta(get_the_ID(), 'open_count', true);
        if (empty($opens)) {
            $opens = 1;
        } else {
            $opens = intval($opens) + 1;
        }
        update_post_meta(get_the_ID(), 'open_count', $opens);
    }

    /**
     * Mark the current post as crawled.
     */
    private function trackCrawled()
    {
        $opens = get_post_meta(get_the_ID(), 'crawl_count', true);
        if (empty($opens)) {
            $opens = 1;
        } else {
            $opens = intval($opens) + 1;
        }
        update_post_meta(get_the_ID(), 'crawl_count', $opens);
    }

    /**
     * Perform redirect to exhibition associated with the social share post.
     */
    private function redirect()
    {
        // TODO: figure out what URL to use
        wp_redirect(get_home_url());
        exit;
    }

}