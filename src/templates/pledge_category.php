<?php
use AWC\SocialPledge\OptionPage;
use AWC\SocialPledge\PledgeDialogData;

/**
 * pledge_category.php
 * Template used for rendering the pledge form with all selected categories - this is used for the user to select
 * their pledge and share it.
 * Query string parameters:
 * - img = URL of image to be shared.
 * - screen_width = Px width of viewport.  This is used to size the image.
 * - show_image = if set to "0", this will prevent actually showing the image.
 * - parent_id
 * Created By: nico
 * Created On: 11/25/2015
 */

$pledgeData = new PledgeDialogData(@$_GET['img'], @$_GET['parent_id']);


function show_pledge_thumbnail()
{
    if (isset($_GET['show_image']) && $_GET['show_image'] == '0') {
        // allow a show_image parameter to be passed to prevent showing the thunbnail, this is useful for the summary form
        return;
    }
    global $pledgeData;

    $screenWidth = isset($_GET['screen_width']) ? $_GET['screen_width'] : 0;
    $image = $pledgeData->getPledgeThumbnail($screenWidth);
    if ($image) {
        echo "<img class='pledge_category_image' alt='Associated image to be posted'
                 src='$image[0]' style='width: $image[1]px; height: $image[2]px' />";
    }
}


?>
    <!--suppress CssUnusedSymbol -->
    <style>
        /* Styling for the pledge dialog.  Ideally this should be defined in a separate CSS */
        .pledge_dialog {
            padding: 20px;
        }

        .pledge_dialog .dlg_close {
            font-size: 23px;
            text-align: right;
            font-weight: bold;
            margin-top: -20px;
            cursor: pointer;
        }

        .pledge_category_list {
            width: 100%;
        }

        .pledge_category_list p {
            margin: 0;
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

        .share_buttons {
            display: flex;
            flex-flow: row wrap;
            justify-content: space-between;
            /* avoid getting too wide on the desktop pledge summary */
            max-width: 500px;
        }

        .share_buttons .btn {
            flex: 0 0 auto;
            display: block;
            padding: 10px 15px;
            border: none;
            text-decoration: none;
            font-size: 18px;
            color: #FFF;
            border-radius: 4px;
            text-align: center;
            margin-top: 15px;
        }

        .share_buttons .disabled {
            opacity: 0.6;
            pointer-events: none;
            cursor: not-allowed;
        }

        .share_buttons .btn:hover {
            color: #efefef;
        }

        .facebook {
            background-color: #3b5998;
        }

        .gplus {
            background-color: #dd4b39;
        }

        .twitter {
            background-color: #55acee;
        }

        .tumblr {
            background-color: #35465c;
        }

        .stumbleupon {
            background-color: #eb4924;
        }

        .pinterest {
            background-color: #cc2127;
        }

        .linkedin {
            background-color: #0077b5;
        }

        .buffer {
            background-color: #323b43;
        }

        .pledge_selection_error {
            display: none;
            color: #ff6863;
        }

        @media screen and (max-width: 500px) {
            .share_buttons .btn {
                min-width: 45%;
            }
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
                <label class="pledge_content">
                    <input title="<?php the_title(); ?>" type="checkbox"
                           value="<?php the_ID(); ?>"
                           class="pledge_select">

                    <?php the_content(); ?>
                </label>
            </div>
            <?php
        }
        ?>
    </div>
    <div class="pledge_selection_error">
        Please select your pledge first
    </div>
    <div class="share_buttons">
        <input type="hidden" name="share-url" value="<?= $pledgeData->getShareUrl(); ?>"/>
        <input type="hidden" name="hashtags" value="<?= esc_attr($pledgeData->getHashtags()); ?>"/>
        <a class="btn share facebook" href="#"
           data-appid="<?= OptionPage::getOption(OptionPage::OPTION_FACEBOOK_APPID) ?>">
            <i class="fa fa-facebook"></i> Facebook</a>
        <a class="btn share twitter" href="#">
            <i class="fa fa-twitter"></i> Twitter</a>
        <a class="btn share gplus" href="#"><i class="fa fa-google-plus"></i> Google+</a>
        <!--        <a class="btn share linkedin" href="#"><i class="fa fa-linkedin"></i> Share</a>-->
        <a class="btn share tumblr" href="#">
            <i class="fa fa-tumblr"></i> Tumblr</a>
    </div>
<?php
