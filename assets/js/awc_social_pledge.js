jQuery(document).ready(function ($) {
    ////////////////// Dialog UI

    // find image right before the given element
    function findRelatedImage(source) {
        if (!source.length)
            return '';
        var match = source.prev().find('.grve-image.image-fullwidth img');
        if (match.length) {
            return match.attr('src');
        }
        return findRelatedImage(source.parent());
    }

    function centerDialog(content) {
        var height = content.height();
        var winHeight = $(window).height();
        var spacing = Math.max(0, (winHeight - height) / 2);
        var top = $(window).scrollTop() + spacing;
        var mainContentPos = $('#grve-main-content').position();
        if (mainContentPos && mainContentPos.top > top) {
            top = mainContentPos.top;
        }
        //console.log('set top = ' + top + ', spacing = ' + spacing + ', height = ' + height + ', winHeight = ' + winHeight);
        content.css('margin-top', top + 'px');
    }

    // very simple dialog function (we can't use colorbox, because the links within the dialog might point to colorbox)
    function simpleLightbox(url, onDialogReadyCb) {
        var bodyHeight = $(document.body).height();
        var overlay = $('<div style="position: absolute; top: 0; left: 0; width: 100%; height: ' + bodyHeight + 'px;' +
            'background: rgba(0,0,0,.7); text-align: center; z-index: 10"></div>')
            .appendTo(document.body);

        var content = $('<div class="pledge_dialog" style="background: white; width: 200px; ' +
                //'position: relative; top: 50%; transform: translateY(-50%);' +
            'margin: 0 auto; box-sizing: border-box ">' +
            '<div id="cboxLoadingGraphic" style="position: static; width: 200px; height: 200px"></div>' +
            '</div>')
            .appendTo(overlay)
            .click(function (evt) {
                // prevent bubble to overlay
                evt.stopPropagation();
            });
        centerDialog(content);
        $.get(url, null, function (result) {
            // set the dialog's content and resize it to contain the image
            content.html(result);
            var img = content.find('.pledge_category_image');
            if (img.length) {
                content.width(img.width());
            }
            onDialogReadyCb(content);
            centerDialog(content);
        });
        overlay.click(function () {
            overlay.remove();
        });
    }

    $('.social-pledge-button').click(function () {
        var img = findRelatedImage($(this));
        if(!img) {
            console.warn('Error: unable to find image');
            return false;
        }
        var url = this.href;
        url += '&img=' + encodeURIComponent(img);
        url += '&screen_width=' + $(window).width();
        simpleLightbox(url, function (dlg) {
            addShareButtons(dlg, img, url);
        });

        return false;
    });

    ////////////////// Summary UI

    // TODO: set up handlers for social pledge summary
    //$('.social-pledge-summary')


    ////////////////// Sharing Functions

    function validateShare(container) {
        if (container.find('input[type=checkbox]:checked').length == 0) {
            container.find('.pledge_selection_error').show();
            return false;
        } else {
            container.find('.pledge_selection_error').hide();
            return true;
        }
    }

    function getPledgeTexts(container) {
        return container.find('input[type=checkbox]:checked').map(function () {
            return $(this).next('label').text();
        }).toArray();
    }

    // URL that will give sharing options, according to selected pledges in the specified container
    function getPledgeShareUrl(container, baseUrl) {
        var selected = container.find('input[type=checkbox]:checked').map(function () {
            return this.value
        }).toArray();

        return baseUrl + '&type=share' +
            '&title=' + encodeURIComponent(document.title) +
            '&url=' + encodeURIComponent(location.href) +
            '&selected=' + selected.join(',');
    }

    // activate the sharing buttons in the designated container.
    // baseUrl is the pledge_category url, used to determine the share URL
    function addShareButtons(container, originalImage, baseUrl) {
        var button = container.find('.share.facebook');
        if (button.length) {
            setupFacebookSdk(button);
            button.click(function () {
                if (validateShare(container)) {
                    shareFacebook(this, container, originalImage);
                }
                return false;
            });
        }
    }

    function setupFacebookSdk(button) {
        if (typeof FB != 'undefined')
            return;

        button.addClass('disabled');
        var appId = button.data('appid');
        window.fbAsyncInit = function () {
            FB.init({
                appId: appId,
                xfbml: false,
                version: 'v2.5'
            });
            button.removeClass('disabled');
        };

        (function (d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) {
                return;
            }
            js = d.createElement(s);
            js.id = id;
            js.src = "//connect.facebook.net/en_US/sdk.js";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
    }

    function shareFacebook(button, container, originalImage) {
        if (typeof FB != 'undefined') {
            $(button).addClass('disabled');
            var url = location.href;
            var pledges = getPledgeTexts(container).join('&nbsp;&nbsp;');

            FB.ui({
                method: 'feed',
                link: url,
                picture: originalImage,
                description: pledges
            }, function () {
                $(button).removeClass('disabled');
                // close dialog?
            });
        } else {
            if (typeof console != 'undefined')
                console.warn('Facebook SDK was not loaded');
        }
    }

});
