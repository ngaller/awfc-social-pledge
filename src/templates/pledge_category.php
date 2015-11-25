<?php
/**
 * pledge_category.php
 * Created By: nico
 * Created On: 11/25/2015
 */
?>
    <style>
        .pledge_category {
            display: block;
        }

        .pledge_category .pledge_select {
            float: left;
        }

        .pledge_category .pledge_content {

        }
    </style>
    <div class="pledge_category_list">
        <?php

        while (have_posts()) {
            the_post(); ?>
            <div class="pledge_category">
                <input title="<?php the_title(); ?>" id="pledge_<?php the_ID(); ?>" type="checkbox" class="pledge_select">

                <label for="pledge_<?php the_ID(); ?>" class="pledge_content">
                    <?php the_content(); ?>
                </label>
            </div>
            <?php
        }
        ?>
    </div>
<?php
