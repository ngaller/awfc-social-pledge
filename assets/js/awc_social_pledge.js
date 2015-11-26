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

    // very simple dialog function (we can't use colorbox, because the links within the dialog might point to colorbox)
    function simpleLightbox(url) {
        var overlay = $('<div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,.7); text-align: center; z-index: 10"></div>')
            .appendTo(document.body);

        var content =  $('<div class="pledge_dialog" style="background: white; width: 200px; ' +
                    'margin: 30px auto; padding: 20px; ' +
                    // vertical center
                    'position: relative; top: 50%; transform: translateY(-50%);">' +
                '<div id="cboxLoadingGraphic" style="position: static; width: 200px; height: 200px"></div>' +
                '</div>')
            .appendTo(overlay)
            .click(function() {
                return false;
            });
        $.get(url, null, function(result) {
            // set the dialog's content and resize it to contain the image
            content.html(result);
            var img = content.find('.pledge_category_image');
            if(img.length) {
                content.width(img.width() + 40);
            }
        });
        overlay.click(function() {
            overlay.remove();
        });
    }

    $('.social-pledge-button').click(function () {
        var img = findRelatedImage($(this));
        var url = this.href;
        if(img) {
            url += '&img=' + encodeURIComponent(img);
            url += '&screen_width=' + $(window).width();
        }
        simpleLightbox(url);

        return false;
    });

    ////////////////// Summary UI

    // TODO: set up handlers for social pledge summary
    //$('.social-pledge-summary')


    ////////////////// Sharing Functions

});
