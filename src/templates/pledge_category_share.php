<?php
/**
 * pledge_category_share.php
 * Intermediate page used for sharing.
 *  - generate social share post
 *  - redirect the user to the URL used to share it (depending on the "share" parameter, indicating the social network)
 *
 * Parameters:
 *  - parent_id: parent post id, used to extract campaign information
 *  - img: URL to image being shared
 *  - selected: selected ids (comma-separated)
 *
 * Created By: nico
 * Created On: 11/28/2015
 */

use AWC\SocialPledge\SocialSharePostType;

$parentId = @$_GET['parent_id'] or die("Missing parameter parent_id");
$img = @$_GET['img'] or die("Missing parameter img");
$selected = @$_GET['selected'] or die("Missing parameter selected");
$shareType = @$_GET['share'] or die("Missing parameter share");

$shareData = SocialSharePostType::createSocialShare($img, $shareType, $parentId, $selected);
$shareUrl = $shareData->getShareUrl();

if($shareUrl)
    wp_redirect($shareUrl);
exit;