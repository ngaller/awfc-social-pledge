<?php
/**
 * TwitterLogin.php
 * Created By: nico
 * Created On: 12/17/2015
 */

namespace AWC\SocialPledge;

use Abraham\TwitterOAuth\TwitterOAuth;
use Abraham\TwitterOAuth\TwitterOAuthException;

/**
 * Used to complete Twitter OAuth flow.
 *
 * @package AWC\SocialPledge
 */
class TwitterLogin
{
    private $clientKey, $clientSecret, $accessToken, $accessTokenSecret;

    function __construct()
    {
        $opts = new OptionPage();
        $this->clientKey = $opts->getOption(OptionPage::OPTION_TWITTER_CLIENTKEY);
        $this->clientSecret = $opts->getOption(OptionPage::OPTION_TWITTER_CLIENTSECRET);
        $this->accessToken = $opts->getOption(OptionPage::OPTION_TWITTER_ACCESSTOKEN);
        $this->accessTokenSecret = $opts->getOption(OptionPage::OPTION_TWITTER_ACCESSTOKENSECRET);
    }

    /**
     * Check for query string parameters that signify we need to initiate or complete the Twitter OAuth flow.
     */
    public function onWpLoaded()
    {
        if (isset($_GET['initiate_twitter_login'])) {
            $this->initiateTwitterLogin();
        } else if (isset($_GET['twitter_login_redirect'])) {
            $this->completeTwitterLogin();
        }
    }

    /**
     * Instantiate and return a new TwitterOAuth object.
     */
    public function getTwitterConnection()
    {
        return new TwitterOAuth($this->clientKey, $this->clientSecret, $this->accessToken, $this->accessTokenSecret);
    }

    /**
     * Use the /account/verify_credentials endpoint to verify the access token is valid
     * @return bool
     */
    public function validateToken()
    {
        if ($this->accessToken) {
            $connection = $this->getTwitterConnection();
            $creds = $connection->get('account/verify_credentials');
            if($creds && 200 == $connection->getLastHttpCode()){
                return $creds->screen_name;
            }
        }
        return false;
    }

    /**
     * Initiate Twitter OAuth flow:
     * Request a token, save the request token for later, and redirect the user to oauth/authorize
     *
     * @see https://dev.twitter.com/web/sign-in/implementing
     * @see https://dev.twitter.com/oauth/3-legged
     */
    private function initiateTwitterLogin()
    {
        $this->getTwitterOptionsFromQueryString();
        $connection = new TwitterOAuth($this->clientKey, $this->clientSecret);
        try {
            $callback = home_url('?twitter_login_redirect=1');
            $token = $connection->oauth('oauth/request_token', ['oauth_callback' => $callback]);
        } catch (TwitterOAuthException $e) {
            $this->showError('Unable to obtain Twitter request token: ' . $e->getMessage());
            return;
        }
        if ($token && @$token['oauth_callback_confirmed'] === 'true') {
            $requestToken = $token['oauth_token'];
            $requestTokenSecret = $token['oauth_token_secret'];
            update_user_meta(get_current_user_id(), 'twitter_oauth_token', $requestToken);
            update_user_meta(get_current_user_id(), 'twitter_oauth_token_secret', $requestTokenSecret);
            // cannot use session in WP
//            $_SESSION['twitter_request_token'] = $requestToken;
//            $_SESSION['twitter_request_token_secret'] = $requestTokenSecret;

            wp_redirect('https://api.twitter.com/oauth/authorize?oauth_token=' . urlencode($requestToken));
            exit;
        } else {
            $this->showError('Unable to obtain Twitter request token: unknown error');
        }
    }

    /**
     * Complete twitter OAuth flow:
     * Use the provided oauth_verifier and the session's twitter_request_token to obtain an access token.
     * Save it into the options, and output the javascript to close the window and update the original
     * option window.
     */
    public function completeTwitterLogin()
    {
        $requestToken = get_user_meta(get_current_user_id(), 'twitter_oauth_token', true);
        if ($requestToken != $_GET['oauth_token']) {
            $this->showError('Invalid oauth_token');
            return;
        }
        $requestTokenSecret = get_user_meta(get_current_user_id(), 'twitter_oauth_token_secret', true);
        $connection = new TwitterOAuth($this->clientKey, $this->clientSecret, $requestToken, $requestTokenSecret);
        $token = $connection->oauth('oauth/access_token', [
            'oauth_verifier' => $_GET['oauth_verifier']
        ]);
        if ($token) {
            OptionPage::saveAWCOption(OptionPage::OPTION_TWITTER_ACCESSTOKEN, $token['oauth_token']);
            OptionPage::saveAWCOption(OptionPage::OPTION_TWITTER_ACCESSTOKENSECRET, $token['oauth_token_secret']);
            $this->showSuccess();
        } else {
            $this->showError('Twitter login failed');
        }
    }

    /**
     * Retrieve client id and secret from the query string, if they are specified.
     * This is used so that they don't have to save the settings prior to using the Login button.
     */
    private function getTwitterOptionsFromQueryString()
    {
        if (isset($_GET['twitter_clientkey'])) {
            $this->clientKey = $_GET['twitter_clientkey'];
            $this->clientSecret = $_GET['twitter_clientsecret'];

            OptionPage::saveAWCOption(OptionPage::OPTION_TWITTER_CLIENTKEY, $this->clientKey);
            OptionPage::saveAWCOption(OptionPage::OPTION_TWITTER_CLIENTSECRET, $this->clientSecret);
        }
    }

    private function showError($msg)
    {
        echo '<script type="text/javascript">
window.opener.onTwitterLoginError("' . esc_js($msg) . '");
window.close();
</script>';
        exit;
    }

    private function showSuccess()
    {
        echo '<script type="text/javascript">
window.opener.onTwitterLoginSuccess();
window.close();
</script>';
        exit;
    }
}