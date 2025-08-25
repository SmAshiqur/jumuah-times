// Enqueue custom scripts
    

jQuery(document).ready(function($) {
    // Handle checkbox clicks
    $('#mosque_prayer_time_activated_geo').click(function() {
        if ($(this).prop('checked')) {
            // Checkbox "Footer Widget - GEO Location" is checked, uncheck Checkbox "Footer Widget - SECURE API"
            $('#mosque_prayer_time_activated_secure_api').prop('checked', false);
        }
    });

    $('#mosque_prayer_time_activated_secure_api').click(function() {
        if ($(this).prop('checked')) {
            // Checkbox "Footer Widget - SECURE API" is checked, uncheck Checkbox "Footer Widget - GEO Location"
            $('#mosque_prayer_time_activated_geo').prop('checked', false);
        }
    });
});
