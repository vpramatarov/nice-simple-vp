jQuery(document).ready(function($) {
    // --- LOAD MORE REFRESH NONCE ---
    $.post(nsJs.ajaxUrl, {
        action: 'ns_get_nonce',
        _ts: new Date().getTime() // cache buster
    }, function (response) {
        if (response.success && response.data.nonce) {
            $('.ns-load-more-btn').data('nonce', response.data.nonce);

            if (typeof nsJs !== 'undefined') {
                nsJs.nonce = response.data.nonce;
            }
        }
    });

    // LOAD MORE (AJAX)
    $('.ns-load-more-btn').on('click', function(e) {
        e.preventDefault();
        const $btn = $(this);
        const offset = $btn.data('offset');
        const limit = $btn.data('limit') || nsJs.projectsLimit;
        const nonce = $btn.data('nonce');
        const container = $('.portfolio-grid');

        if ($btn.hasClass('loading')) {
            return;
        }

        $btn.addClass('loading').text(nsJs.loadingText);

        $.post(nsJs.ajaxUrl, {
            action: 'ns_load_more',
            nonce: nonce,
            offset: offset,
            limit: limit
        }, function(res) {
            $btn.removeClass('loading').text(nsJs.buttonText);
            if (res.success) {
                container.append(res.data.html);
                $btn.data('offset', offset + limit);

                let has_more = res.data.has_more || '';
                if (!has_more) {
                    $btn.hide();
                }
            } else {
                console.log(res.data.message || 'Error');
                $btn.hide();
            }
        });
    });
});