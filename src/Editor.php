<?php
/**
 * Editor.php
 * Created By: nico
 * Created On: 11/24/2015
 */

namespace AWC\SocialPledge;


/**
 * Customizations for the Visual Composer UI, enabling adding the Pledge button.
 * Remember to enable the "AWC Social Pledge Button" element in the Visual Composer security options!
 *
 * @package AWC\SocialPledge
 */
class Editor
{
    /**
     * Register customizations.
     * Called on WP Loaded by Init.
     */
    public function integrateWithVC()
    {
        $this->registerSocialPledgeButton();
        $this->registerSocialPledgeSummary();
    }

    private function registerSocialPledgeButton()
    {
        $terms = $this->getPledgeCategories();
        vc_map([
            'name' => __('AWC Social Pledge Button', 'awc-social-pledge'),
            'base' => 'awc_social_pledge_button',
            'icon' => plugins_url('assets/img/pledge_icon_editor.png', __DIR__),
            'category' => __('Content', 'js_composer'),
            'params' => [
                [
                    'type' => 'dropdown',
                    'heading' => 'Pledge Category',
                    'param_name' => 'category',
                    'description' => 'Define category of pledge to be included.  Define those in WP admin.',
                    'value' => $terms
                ],
                [
                    'type' => 'dropdown',
                    'heading' => 'Second Pledge Category',
                    'param_name' => 'category2',
                    'description' => 'Optionally, define another category to be included.  They will be combined on the page.',
                    'value' => $terms
                ]
            ]
        ]);
    }

    private function registerSocialPledgeSummary()
    {
        vc_map([
            'name' => __('AWC Social Pledge Summary', 'awc-social-pledge'),
            'base' => 'awc_social_pledge_summary',
            'icon' => plugins_url('assets/img/pledge_icon_editor.png', __DIR__),
            'category' => __('Content', 'js_composer')
        ]);
    }

    private function getPledgeCategories()
    {
        $terms = get_terms([CustomPostType::TAXONOMY]);
        if (is_wp_error($terms)) {
            throw new \Exception('Unable to retrieve pledge categories: ' . $terms->get_error_message());
        }
        $terms = array_map(function ($term) {
            return $term->slug;
        }, $terms);
        array_unshift($terms, '');
        return $terms;
    }
}