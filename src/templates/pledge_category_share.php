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
//$shareUrl = 'https://www.facebook.com/dialog/feed?app_id=145634995501895&display=popup&caption=An%20example%20caption&link=https%3A%2F%2Fdevelopers.facebook.com%2Fdocs%2F&redirect_uri=https://developers.facebook.com/tools/explorer';
$shareUrl = $shareData->getShareUrl();

// TODO: for twitter, if more than 1 pledge is selected, we should split it in separate iframes.

wp_redirect($shareUrl);
exit;