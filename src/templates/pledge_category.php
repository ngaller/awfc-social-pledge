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
$screenWidth = isset($_GET['screen_width']) ? $_GET['screen_width'] : 0;


function show_pledge_thumbnail()
{
    if (isset($_GET['show_image']) && $_GET['show_image'] == '0') {
        // allow a show_image parameter to be passed to prevent showing the thunbnail, this is useful for the summary form
        return;
    }
    global $pledgeData;
    global $screenWidth;

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

        .pledge_dialog .dlg_close {
            font-size: 23px;
            text-align: right;
            font-weight: bold;
            margin-top: -20px;
            cursor: pointer;
        }

        .social-pledge-summary .pledge_dialog_instructions {
            /* hide instructions for summary */
            display: none;
        }

        .pledge_category_list label {
            text-transform: none;
            font-weight: normal;
            /* set font-size to counter-act "10px" setting marked important in theme */
            font-size: inherit !important;
            line-height: 1.5;
            /* ensure wrapping labels will not indent underneath their checkbox */
            text-indent: -15px;
            padding-left: 15px;
            display: block;
        }

        .pledge_category_list label, 
        .pledge_category_list .pledge_dialog_instructions, 
        .pledge_category_list .pledge_dialog_pledgeinfo {
            /* match color on body (this is not inherited because we don't have grve-main-content as parent) */
            color: #6e7177;
        }

        .pledge_category_list .thankyou {
            text-align: center;
            padding-top: 10px;
            display: none;
            /* avoid getting too wide on the desktop pledge summary */
            max-width: 500px;
        }

        .pledge_category_list .thankyou label {
            font-size: 120% !important;
            font-weight: bold;
        }

        .pledge_category_list .pledge_select {
            width: 16px;
            height: 16px;
            padding: 0;
            margin: 0;
            vertical-align: bottom;
            position: relative;
            /* vertically align checkboxes with labels.  If the font size and/or line height is changed this
               value may have to be changed */
            top: -3px;
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
            /* flex containers lets us easily center the buttons */
            display: flex;
            flex-flow: row wrap;
            justify-content: space-between;
            /* avoid getting too wide on the desktop pledge summary */
            max-width: 500px;
        }

        .share_buttons .btn {
            flex: 0 0 auto;
            padding: 10px 15px;
            border: none;
            text-decoration: none;
            color: #FFF;
            border-radius: 4px;
            text-align: center;
            margin-top: 15px;
            /* avoid spanning more than 2 columns */
            max-width: calc(50% - 2px);
            /* make all buttons the same color */
            background-color: #303030;
            height: 42px;
        }

        .share_buttons .disabled {
            opacity: 0.6;
            pointer-events: none;
            cursor: not-allowed;
        }

        .share_buttons .btn:hover {
            color: #efefef;
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

        @media screen and (min-width: 1025px) {
            .social-pledge-summary .share_buttons {
                display: block;
                max-width: 100%;
            }
        }
    </style>

    <div class="pledge_category_list"
         style="min-width: <?= $screenWidth ? $pledgeData->getPledgeThumbnailWidth($screenWidth) : 0 ?>px;">
        <p class="grve-subtitle pledge_dialog_instructions"><?= $pledgeData->getInstructions(); ?></p>
        <div class="thumbnail_container">
            <?php show_pledge_thumbnail(); ?>
        </div>

        <?php

        while (have_posts()) {
            the_post(); ?>
            <div class="pledge_category">
                <label class="pledge_content grve-subtitle">
                    <input title="<?php the_title(); ?>" type="checkbox"
                           value="<?php the_ID(); ?>"
                           class="pledge_select">

                    <?php the_content(); ?>
                </label>
            </div>
            <?php
        }
        ?>

        <div class="pledge_selection_error">

        </div>
        <div class="share_buttons">
            <input type="hidden" name="share-url" value="<?= $pledgeData->getShareUrl(); ?>"/>
            <input type="hidden" name="hashtags" value="<?= esc_attr($pledgeData->getHashtags()); ?>"/>
            <button class="btn share facebook" data-share-type="facebook">
                <i class="fa fa-facebook"></i> Facebook
            </button>
            <button class="btn share twitter" data-share-type="twitter">
                <i class="fa fa-twitter"></i> Twitter
            </button>
            <button class="btn share gplus" data-share-type="gplus">
                <i class="fa fa-google-plus"></i> Google+
            </button>
            <button class="btn share tumblr" data-share-type="tumblr">
                <i class="fa fa-tumblr"></i> Tumblr
            </button>
            <button class="btn share count-only" data-share-type="count-only">
                Don't Share - Just Count my Pledge
            </button>
            <p class="grve-subtitle pledge_dialog_pledgeinfo"><?= $pledgeData->getPledgeInfo(); ?></p>
        </div>
        <div class="thankyou">
            <label>Thank you for your pledge!</label>
        </div>
    </div>

<?php
