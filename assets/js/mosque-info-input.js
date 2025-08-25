function modal_script() {

        jQuery(document).ready(function ($) {
            // Check the checkbox on page load
            var secureApiCheckbox = $('input[name="mosque_prayer_time_activated_secure_api"]');
            if (secureApiCheckbox.prop('checked')) {
                displayMosqueInfoInput();
            }

            // Show the content of Mosque_Info_Input when the checkbox is clicked
            secureApiCheckbox.change(function () {
                if ($(this).prop('checked')) {
                    displayMosqueInfoInput();
                } else {
                    hideMosqueInfoInput();
                }
            });

            function displayMosqueInfoInput() {
                // Dynamically load or show the content of Mosque_Info_Input
                $('#mosque-info-container').html('<?php ob_start(); Mosque_Info_Input::initialize_settings(); echo addslashes(ob_get_clean()); ?>');
            }

            function hideMosqueInfoInput() {
                // Hide the content of Mosque_Info_Input
                $('#mosque-info-container').html('');
            }
        });
  
}