<?php

namespace AWC\SocialPledge;

/**
 * SharingMeta.php
 * This is used to include the meta tags on the page when the special sharing tags are present.
 *
 * @package AWC\SocialPledge
 */
class SharingMetaData
{
    /** @var int attachment id */
    public $imageId;
    /** @var string URL to the social-share post */
    public $permalink;
    /** @var string URL to redirect the users to */
    public $homepageUrl;
    /** @var string complete pledge text (for Twitter, it will be the shorter version) */
    public $pledgeText;
    /** @var string Lead text (this is used differently depending on the social network) */
    public $title;
    /** @var string Hashtags, comma separated list */
    public $hashtags;
    /** @var string social network identifier: gplus, facebook, tumblr, twitter */
    public $shareType;
    /** @var int id for the campaign term */
    public $campaignId;

    /**
     * Return share URL appropriate for the post type.
     *
     * @return string
     */
    public function getShareUrl()
    {
        switch ($this->shareType) {
            case 'facebook':
                return $this->getShareUrlForFacebook();
            case 'gplus':
                return $this->getShareUrlForGooglePlus();
            case 'tumblr':
                return $this->getShareUrlForTumblr();
            case 'twitter':
                return $this->getShareUrlForTwitter();
            case 'count-only':
                return '';
            default:
                die('Invalid share type');
        }
    }

    public function generateMetaTags()
    {
        switch ($this->shareType) {
            case 'gplus':
                $this->generateGooglePlusTags();
                break;
            case 'facebook':
                $this->generateFacebookTags();
                break;
            case 'tumblr':
                // don't think this is used right now.. so we'll just do the Facebook ones
                $this->generateFacebookTags();
                break;
            case 'twitter':
                $this->generateTwitterCardTags();
                break;
            default:
                die('Invalid share type');
        }
    }

    private function getShareUrlForFacebook()
    {
        //https://www.facebook.com/dialog/feed?app_id=145634995501895&display=popup&caption=An%20example%20caption&link=https%3A%2F%2Fdevelopers.facebook.com%2Fdocs%2F&redirect_uri=https://developers.facebook.com/tools/explorer
        $appId = OptionPage::getAWCOption(OptionPage::OPTION_FACEBOOK_APPID);
        $imgUrl = self::getImageUrl($this->imageId);
        $return = add_query_arg('return', '1', $this->permalink);
        return "https://www.facebook.com/dialog/share?app_id=$appId&display=popup" .
        "&redirect_uri=" . urlencode($return) .
        "&href=" . urlencode($this->permalink) .
        "&description=" . urlencode($this->pledgeText) .
        "&picture=" . urlencode($imgUrl);
        // this caption will be displayed at the bottom, but we'd rather just show the site's name there
//        "&caption=" . urlencode($this->title);
    }

    private function getShareUrlForGooglePlus()
    {
        return "https://plus.google.com/share?url=" . urlencode($this->permalink);
    }

    private function getShareUrlForTumblr()
    {
        $imgUrl = self::getImageUrl($this->imageId);
        $description = $this->pledgeText;
        if ($this->title)
            $description = $this->pledgeText . ' ' . $this->title;
        $url = 'https://www.tumblr.com/widgets/share/tool?canonicalUrl=' . urlencode($this->permalink) .
            '&posttype=photo' .
            '&tags=' . urlencode($this->hashtags) .
            '&caption=' . urlencode($description) .
            '&content=' . urlencode($imgUrl);
        return $url;
    }

    private function getShareUrlForTwitter()
    {
        $via = OptionPage::getAWCOption(OptionPage::OPTION_TWITTER_SCREENNAME);
        $twitterMedia = new TwitterMedia();
        $pic = $twitterMedia->getTwitterUrl($this->imageId);
        $statusText = $this->pledgeText;
        if ($this->title) {
            $statusText = $statusText . ' ' . $this->title;
        }
        $statusText = $statusText . ' ' . $pic;

        $url = 'https://twitter.com/intent/tweet?url=' . urlencode($this->permalink) .
            '&text=' . urlencode($statusText);
        if ($this->hashtags && strpos($statusText, '#') === false) {
            $url .= '&hashtags=' . urlencode($this->hashtags);
        }
        if ($via) {
            $url .= '&via=' . urlencode($via);
        }
        return $url;
    }

    private function generateFacebookTags()
    {
        $imgUrl = self::getImageUrl($this->imageId);
        //$img = image_downsize($this->imageId, 'thumbnail');

        ?>
        <!-- Open Graph -->
        <meta property="og:type" content="article"/>
        <meta property="og:url" content="<?= esc_attr($this->permalink) ?>"/>
        <meta property="og:title" content="<?= esc_attr($this->title) ?>"/>
        <!--        <meta property="og:site_name" content=""/> -->
        <meta property="og:image" content="<?= esc_attr($imgUrl) ?>"/>
        <meta property="og:description" content="<?= esc_attr($this->pledgeText) ?>"/>
        <meta property="og:headline" content="<?= esc_attr($this->title) ?>"/>
        <?php
    }

    private function generateGooglePlusTags()
    {
        $imgUrl = self::getImageUrl($this->imageId);
        // this is opengraph, like for facebook, but we put the description as a title, because G+ does not post the
        // description apparently??
        $description = $this->title . '  ' . $this->pledgeText;
        ?>
        <!-- Open Graph -->
        <meta property="og:type" content="article"/>
        <meta property="og:url" content="<?= esc_attr($this->permalink) ?>"/>
        <meta property="og:title" content="<?= esc_attr($description) ?>"/>
        <!--        <meta property="og:site_name" content=""/> -->
        <meta property="og:image" content="<?= esc_attr($imgUrl) ?>"/>
        <!--        <meta property="og:description" content=""/> -->
        <meta property="og:headline" content="<?= esc_attr($description) ?>"/>
        <title><?= esc_html($this->title) ?></title>
        <?php
    }

    // we don't actually use the Twitter card right now, but just in case...
    private function generateTwitterCardTags()
    {
        $imgUrl = self::getImageUrl($this->imageId);
        ?>
        <!-- Twitter -->
        <meta property="twitter:card" content="summary_large_image"/>
        <meta property="twitter:title" content="<?= esc_attr($this->title) ?>"/>
        <meta property="twitter:description" content="<?= esc_attr($this->pledgeText) ?>"/>
        <meta property="twitter:image" content="<?= esc_attr($imgUrl) ?>"/>
        <?php
        $via = OptionPage::getAWCOption(OptionPage::OPTION_TWITTER_SCREENNAME);
        if ($via) {
            ?>
            <meta property="twitter:site" content="@<?= esc_attr($via) ?>"/><?php
        }
    }

    /**
     * Uses the Social Campaign data to populate the metadata options.
     * @param array $campaignData
     */
    public function applyCampaignData($campaignData)
    {
        if (isset($campaignData['hashtags'])) {
            $this->hashtags = $campaignData['hashtags'];
        }
        if (isset($campaignData['homepage'])) {
            $this->homepageUrl = $campaignData['homepage'];
        }
        if ($this->shareType == 'gplus') {
            $key = 'google+';
        } else {
            $key = strtolower($this->shareType);
        }
        if (isset($campaignData[$key])) {
            $this->title = $campaignData[$key];
        }
    }

    private static function getImageUrl($imageId) {
        // Use the same thumbnail as the one used on the sharing dialog, but max screen size (which should be at least 768px, 
        // sufficient for a good resolution image on FB / G+)
        $image = PledgeDialogData::getPledgeThumbnailById(0, $imageId);

        return $image[0];
    }

}
