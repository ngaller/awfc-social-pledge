<?php
/**
 * OptionPa.php
 * Created By: nico
 * Created On: 11/28/2015
 */

namespace AWC\SocialPledge;


class OptionPage
{
    const AWC_SOCIALPLEDGE_SETTINGS = 'awc_social_pledge_settings';
    const OPTION_FACEBOOK_APPID = 'facebook_appid';
    const OPTION_TWITTER_SCREENNAME = 'twitter_screenname';

    public function registerOptionPage()
    {
        add_action('admin_init', array(&$this, 'adminInit'));
        add_action('admin_menu', array(&$this, 'adminMenu'));
        add_filter("plugin_action_links_" . AWC_SOCIAL_PLEDGE_PLUGIN_BASENAME, [&$this, 'settingsLink']);
    }

    public function settingsLink($links) {
        $settings = '<a href="options-general.php?page='. self::AWC_SOCIALPLEDGE_SETTINGS .'">Settings</a>';
        array_unshift($links, $settings);
        return $links;
    }

    public function adminInit()
    {
        register_setting(self::AWC_SOCIALPLEDGE_SETTINGS, self::AWC_SOCIALPLEDGE_SETTINGS, array(&$this, 'sanitizeOptions'));
        add_settings_section('default', 'AWC Social Pledge Settings', null, self::AWC_SOCIALPLEDGE_SETTINGS);
        add_settings_field(self::OPTION_FACEBOOK_APPID, 'Facebook App Id',
            array(&$this, 'createSettingsTextbox'), self::AWC_SOCIALPLEDGE_SETTINGS,
            'default', array('name' => self::OPTION_FACEBOOK_APPID));
        add_settings_field(self::OPTION_TWITTER_SCREENNAME, 'Twitter Screenname',
            array(&$this, 'createSettingsTextbox'), self::AWC_SOCIALPLEDGE_SETTINGS,
            'default', array('name' => self::OPTION_TWITTER_SCREENNAME));
    }

    public function adminMenu()
    {
        if (current_user_can('manage_options')) {
            add_options_page('AWC Social Pledge Settings', 'Social Pledge', 'manage_options',
                self::AWC_SOCIALPLEDGE_SETTINGS, array(&$this, 'outputPluginSettingsPage'));
        }
    }

    public function outputPluginSettingsPage()
    {
        /** @noinspection HtmlUnknownTarget */
        echo '<div class="wrap"><form method="POST" action="options.php">';

        settings_fields(self::AWC_SOCIALPLEDGE_SETTINGS);
        do_settings_sections(self::AWC_SOCIALPLEDGE_SETTINGS);
        submit_button();

        echo '</form></div>';
    }


    public function createSettingsTextbox($args)
    {
        $optionName = $args['name'];
        $size = isset($args['size']) ? $args['size'] : 40;
        $placeholder = isset($args['placeholder']) ? esc_attr($args['placeholder']) : '';
        $option = $this->getOption($optionName);
        $value = isset($option) ? esc_attr($option) : '';
        echo "<input type='text' id='$optionName'
            name='" . self::AWC_SOCIALPLEDGE_SETTINGS . "[$optionName]'
            value='$value' size='$size' placeholder='$placeholder'/>";
    }

    public function sanitizeOptions($options)
    {
        return $options;
    }

    public static function getOption($optionName)
    {
        $options = get_option(self::AWC_SOCIALPLEDGE_SETTINGS);
        if (is_array($options) && isset($options[$optionName]))
            return $options[$optionName];
        return '';
    }

}