jQuery(document).ready(function($) {
    $('#activate-mosque-prayer-time-plugin').on('click', function() {
        $.ajax({
            type: 'POST',
            url: mosque_prayer_time_data.ajax_url,
            data: {
                action: 'activate_mosque_prayer_time_plugin',
            },
            success: function(response) {
                location.reload();
            },
        });
    });

    $('#deactivate-mosque-prayer-time-plugin').on('click', function() {
        $.ajax({
            type: 'POST',
            url: mosque_prayer_time_data.ajax_url,
            data: {
                action: 'deactivate_mosque_prayer_time_plugin',
            },
            success: function(response) {
                location.reload();
            },
        });
    });
});
