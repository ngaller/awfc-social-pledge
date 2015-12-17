/**
 * Created by nico on 12/14/2015.
 */

jQuery(document).ready(function ($) {
    $('#twitterLogin').click(function () {
        var clientId = $('#twitter_clientkey').val();
        if (!clientId) {
            alert('Please enter client id first');
            return false;
        }
        var clientSecret = $('#twitter_clientsecret').val();
        if (!clientSecret) {
            alert('Please enter client secret first');
            return false;
        }
        var url = '/?initiate_twitter_login=1&twitter_clientkey=' + encodeURIComponent(clientId) +
            '&twitter_clientsecret=' + encodeURIComponent(clientSecret);
        window.open(url, '_blank');
        $('#submit').click();
        return false;
    });

    window.onTwitterLoginSuccess = function (token) {
        $('.twitter-token-status').html('Login Validated');
    };

    window.onTwitterLoginError = function (msg) {
        $('.twitter-token-status').html(msg);
    };
});