jQuery(function ($) {
    $('.ns-color-field').wpColorPicker();

    $('#ns-settings-tabs').on('click', '.nav-tab', function (e) {
        const $this = $(this);
        const slug = $this.data('ns-tab');
        if (!slug) {
            return;
        }
        e.preventDefault();

        $this.siblings('.nav-tab').removeClass('nav-tab-active');
        $this.addClass('nav-tab-active');

        $('.ns-tab-panel').removeClass('is-active');
        $('.ns-tab-panel[data-ns-tab="' + slug + '"]').addClass('is-active');

        if (window.history && window.history.replaceState) {
            window.history.replaceState({}, '', this.href);
        }
    });
});
