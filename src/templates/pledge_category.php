<?php
/**
 * pledge_category.php
 * Template used for rendering the pledge form with all selected categories.
 * Optionally an image can be passed.
 * Created By: nico
 * Created On: 11/25/2015
 */

// Calculate optimal width for the thumbnail image, based on the screen_width parameter
function get_pledge_thumbnail_width() {
    if(isset($_GET['screen_width']))
        $viewport_width = $_GET['screen_width'];
    else
        $viewport_width = 768;
    if($viewport_width < 600) {
        $width = $viewport_width * .9;
    } else {
        $width = 500;
    }
    return $width;
}

// Return image information (url, width, height), based on the img parameter.
function get_pledge_thumbnail()
{
    global $wpdb;

    if (isset($_GET['img']))
        $attachment_url = $_GET['img'];
    else
        return false;
    $width = get_pledge_thumbnail_width();

    // remove the part before /uploads, because it will have the CDN instead of the real attachment url
    $attachment_url = preg_replace('/^.*(\/uploads\/)/', '$1', $attachment_url);
    /** @noinspection SqlDialectInspection */
    $rs = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid like '%s';", '%' . $attachment_url));
    $image_id = $rs[0];
    if (!empty($image_id)) {
        $image = image_downsize($image_id, [$width, $width]);
//        print_r($image);
        return $image;
    }
    return false;
}

function show_pledge_thumbnail()
{
    if (isset($_GET['show_image']) && $_GET['show_image'] == '0') {
        // allow a show_image parameter to be passed to prevent showing the thunbnail, this is useful for the summary form
        return;
    }
    $image = get_pledge_thumbnail();
    if($image) {
        echo "<img class='pledge_category_image' alt='Associated image to be posted'
                 src='$image[0]' width='$image[1]' height='$image[2]' />";
    }
}


?>
    <style>
        .pledge_category_list {
            width: 100%;
        }

        .pledge_category {
            display: block;
        }

        .pledge_category_list .thumbnail_container {
            text-align: center;
        }

        .pledge_category .pledge_select {
            float: left;
        }

        .pledge_category .pledge_content {

        }

        .pledge_category_image {
            margin: 0 auto;
            max-width: none;
        }
    </style>
    <div class="pledge_category_list">
        <div class="thumbnail_container">
            <?php show_pledge_thumbnail(); ?>
        </div>

        <?php

        while (have_posts()) {
            the_post(); ?>
            <div class="pledge_category">
                <input title="<?php the_title(); ?>" id="pledge_<?php the_ID(); ?>" type="checkbox"
                       class="pledge_select">

                <label for="pledge_<?php the_ID(); ?>" class="pledge_content">
                    <?php the_content(); ?>
                </label>
            </div>
            <?php
        }
        ?>
    </div>
<?php
