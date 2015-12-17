<?php
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
        /* Styling for the pledge dialog.  Ideally this should be defined in a separate CSS.  For performance reason
           we load it inline instead. */
        .pledge_dialog {
            padding: 20px;
            text-align: left;
        }

        .pledge_dialog label {
            text-transform: none;
            font-weight: normal;
            font-size: 100% !important;
            line-height: 1.5;
            /* ensure wrapping labels will not indent underneath their checkbox */
            text-indent: -15px;
            padding-left: 15px;
            display: block;
            color: #6e7177; /* match color on body */
        }

        .pledge_dialog .thankyou {
            text-align: center;
            padding-top: 10px;
            display: none;
        }

        .pledge_dialog .thankyou label {
            font-size: 120% !important;
            font-weight: bold;
        }

        .pledge_dialog .pledge_select {
            width: 13px;
            height: 13px;
            padding: 0;
            margin: 0;
            vertical-align: bottom;
            position: relative;
            /* vertically align checkboxes with labels.  If the font size and/or line height is changed this
               value may have to be changed */
            top: -1px;
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
            line-height: 1.5;
            display: inline;
        }

        .pledge_category {
            display: block;
        }

        .pledge_category_list .thumbnail_container {
            text-align: center;
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
            /* avoid spanning more than 2 columns */
            max-width: calc(50% - 2px);
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

        .count-only {
            background-color: #303030;
        }

        .pledge_selection_error {
            display: none;
            color: #ff6863;
        }

        @media screen and (max-width: 500px) {
            .share_buttons .btn {
                min-width: 45%;
                max-width: 100%;
            }
        }
    </style>
    <div class="pledge_category_list">
        <h5><?= $pledgeData->getInstructions(); ?></h5>
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
        <a class="btn share facebook" data-share-type="facebook" href="javascript:void(0)">
            <i class="fa fa-facebook"></i> Facebook</a>
        <a class="btn share twitter" data-share-type="twitter" href="javascript:void(0)">
            <i class="fa fa-twitter"></i> Twitter</a>
        <a class="btn share gplus" data-share-type="gplus" href="javascript:void(0)"><i class="fa fa-google-plus"></i>
            Google+</a>
        <!--        <a class="btn share linkedin" href="#"><i class="fa fa-linkedin"></i> Share</a>-->
        <a class="btn share tumblr" data-share-type="tumblr" href="javascript:void(0)">
            <i class="fa fa-tumblr"></i> Tumblr</a>
        <button class="btn share count-only" data-share-type="count-only">
            Don't Share - Just Count my Pledge
        </button>
    </div>
    <div class="thankyou">
        <label>Thank you for your pledge!</label>
    </div>
<?php
