<?php
/**
 * CustomPostType.php
 * Created By: nico
 * Created On: 11/24/2015
 */

namespace AWC\SocialPledge;


class PledgePostType
{
    const POST_TYPE = "pledge";
    const TAXONOMY = "pledge_category";

    public function register()
    {
        $this->registerPostType();
        $this->registerTaxonomy();
        $this->registerCategoryTemplate();
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