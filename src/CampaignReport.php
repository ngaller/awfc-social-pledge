<?php
/**
 * CampaignReport.php
 * Created By: nico
 * Created On: 12/26/2015
 */

namespace AWC\SocialPledge;

/**
 * Used to output the report
 *
 * @package AWC\SocialPledge
 */
class CampaignReport
{
    public function onWpLoaded()
    {
        add_action('wp_ajax_awfc-social-campaign-report', [$this, 'onSocialCampaignReport']);
    }

    public function onSocialCampaignReport()
    {
        global $wpdb;

        if (!isset($_GET['campaign']))
            return;

        $campaign = $_GET['campaign'];
        $sql = "select date_format(p.post_date, '%%Y-%%m-%%d'), count(*), ifnull(sum(pm_opc.meta_value),0)
from $wpdb->posts p
join $wpdb->postmeta pm_ip on pm_ip.post_id = p.ID and pm_ip.meta_key = 'client_ip'
left join $wpdb->postmeta pm_opc on pm_opc.post_id = p.ID and pm_opc.meta_key = 'open_count'
where p.post_type = %s and p.post_title like %s
group by pm_ip.meta_value, date_format(p.post_date, '%%Y-%%m-%%d')";
        $sth = $wpdb->prepare($sql, SocialSharePostType::POST_TYPE, $campaign . '%');
        $rows = $wpdb->get_results($sth, ARRAY_N);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename=CampaignReport.csv');
        echo "Share Date,Share Count,Open Count\n";
        foreach ($rows as $row) {
            echo "$row[0],$row[1],$row[2]\n";
        }
        wp_die();
    }
}