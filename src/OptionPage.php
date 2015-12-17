<?php
/**
 * OptionPa.php
 * Created By: nico
 * Created On: 11/28/2015
 */

namespace AWC\SocialPledge;


use F1\WPUtils\Admin\AdminPageHelper;

class OptionPage extends AdminPageHelper
{
    const AWC_SOCIALPLEDGE_SETTINGS = 'awc_social_pledge_settings';
    const OPTION_FACEBOOK_APPID = 'facebook_appid';
    const OPTION_TWITTER_SCREENNAME = 'twitter_screenname';
    const OPTION_TWITTER_CLIENTKEY = 'twitter_clientkey';
    const OPTION_TWITTER_CLIENTSECRET = 'twitter_clientsecret';
    const OPTION_TWITTER_ACCESSTOKEN = 'twitter_accesstoken';
    const OPTION_TWITTER_ACCESSTOKENSECRET = 'twitter_accesstokensecret';

    function __construct()
    {
        parent::__construct(self::AWC_SOCIALPLEDGE_SETTINGS, 'AWFC Social Pledge Settings');
        $this->addSetting(self::OPTION_FACEBOOK_APPID, 'Facebook App Id');
        $this->addSetting(self::OPTION_TWITTER_SCREENNAME, 'Twitter Screenname');
        $this->addSetting(self::OPTION_TWITTER_CLIENTKEY, 'Twitter Client ID');
        $this->addSetting(self::OPTION_TWITTER_CLIENTSECRET, 'Twitter Client Secret');
        // TODO: provide oauth flow
        $this->addSetting(self::OPTION_TWITTER_ACCESSTOKEN, 'Twitter Access Token');
        $this->addSetting(self::OPTION_TWITTER_ACCESSTOKENSECRET, 'Twitter Access Token Secret');
    }

    public function registerOptionPage()
    {
        parent::registerOptionPage(AWC_SOCIAL_PLEDGE_PLUGIN_BASENAME);
    }

    public function onSanitizeOptions($options)
    {
        if (isset($options[self::OPTION_TWITTER_SCREENNAME])) {
            // remove @ from twitter name if they included it
            $options[self::OPTION_TWITTER_SCREENNAME] = str_replace('@', '', $options[self::OPTION_TWITTER_SCREENNAME]);
        }
        return parent::onSanitizeOptions($options);
    }

    public static function getAWCOption($optionName)
    {
        $opts = new OptionPage();
        return $opts->getOption($optionName);
    }

}