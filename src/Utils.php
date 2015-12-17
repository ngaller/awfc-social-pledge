<?php
/**
 * Utils.php
 * Created By: nico
 * Created On: 12/6/2015
 */

namespace AWC\SocialPledge;


class Utils
{
    /**
     * Return image id given an attachment URL
     *
     * @param $url
     * @return int
     */
    public static function getAttachmentId($url)
    {
        /** @var \WP_Query */
        global $wpdb;

        if (is_numeric($url)) {
            return intval($url);
        }

        // remove the part before /uploads, because it will have the CDN instead of the real attachment url
        $url = preg_replace('/^.*(\/uploads\/)/', '$1', $url);
        /** @noinspection SqlDialectInspection */
        $rs = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid like '%s';", '%' . $url));
        return $rs[0];
    }
}