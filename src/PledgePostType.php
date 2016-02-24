<?php
/**
 * CustomPostType.php
 * Created By: nico
 * Created On: 11/24/2015
 */

namespace AWC\SocialPledge;


class PledgePostType
{
    const POST_TYPE = 'pledge';
    const TAXONOMY = 'pledge_category';
    const METAKEY_PLEDGE_SHORT_CONTENT = 'pledge_short_content';

    public static function getSelectedPledgeText($selectedPledgeIds, $useShortContent)
    {
        if (!is_array($selectedPledgeIds)) {
            $selectedPledgeIds = explode(',', $selectedPledgeIds);
        }
        // when using short content, limit to 1 pledge
        if ($useShortContent) {
            $selectedPledgeIds = [$selectedPledgeIds[0]];
        }
        $posts = get_posts([
            'post_type' => self::POST_TYPE,
            'include' => $selectedPledgeIds
        ]);
        $content = array_map(function ($p) use ($useShortContent) {
            $content = $p->post_content;
            if ($useShortContent) {
                $short = get_post_meta($p->ID, self::METAKEY_PLEDGE_SHORT_CONTENT, true);
                if (!empty($short)) {
                    $content = $short;
                }
            }
            // expanding shortcode will let us get the text from the colorbox links
            $content = do_shortcode($content);                        
            // remove all tags (FB would interpret them literally anyway, and display the angle brackets)
            $content = wp_strip_all_tags($content);
            return $content;
        }, $posts);
        return join('&nbsp;&nbsp;', $content);
    }

    public function register()
    {
        $this->registerPostType();
        $this->registerTaxonomy();
        $this->registerCategoryTemplate();
        $this->registerMetaBox();
    }

    private function registerPostType()
    {
        register_post_type(self::POST_TYPE,
            [
                'labels' => [
                    'name' => __(sprintf('%ss', ucwords(str_replace("_", " ", self::POST_TYPE)))),
                    'singular_name' => __(sprintf('%s', ucwords(str_replace("_", " ", self::POST_TYPE))))
                ],
                'public' => true,
                'show_ui' => true,
                'has_archive' => false,
                'hierarchical' => false,
                // cannot set "exclude from search" because it will prevent taxonomy results from returning
//                'exclude_from_search' => true,
                'publicly_queryable' => false,
                'taxonomies' => [self::TAXONOMY],
                'description' => __(sprintf('%s', ucwords(str_replace("_", " ", self::POST_TYPE)))),
                'supports' => ['title', 'editor']
            ]);
    }

    private function registerMetaBox()
    {
        add_action('add_meta_boxes_' . self::POST_TYPE, [$this, 'addMetaBoxes']);
        add_action('save_post', [$this, 'saveMetaBoxes']);
    }

    /**
     * Register meta box for the Short Content (used for tweets)
     */
    public function addMetaBoxes()
    {
        add_meta_box(self::METAKEY_PLEDGE_SHORT_CONTENT, 'Short Content', [$this, 'outputShortContentMetaBox'],
            self::POST_TYPE, 'normal');
    }

    /**
     * Check if metabox data was posted.
     *
     * @param $postId
     */
    public function saveMetaBoxes($postId)
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (isset($_POST['pledge_short_content_nonce']) &&
            wp_verify_nonce($_POST['pledge_short_content_nonce'], self::POST_TYPE) &&
            isset($_POST['pledge_short_content_value'])
        ) {
            $input = sanitize_text_field($_POST['pledge_short_content_value']);
            update_post_meta($postId, self::METAKEY_PLEDGE_SHORT_CONTENT, $input);
        }
    }

    /**
     * HTML for the Short Content meta box
     * @param \WP_Post $post
     */
    public function outputShortContentMetaBox($post)
    {
        $value = get_post_meta($post->ID, self::METAKEY_PLEDGE_SHORT_CONTENT, true);

        wp_nonce_field(self::POST_TYPE, 'pledge_short_content_nonce');
        ?>
        <label for='pledge_short_content_value'>If specified, this text will be used for shorter posts (i.e.
            Twitter)</label>
        <br/>
        <script type="text/javascript">
            function updateShortContentCharCount() {
                var $ = jQuery;
                var l = $('#pledge_short_content_value').val().length;
                if (l) {
                    $('#pledge_short_content_char_count').html('Character Count: ' + l);
                } else {
                    $('#pledge_short_content_char_count').html('');
                }
            }
        </script>
        <textarea name='pledge_short_content_value' id='pledge_short_content_value' style='width: 80%'
                  onkeyup='updateShortContentCharCount()'><?= esc_html($value) ?></textarea>
        <br/>
        <label id="pledge_short_content_char_count"></label>

        <?php
    }

    // register a "Pledge Category" taxonomy that can be used to group the pledges
    private function registerTaxonomy()
    {
        register_taxonomy(self::TAXONOMY, self::POST_TYPE, [
            'labels' => [
                'name' => 'Pledge Categories',
                'singular_name' => 'Pledge Category'
            ],
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_tagcloud' => false,
            'show_in_quick_edit' => true,
            'show_admin_column' => true,
            'rewrite' => false,
            'query_var' => true,   // this will let us query the pledges by using /?pledge_category=reduce-meat
            'description' => 'Used to group pledges so they may be selected when creating a social pledge button'
        ]);
    }

    // using filters, register a custom template for the "pledge category" taxonomy.
    // This will be used to trim away all the styling when rendering categories, so that they may be pulled
    // by the javascript more easily.
    private function registerCategoryTemplate()
    {
        // taxonomy-pledge_category
        add_filter('taxonomy_template', [$this, 'getTaxonomyTemplateFilter']);
    }

    public function getTaxonomyTemplateFilter($template)
    {
        $term = get_queried_object();
        if (!empty($term->slug)) {
            if ($term->taxonomy == self::TAXONOMY) {
                if (isset($_GET['type']) && $_GET['type'] == 'share') {
                    return __DIR__ . '/templates/pledge_category_share.php';
                } else {
                    return __DIR__ . '/templates/pledge_category.php';
                }
            }
        }
        return $template;
    }
}
