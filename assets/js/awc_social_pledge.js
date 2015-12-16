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

    function closeLightbox() {
        $('.pledge_dialog_overlay').remove();
    }

    // very simple dialog function (we can't use colorbox, because the links within the dialog might point to colorbox)
    function simpleLightbox(url, onDialogReadyCb) {
        var bodyHeight = $(document.body).height();
        closeLightbox();

        // create overlay.  style is defined here to avoid making an extra request for the CSS
        var style = ['position: absolute',
            'top: 0', 'left: 0', 'width: 100%', 'height: ' + bodyHeight + 'px',
            'background: rgba(0,0,0,.7)',
            'text-align: center',
            'z-index: 10'].join(';');

        var overlay = $('<div class="pledge_dialog_overlay" style="' + style + '"></div>')
            .appendTo(document.body)
            .click(function (evt) {
                // allow closing dialog on overlay click
                if (evt.target === this) {
                    $(this).remove();
                }
            });

        var content = $('<div class="pledge_dialog" style="background: white; width: 200px; ' +
                // for centering with CSS.  unfortunately this prevents scrolling on mobile
                //'position: relative; top: 50%; transform: translateY(-50%);' +
            'margin: 0 auto; box-sizing: border-box ">' +
            '<div id="cboxLoadingGraphic" style="position: static; width: 100%; height: 200px"></div>' +
            '</div>')
            .appendTo(overlay);

        centerDialog(content);
        $.get(url, null, function (result) {
            // set the dialog's content and resize it to contain the image
            content.html(result);
            $('<div class="dlg_close">&times;</div>').prependTo(content)
                .click(closeLightbox);
            var img = content.find('.pledge_category_image');
            if (img.length) {
                content.width(img.width());
            }
            onDialogReadyCb(content);
            centerDialog(content);
        });
    }

    var pledgeButtons = $('.social-pledge-button');
    pledgeButtons.click(function () {
        var img = findRelatedImage($(this));
        if (!img) {
            console.warn('Error: unable to find image');
            return false;
        }
        var url = this.href;
        url += '&img=' + encodeURIComponent(img);
        url += '&screen_width=' + $(window).width();
        simpleLightbox(url, function (dlg) {
            resetColorbox(dlg);
            addShareButtons(dlg, url);
        });

        return false;
    });

    ////////////////// Summary UI

    function loadPledgeSummary(container, categories) {
        var url = pledgeButtons[0].href.replace(/(pledge_category=).*&?/, '$1' + encodeURIComponent(categories));
        var img = $('.image-fullwidth img').attr('src');
        url += '&img=' + encodeURIComponent(img) + '&show_image=0';
        $.get(url, null, function (result) {
            // set the dialog's content and resize it to contain the image
            container.html(result);
            addShareButtons(container, url);
            resetColorbox(container);
        });
    }

    var pledgeSummary = $('.social-pledge-summary');
    if (pledgeSummary.length) {
        var categories = [];
        pledgeButtons.each(function () {
            var cats = $(this).data('pledge-categories').split(',');
            cats.forEach(function (cat) {
                if (categories.indexOf(cat) == -1)
                    categories.push(cat);
            });
        });
        loadPledgeSummary(pledgeSummary, categories);
    }

    ////////////////// Miscellaneous

    // reset the colorbox handler for the content that was just added
    function resetColorbox(dlg) {
        if ($.fn.colorbox)
        // copy those settings from the wp-colorbox.js file
            dlg.find(".wp-colorbox-iframe").colorbox({iframe: true, width: "80%", height: "80%"});
    }

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

    //function getPledgeText(pledgeCheckbox) {
    //    // bit of a hack because we make this dependent on the structure of the label vs checkbox
    //    return $(pledgeCheckbox).parent('label').text().trim();
    //}
    //
    //function getPledgeTexts(container) {
    //    return container.find('input[type=checkbox]:checked').map(function () {
    //        return getPledgeText(this);
    //    }).toArray();
    //}

    function getPledgeIds(container) {
        return container.find('input[type=checkbox]:checked').map(function () {
            return this.value;
        }).toArray();
    }

    // URL that will give sharing options, according to selected pledge ids
    function getSocialShareUrl(selectedIds, shareType, baseUrl) {
        return baseUrl + '&type=share' +
            '&share=' + shareType +
            '&title=' + encodeURIComponent(document.title) +
            '&url=' + encodeURIComponent(location.href) +
            '&selected=' + encodeURIComponent(selectedIds.join(','));
    }

    function openPopup(url) {
        // TODO: calculate optimal width / height, top and left
        // (popup will resize, but we don't want to go over the device dimensions, I don't think)
        var width = 600;
        var height = 600;
        var top = $(window).height / 2 - height / 2;
        var left = $(window).width / 2 - width / 2;
        window.open(url, '_blank',
            'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=' + height + ',width=' + width +
            ',top=' + top + ',left=' + left);
    }

    // activate the sharing buttons in the designated container.
    function addShareButtons(container, pledgeCategoryUrl) {
        container.find('.share').click(function () {
            if (validateShare(container)) {
                var selectedIds = getPledgeIds(container);
                var shareType = $(this).data('share-type');
                var shareUrl = getSocialShareUrl(selectedIds, shareType, pledgeCategoryUrl);
                if (shareType == 'count-only') {
                    $.ajax(shareUrl);  // no need to wait for result
                } else {
                    openPopup(shareUrl);
                }
                container.find('.thankyou').show();
                //window.open('', '_blank', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600');
                //getSocialShareUrl(, pledgeCategoryUrl, function (shareUrl) {
                //    $(this).removeClass('disabled');
                //    if ($(this).hasClass('facebook')) {
                //        shareFacebook($(this), shareUrl, container, originalImage);
                //    } else if ($(this).hasClass('twitter')) {
                //
                //    }
                //});
            }
        });
        //setupFacebookSdk(container.find('.share.facebook'));
        //var button = container.find('.share.facebook');
        //if (button.length) {
        //
        //    button.click(function () {
        //        if (validateShare(container)) {
        //            shareFacebook(this, container, originalImage);
        //        }
        //        return false;
        //    });
        //}
        //
        //button = container.find('.share.twitter');
        //if (button.length) {
        //    button.click(function () {
        //        if (validateShare(container)) {
        //            shareTwitter(this, container, baseUrl);
        //        }
        //    });
        //}
    }

    //function setupFacebookSdk(button) {
    //    if (typeof FB != 'undefined')
    //        return;
    //
    //    button.addClass('disabled');
    //    var appId = button.data('appid');
    //    window.fbAsyncInit = function () {
    //        FB.init({
    //            appId: appId,
    //            xfbml: false,
    //            version: 'v2.5'
    //        });
    //        button.removeClass('disabled');
    //    };
    //
    //    (function (d, s, id) {
    //        var js, fjs = d.getElementsByTagName(s)[0];
    //        if (d.getElementById(id)) {
    //            return;
    //        }
    //        js = d.createElement(s);
    //        js.id = id;
    //        js.src = "//connect.facebook.net/en_US/sdk.js";
    //        fjs.parentNode.insertBefore(js, fjs);
    //    }(document, 'script', 'facebook-jssdk'));
    //}
    //
    //function shareFacebook(button, shareUrl, container, originalImage) {
    //    if (typeof FB != 'undefined') {
    //        $(button).addClass('disabled');
    //        var pledges = getPledgeTexts(container).join('&nbsp;&nbsp;');
    //        FB.ui({
    //            method: 'feed',
    //            link: shareUrl,
    //            // content for the share.
    //            // this must be included, otherwise they won't get the preview for their post - it only appears
    //            // once facebook has crawled it.
    //            picture: originalImage,
    //            description: pledges
    //        }, function () {
    //            $(button).removeClass('disabled');
    //            // close dialog?
    //        });
    //    } else {
    //        if (typeof console != 'undefined')
    //            console.warn('Facebook SDK was not loaded');
    //    }
    //}
    //
    //function shareTwitter(button, container, baseUrl) {
    //    var selectedPledges = container.find('input[type=checkbox]:checked');
    //    selectedPledges.each(function () {
    //        var twitterUrl = 'https://twitter.com/intent/tweet';
    //        var pledgeText = getPledgeText(this);
    //        var pledgeId = this.value;
    //        twitterUrl += '?text=' + encodeURIComponent(pledgeText);
    //        twitterUrl += '&url=' + encodeURIComponent(getPledgeShareUrl([pledgeId], baseUrl));
    //        var via = $(button).data('twitter-user');
    //        if (via) {
    //            twitterUrl += '&via=' + via;
    //        }
    //        window.open(twitterUrl);
    //    });
    //}

});
