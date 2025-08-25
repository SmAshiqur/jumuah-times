<?php
class Mosque_Info_Input {

    public static function initialize_settings() {
        // Register mosque info specific settings and fields
        register_setting('mosque_prayer_time_settings', 'mosque_prayer_time_mosque_name');
        register_setting('mosque_prayer_time_settings', 'mosque_prayer_time_mosque_slug');

        add_settings_field('mosque_prayer_time_mosque_name', 'Name of the Mosque', array('Mosque_Info_Input', 'text_input_callback'), 'mosque_prayer_time_settings', 'mosque_prayer_time_section', array('field_name' => 'mosque_prayer_time_mosque_name', 'is_secure_api_activated' => self::is_secure_api_activated()));
        add_settings_field('mosque_prayer_time_mosque_slug', 'Shortcode', array('Mosque_Info_Input', 'text_input_callback'), 'mosque_prayer_time_settings', 'mosque_prayer_time_section', array('field_name' => 'mosque_prayer_time_mosque_slug', 'is_secure_api_activated' => self::is_secure_api_activated()));

        //  JavaScript for validation
        add_action('admin_enqueue_scripts', array('Mosque_Info_Input', 'enqueue_validation_script'));
    }

    public static function text_input_callback($args) {
        $field_name = $args['field_name'];
        $field_value = get_option($field_name);
        $is_secure_api_activated = $args['is_secure_api_activated'];

        echo '<input type="text" name="' . esc_attr($field_name) . '" value="' . esc_attr($field_value) . '" ' . ($is_secure_api_activated ? 'required' : '') . '>';
    }

    public static function enqueue_validation_script() {
        ?>
        <!-- <script>
            jQuery(document).ready(function($) {
                // Validation for required fields
                $('#submit').on('click', function() {
                    var mosqueName = $('#mosque_prayer_time_mosque_name').val();
                    var mosqueSlug = $('#mosque_prayer_time_mosque_slug').val();
    
                    // Check if "Footer Widget - SECURE API" is activated
                    var isSecureApiActivated = $('#mosque_prayer_time_activated_secure_api').prop('checked');
    
                    // If "Footer Widget - SECURE API" is activated
                    if (isSecureApiActivated) {
                        // If the fields are empty and were previously filled, prevent form submission
                        if ((mosqueName === '' || mosqueSlug === '') && ($('#mosque_prayer_time_mosque_name').data('filled') || $('#mosque_prayer_time_mosque_slug').data('filled'))) {
                            alert('Both fields are required');
                            return false; // Prevent form submission
                        }
    
                        // Store whether each field is filled
                        $('#mosque_prayer_time_mosque_name').data('filled', mosqueName !== '');
                        $('#mosque_prayer_time_mosque_slug').data('filled', mosqueSlug !== '');
                    }
                    // Continue with form submission if "Footer Widget - SECURE API" is not activated
                    return true;
                });
            });
        </script> -->
        <?php
    }
    
    

    //  function to check if "Footer Widget - SECURE API" is activated
    private static function is_secure_api_activated() {
        return get_option('mosque_prayer_time_activated_secure_api');
    }
}
?>
