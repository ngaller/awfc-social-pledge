<?php
/**
 * pledge_category_sharing.php
 * Alternate template used for rendering a page for the benefit of social network crawler.
 *
 * Created By: nico
 * Created On: 11/28/2015
 */

function maybe_redirect()
{
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'facebookexternalhit') === false) {
        header('Location: ' . $_GET['url']);
    }
}

// Return image information (url, width, height), based on the img parameter.
function get_pledge_thumbnail()
{
    global $wpdb;

    if (isset($_GET['img']))
        $attachment_url = $_GET['img'];
    else
        return false;
    $width = 700;

    // remove the part before /uploads, because it will have the CDN instead of the real attachment url
    $attachment_url = preg_replace('/^.*(\/uploads\/)/', '$1', $attachment_url);
    /** @noinspection SqlDialectInspection */
    $rs = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid like '%s';", '%' . $attachment_url));
    $image_id = $rs[0];
    if (!empty($image_id)) {
        $image = image_downsize($image_id, [$width, $width]);
        return $image;
    }
    return false;
}

maybe_redirect();

?>
    <!doctype html>
    <meta property="og:title" content="<?= $_GET['title']; ?>"/>
    <meta property="og:site_name" content="<?php bloginfo('name') ?>"/>
    <!-- URL to the actual page - this will be used to aggregate shares -->
    <!-- not working right now, as the crawler follows the URL and uses it to generate the content... -->
    <!--<meta property="og:url" content=""/>-->
    <meta property="og:image" content="<?= get_pledge_thumbnail()[0]; ?>"/>
    <meta property="og:description" content="
<?php

    while (have_posts()) {
        the_post();
        echo esc_attr(get_the_content());
    }
    ?>
"/>


    <div class="pledge_category">
        <input title="<?php the_title(); ?>" id="pledge_<?php the_ID(); ?>" type="checkbox"
               class="pledge_select">

        <label for="pledge_<?php the_ID(); ?>" class="pledge_content">
            <?php the_content(); ?>
        </label>
    </div>
