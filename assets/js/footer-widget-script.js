jQuery(document).ready(function ($) {
    $('#activate-footer-widget').on('click', function (e) {
        e.preventDefault();
        $.ajax({
            url: footer_widget_data.ajax_url,
            type: 'post',
            data: {
                action: 'activate_footer_widget'
            },
            success: function (response) {
                // Handle the response if needed
            }
        });
    });

    $('#deactivate-footer-widget').on('click', function (e) {
        e.preventDefault();
        $.ajax({
            url: footer_widget_data.ajax_url,
            type: 'post',
            data: {
                action: 'deactivate_footer_widget'
            },
            success: function (response) {
                // Handle the response if needed
            }
        });
    });

    // Add similar logic for other buttons if needed
});
