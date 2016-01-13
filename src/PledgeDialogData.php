<?php
/**
 * PledgeData.php
 * Created By: nico
 * Created On: 12/5/2015
 */

namespace AWC\SocialPledge;

/**
 * Logic for the pledge dialog data (generated in pledge_category.php)
 *
 * @package AWC\SocialPledge
 */
class PledgeDialogData
{
    private $imageId;
    private $socialCampaign;
    private $parentId;

    /**
     * PledgeDialogData constructor.
     * @param string $img URL of image to associate with the share
     * @param int $parentPostId
     */
    function __construct($img, $parentPostId = 0)
    {
        $this->imageId = Utils::getAttachmentId($img);
        $this->socialCampaign = SocialCampaignTaxonomy::getSocialCampaign($parentPostId);
        $this->parentId = $parentPostId;
    }

    /**
     * Return hashtags for the social campaign (empty string if not set)
     *
     * @return string
     */
    public function getHashtags()
    {
        if ($this->socialCampaign) {
            return str_replace(' ', '', $this->socialCampaign->name);
        }
        return '';
    }

    /**
     * Return instructions for the dialog, as extracted from the Social Campaign (empty string if not set)
     *
     * @return string
     */
    public function getInstructions()
    {
        if ($this->socialCampaign) {
            $data = SocialCampaignTaxonomy::parseSocialCampaign($this->socialCampaign);
            if(isset($data['instructions']))
                return $data['instructions'];
        }
        return '';
    }

    /**
     * Return a URL
     *
     * @return string
     */
    public function getShareUrl()
    {
        $url = home_url('/');
        $url .= '?pid=' . $this->parentId;
        if ($this->imageId) {
            $url .= '&img=' . $this->imageId;
        }
        return $url;
    }

    /**
     * Return image info for the pledge thumbnail
     *
     * @param int $screenWidth
     * @return array - URL, width, height
     */
    public function getPledgeThumbnail($screenWidth)
    {
        if (empty($this->imageId))
            return false;
        $width = $this->getPledgeThumbnailWidth($screenWidth);

        // use the "large" image size.  Picking a pre-determined size will allow us to watermark those specific images,
        // even though the selected size may be too large for the device
        $image = image_downsize($this->imageId, AWC_SOCIAL_PLEDGE_SHARE_IMAGE_SIZE);
        // scale the image via width / height
        $size = image_constrain_size_for_editor($image[1], $image[2], [$width, $width]);
        return [$image[0], $size[0], $size[1]];
    }


    /**
     * Calculate optimal width for the thumbnail image, based on the screen_width parameter
     *
     * @param int $screenWidth
     * @return int
     */
    public function getPledgeThumbnailWidth($screenWidth)
    {
        if (!$screenWidth) {
            $screenWidth = 768;
        }
        if ($screenWidth < 600) {
            $width = $screenWidth - 40;  // 40 being the padding
        } else {
            $width = 500;
        }
        return $width;
    }

}