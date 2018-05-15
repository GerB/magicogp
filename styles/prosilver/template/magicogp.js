(function ($) {
    "use strict";

function parseOgp($container) {
        $container.find('.postlink').each(function() {
            var ogpdata = $(this).data('ogp');
            if (ogpdata.title !== undefined) {
                var block = '<div class="ogpblock">';
                if (ogpdata.image !== undefined) {
                    block += '<img src="' + ogpdata.image + '"/>';
                }
                block += '<div class="ogptext"><h4>' + ogpdata.title + '</h4>' + ogpdata.description + '</div></div>';
                $(this).html(block);
                $(this).addClass('ogplink');
                $(this).removeAttr('data-ogp');
            }
        });
    }

    parseOgp($('.content'));

    if (typeof mChat === 'object') {
        $(mChat).on('mchat_add_message_before', function(e, data) {
            parseOgp(data.message);
        });
    }

})(jQuery);