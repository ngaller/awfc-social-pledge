<?php
/**
 * TwitterMedia.php
 * Created By: nico
 * Created On: 12/17/2015
 */

namespace AWC\SocialPledge;
use Abraham\TwitterOAuth\TwitterOAuth;

/**
 * Responsible for obtaining a twitter URL (t.co) for a given picture.
 *
 * @package AWC\SocialPledge
 */
class TwitterMedia
{
    private $clientKey, $clientSecret, $accessToken, $accessTokenSecret;

    function __construct()
    {
        $opts = new OptionPage();
        $this->clientKey = $opts->getOption(OptionPage::OPTION_TWITTER_CLIENTKEY);
        $this->clientSecret = $opts->getOption(OptionPage::OPTION_TWITTER_CLIENTSECRET);
        $this->accessToken = $opts->getOption(OptionPage::OPTION_TWITTER_ACCESSTOKEN);
        $this->accessTokenSecret = $opts->getOption(OptionPage::OPTION_TWITTER_ACCESSTOKENSECRET);
    }

    public function getTwitterUrl($imageId)
    {
        $url = get_post_meta($imageId, 'twitter_url', true);
        // XXX should we test it??
        if (!$url) {
            $url = $this->uploadPicture($imageId);
            update_post_meta($imageId, 'twitter_url', $url);
        }
        return $url;
    }

    /**
     * Upload picture to Twitter media and create post to retrieve the t.co URL
     *
     * @param int $imageId
     * @return string
     */
    private function uploadPicture($imageId)
    {
        $path = $this->getImagePath($imageId);
        $title = get_the_title($imageId);
        $connection = new TwitterOAuth($this->clientKey, $this->clientSecret,
            $this->accessToken, $this->accessTokenSecret);
        $media = $connection->upload('media/upload', ['media' => $path]);
        if ($connection->getLastHttpCode() != 200) {
            error_log('Error uploading media to twitter: ' . $connection->getLastHttpCode());
            return '';
        }
        $status = $connection->post('statuses/update', [
            'status' => $title,
            'media_ids' => $media->media_id_string
        ]);
        if ($connection->getLastHttpCode() != 200) {
            error_log('Error posting image to twitter: ' . $connection->getLastHttpCode());
            return '';
        }
        return $this->extractImageUrl($status->text);
    }

    private function extractImageUrl($statusText)
    {
        $matches = [];
        if (preg_match('#https://t.co/\w+#', $statusText, $matches)) {
            return $matches[0];
        } else {
            error_log('Could not find t.co reference in ' . $statusText);
            return '';
        }
    }

    /**
     * Return local path to "large" version of the image
     *
     * @param int $imageId
     * @return string
     */
    private function getImagePath($imageId)
    {
        $path = get_attached_file($imageId);
        $resized = image_get_intermediate_size($imageId, AWC_SOCIAL_PLEDGE_SHARE_IMAGE_SIZE);
        if ($resized) {
            $path = str_replace(basename($path), $resized['file'], $path);
        }
//        $uploadPath = get_option('upload_path');
//        if (!$uploadPath)
//            $uploadPath = WP_CONTENT_DIR . '/uploads';
        return $path;
    }
}