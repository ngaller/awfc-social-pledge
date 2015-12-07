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
    /** @var string title for the campaign */
    public $title;
    /** @var string Description, i.e. the complete pledge text */
    public $description;
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
            default:
                die('Invalid share type');
        }
    }

    public function generateMetaTags()
    {
        switch ($this->shareType) {
            case 'facebook':
            case 'gplus':
            case 'tumblr':
                $this->generateOpenGraphTags();
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
        $appId = OptionPage::getOption(OptionPage::OPTION_FACEBOOK_APPID);
        $imgUrl = wp_get_attachment_url($this->imageId);
        return "https://www.facebook.com/dialog/share?app_id=$appId&display=popup" .
        "&link=" . urlencode($this->permalink) .
        "&description=" . urlencode($this->description) .
        "&picture=" . urlencode($imgUrl) .
        "&caption=" . urlencode($this->title);
    }

    private function getShareUrlForGooglePlus()
    {
        return "https://plus.google.com/share?url=" . urlencode($this->permalink);
    }

    private function getShareUrlForTumblr()
    {
        $imgUrl = wp_get_attachment_url($this->imageId);
        $url = 'https://www.tumblr.com/widgets/share/tool?canonicalUrl=' . urlencode($this->permalink) .
            '&posttype=photo' .
            '&tags=' . urlencode($this->hashtags) .
            '&caption=' . urlencode($this->description) .
            '&content=' . urlencode($imgUrl);
        return $url;
    }

    private function getShareUrlForTwitter()
    {
        $via = OptionPage::getOption(OptionPage::OPTION_TWITTER_SCREENNAME);
        $url = 'https://twitter.com/intent/tweet?url=' . urlencode($this->permalink) .
            '&text=' . urlencode($this->description) .
            '&hashtags=' . urlencode($this->hashtags);
        if ($via) {
            $url .= '&via=' . urlencode($via);
        }
        return $url;
    }

    private function generateOpenGraphTags()
    {
        $imgUrl = wp_get_attachment_url($this->imageId);
        ?>
        <!-- Open Graph -->
        <meta property="og:type" content="article"/>
        <meta property="og:url" content="<?= $this->permalink ?>"/>
        <meta property="og:title" content="<?= $this->title ?>"/>
        <meta property="og:site_name" content="<?php bloginfo('name') ?>"/>
        <meta property="og:image" content="<?= $imgUrl ?>"/>
        <meta property="og:description" content="<?= $this->description ?>"/>
        <?php
    }

    private function generateTwitterCardTags()
    {
        $imgUrl = wp_get_attachment_url($this->imageId);
        ?>
        <!-- Twitter -->
        <meta property="twitter:card" content="summary_large_image"/>
        <meta property="twitter:title" content="<?= esc_attr($this->title) ?>"/>
        <meta property="twitter:description" content="<?= esc_attr($this->description) ?>"/>
        <meta property="twitter:image" content="<?= esc_attr($imgUrl) ?>"/>
        <?php
        $via = OptionPage::getOption(OptionPage::OPTION_TWITTER_SCREENNAME);
        if ($via) {
            ?>
            <meta property="twitter:site" content="@<?= esc_attr($via) ?>"/><?php
        }
    }
}