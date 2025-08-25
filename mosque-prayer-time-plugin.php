<?php
/**
 * Plugin Name: Jummah Times
 * Plugin URI: https://masjidsolutions.net/
 * Description: Beautiful Jummah prayer times display with Khateeb name and topic in an elegant card layout.
 * Version: 2.0.2
 * Requires at least: 6.4.1
 * Requires PHP: 7.2
 * Author: Masjid Solutions
 * Author URI: https://masjidsolutions.net/
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: mjt
 * GitHub Plugin URI: SmAshiqur/jumuah-times
 */


if (!defined('ABSPATH')) {
    exit;
}

// Fixed version constant to match header
define('MJT_VERSION', '2.0.0');
define('MJT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MJT_PLUGIN_PATH', plugin_dir_path(__FILE__));

require 'lib/plugin-update-checker-master/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$updateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/SmAshiqur/jumuah-times',
    __FILE__,
    'jumuah-times' 
);

// Set the branch (change to 'master' if that's your default branch)
$updateChecker->setBranch('main');

// Enable release assets if you plan to use GitHub releases
$updateChecker->getVcsApi()->enableReleaseAssets();







/**
 * Main Plugin Class
 */
class Masjid_Jummah_Times {

    /**
     * Constructor
     */
    public function __construct() {
        // Admin menu and settings
        add_action('admin_menu', array($this, 'add_menu_page'));
        add_action('admin_init', array($this, 'initialize_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));

        // Register shortcode
        add_action('init', array($this, 'register_shortcodes'));
        
        // Enqueue frontend styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_styles'));
    }

    /**
     * Add admin menu page
     */
    public function add_menu_page() {
        add_menu_page('Masjid Jummah Times', 'Jummah Times', 'manage_options', 'masjid-jummah-times', array($this, 'settings_page'), 'dashicons-calendar-alt');
    }

    /**
     * Enqueue admin styles
     */
    public function enqueue_admin_styles($hook) {
        // Only load on our plugin page
        if ($hook != 'toplevel_page_masjid-jummah-times') {
            return;
        }
        
        // Check if the file exists before enqueuing
        $admin_css_file = plugin_dir_path(__FILE__) . 'assets/css/admin-styles.css';
        if (file_exists($admin_css_file)) {
            wp_enqueue_style('jummah-times-admin-styles', plugin_dir_url(__FILE__) . 'assets/css/admin-styles.css', array(), '1.0.1');
        } else {
            // Create directory and file if it doesn't exist
            $this->create_default_css_files();
            wp_enqueue_style('jummah-times-admin-styles', plugin_dir_url(__FILE__) . 'assets/css/admin-styles.css', array(), '1.0.1');
        }
    }

    /**
     * Enqueue frontend styles
     */
    public function enqueue_frontend_styles() {
        // Check if the file exists before enqueuing
        $frontend_css_file = plugin_dir_path(__FILE__) . 'assets/css/frontend-styles.css';
        if (file_exists($frontend_css_file)) {
            wp_enqueue_style('jummah-times-frontend-styles', plugin_dir_url(__FILE__) . 'assets/css/frontend-styles.css', array(), '1.0.1');
            wp_enqueue_style('jummah-times-fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');
        } else {
            // Create directory and file if it doesn't exist
            $this->create_default_css_files();
            wp_enqueue_style('jummah-times-frontend-styles', plugin_dir_url(__FILE__) . 'assets/css/frontend-styles.css', array(), '1.0.1');
            wp_enqueue_style('jummah-times-fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');
        }
    }

    /**
     * Create default CSS files if they don't exist
     */
    private function create_default_css_files() {
        // Create assets directory
        $assets_dir = plugin_dir_path(__FILE__) . 'assets';
        if (!file_exists($assets_dir)) {
            wp_mkdir_p($assets_dir);
        }
        
        // Create CSS directory
        $css_dir = $assets_dir . '/css';
        if (!file_exists($css_dir)) {
            wp_mkdir_p($css_dir);
        }
        
        // Create admin styles CSS file
        $admin_css_file = $css_dir . '/admin-styles.css';
        if (!file_exists($admin_css_file)) {
            $admin_css_content = "
                                /* Admin Styles */
                                .shortcode-instructions {
                                    background: #fff;
                                    padding: 20px;
                                    border-radius: 5px;
                                    border-left: 4px solid #2271b1;
                                    margin: 20px 0;
                                }

                                .preview-section {
                                    background: #fff;
                                    padding: 20px;
                                    border-radius: 5px;
                                    margin: 20px 0;
                                }

                                .preview-container {
                                    max-width: 600px;
                                    margin: 0 auto;
                                }
                                ";
            file_put_contents($admin_css_file, $admin_css_content);
        }
        
        // Create frontend styles CSS file
        $frontend_css_file = $css_dir . '/frontend-styles.css';
        if (!file_exists($frontend_css_file)) {
            $frontend_css_content = "
                                       
                                        ";
            file_put_contents($frontend_css_file, $frontend_css_content);
        }
    }

    /**
     * Initialize plugin settings
     */
    public function initialize_settings() {
        // Register settings
        register_setting('masjid_jummah_times_settings', 'masjid_jummah_times_mosque_name');
        register_setting('masjid_jummah_times_settings', 'masjid_jummah_times_mosque_slug');
        register_setting('masjid_jummah_times_settings', 'masjid_jummah_times_card_style', array('default' => 'modern'));

        // Add fields
        add_settings_field(
            'masjid_jummah_times_mosque_name', 
            'Mosque Name', 
            array($this, 'mosque_name_callback'), 
            'masjid_jummah_times_settings', 
            'masjid_jummah_times_section'
        );
        
        add_settings_field(
            'masjid_jummah_times_mosque_slug', 
            'Mosque Slug (from Masjid Solutions)', 
            array($this, 'mosque_slug_callback'), 
            'masjid_jummah_times_settings', 
            'masjid_jummah_times_section'
        );
        
        add_settings_field(
            'masjid_jummah_times_card_style', 
            'Card Style', 
            array($this, 'card_style_callback'), 
            'masjid_jummah_times_settings', 
            'masjid_jummah_times_section'
        );

        // Add settings section
        add_settings_section(
            'masjid_jummah_times_section', 
            'Jummah Times Settings', 
            array($this, 'section_callback'), 
            'masjid_jummah_times_settings'
        );
    }

    /**
     * Render settings section callback
     */
    public function section_callback() {
        echo '<p>Configure your mosque information to display Jummah times.</p>';
    }

    /**
     * Render mosque name callback
     */
    public function mosque_name_callback() {
        $mosque_name = get_option('masjid_jummah_times_mosque_name', '');
        echo '<input type="text" name="masjid_jummah_times_mosque_name" value="' . esc_attr($mosque_name) . '" class="regular-text">';
        echo '<p class="description">Enter the name of your mosque as you want it to appear on the card.</p>';
    }

    /**
     * Render mosque slug callback
     */
    public function mosque_slug_callback() {
        $mosque_slug = get_option('masjid_jummah_times_mosque_slug', '');
        echo '<input type="text" name="masjid_jummah_times_mosque_slug" value="' . esc_attr($mosque_slug) . '" class="regular-text">';
        echo '<p class="description">Enter the mosque slug from your Masjid Solutions account.</p>';
    }

    /**
     * Render card style callback
     */
    public function card_style_callback() {
        $card_style = get_option('masjid_jummah_times_card_style', 'modern');
        ?>
        <select name="masjid_jummah_times_card_style">
            <option value="modern" <?php selected($card_style, 'modern'); ?>>Modern</option>
            <option value="classic" <?php selected($card_style, 'classic'); ?>>Classic</option>
            <option value="minimal" <?php selected($card_style, 'minimal'); ?>>Minimal</option>
        </select>
        <p class="description">Select a style for the Jummah times card.</p>
        <?php
    }

/**
 * Render settings page with modern UI
 */
public function settings_page() {
    ?>
    <div class="wrap mjt-admin-container">
        <div class="mjt-header">
            <h1><?php echo esc_html__('Masjid Jummah Times', 'masjid-jummah-times'); ?></h1>
      
        </div>

        <div class="mjt-admin-content">
            <!-- Navigation Tabs -->
            <div class="nav-tab-wrapper">
                <a href="#settings" class="nav-tab nav-tab-active"><?php echo esc_html__('Settings', 'masjid-jummah-times'); ?></a>
                <a href="#shortcodes" class="nav-tab"><?php echo esc_html__('Usage', 'masjid-jummah-times'); ?></a>
            </div>

            <!-- Settings Tab -->
            <div id="settings" class="mjt-tab-content active">
                <div class="mjt-settings-form">
                    <form method="post" action="options.php">
                        <?php
                        settings_fields('masjid_jummah_times_settings');
                        do_settings_sections('masjid_jummah_times_settings');
                        submit_button(__('Save Settings', 'masjid-jummah-times'), 'primary mjt-save-button');
                        ?>
                    </form>
                </div>
            </div>

            <!-- Shortcodes Tab -->
            <div id="shortcodes" class="mjt-tab-content">
                <div class="mjt-card">
                    <h2><?php echo esc_html__('How to Use the Jummah Times Card', 'masjid-jummah-times'); ?></h2>
                    
                    <div class="mjt-shortcode-grid">
                        <div class="mjt-shortcode-item">
                            <h3><?php echo esc_html__('Default Style', 'masjid-jummah-times'); ?></h3>
                            <div class="mjt-shortcode-box">
                                <code>[jummah_times]</code>
                                <button class="mjt-copy-button" data-shortcode="[jummah_times]">
                                    <span class="dashicons dashicons-clipboard"></span>
                                </button>
                            </div>
                            <p><?php echo esc_html__('Default style based on your settings', 'masjid-jummah-times'); ?></p>
                        </div>
                        
                        <div class="mjt-shortcode-item">
                            <h3><?php echo esc_html__('Modern Style', 'masjid-jummah-times'); ?></h3>
                            <div class="mjt-shortcode-box">
                                <code>[jummah_times style="modern"]</code>
                                <button class="mjt-copy-button" data-shortcode="[jummah_times style=&quot;modern&quot;]">
                                    <span class="dashicons dashicons-clipboard"></span>
                                </button>
                            </div>
                            <p><?php echo esc_html__('Clean, contemporary design with shadow effects', 'masjid-jummah-times'); ?></p>
                        </div>
                        
                        <div class="mjt-shortcode-item">
                            <h3><?php echo esc_html__('Classic Style', 'masjid-jummah-times'); ?></h3>
                            <div class="mjt-shortcode-box">
                                <code>[jummah_times style="classic"]</code>
                                <button class="mjt-copy-button" data-shortcode="[jummah_times style=&quot;classic&quot;]">
                                    <span class="dashicons dashicons-clipboard"></span>
                                </button>
                            </div>
                            <p><?php echo esc_html__('Traditional design with border and subtle elements', 'masjid-jummah-times'); ?></p>
                        </div>
                        
                        <div class="mjt-shortcode-item">
                            <h3><?php echo esc_html__('Minimal Style', 'masjid-jummah-times'); ?></h3>
                            <div class="mjt-shortcode-box">
                                <code>[jummah_times style="minimal"]</code>
                                <button class="mjt-copy-button" data-shortcode="[jummah_times style=&quot;minimal&quot;]">
                                    <span class="dashicons dashicons-clipboard"></span>
                                </button>
                            </div>
                            <p><?php echo esc_html__('Simple, lightweight design focusing on readability', 'masjid-jummah-times'); ?></p>
                        </div>
                    </div>
                    
                    <div class="mjt-shortcode-tip">
                        <div class="mjt-tip-icon">
                            <span class="dashicons dashicons-lightbulb"></span>
                        </div>
                        <div class="mjt-tip-content">
                            <h4><?php echo esc_html__('Pro Tip', 'masjid-jummah-times'); ?></h4>
                            <p><?php echo esc_html__('Simply copy and paste any shortcode into your page builder or WordPress editor. The Jummah Times card will automatically adapt to your theme colors.', 'masjid-jummah-times'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Modern Admin Styling */
        .mjt-admin-container {
            max-width: 1200px;
            margin: 20px auto;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
        }

        .mjt-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }

        .mjt-admin-content {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        /* Tabs Styling */
        .nav-tab-wrapper {
            border-bottom: 1px solid #ccc;
            margin: 0;
            padding-top: 9px;
            padding-bottom: 0;
            line-height: inherit;
        }

        .nav-tab {
            border-radius: 4px 4px 0 0;
            margin-left: 0.5em;
            font-size: 14px;
        }

        .nav-tab-active {
            border-bottom: 1px solid #fff;
            background: #fff;
            color: #0073aa;
        }

        /* Tab Content */
        .mjt-tab-content {
            display: none;
            padding: 20px;
        }

        .mjt-tab-content.active {
            display: block;
        }

        /* Card Style */
        .mjt-card {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
        }

        /* Settings Form */
        .mjt-settings-form {
            max-width: 800px;
        }

        .mjt-save-button {
            margin-top: 20px !important;
        }

        /* Shortcode Grid */
        .mjt-shortcode-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }

        .mjt-shortcode-item {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #0073aa;
        }

        .mjt-shortcode-item h3 {
            margin-top: 0;
            color: #23282d;
        }

        .mjt-shortcode-box {
            background: #fff;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 3px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .mjt-copy-button {
            background: none;
            border: none;
            color: #0073aa;
            cursor: pointer;
            padding: 5px;
        }

        .mjt-copy-button:hover {
            color: #00a0d2;
        }

        /* Tips Section */
        .mjt-shortcode-tip {
            background: #f0f6fc;
            border-left: 4px solid #72aee6;
            padding: 15px;
            margin-top: 30px;
            display: flex;
            align-items: flex-start;
            border-radius: 3px;
        }

        .mjt-tip-icon {
            margin-right: 15px;
            color: #0073aa;
            font-size: 24px;
        }

        .mjt-tip-content h4 {
            margin-top: 0;
            margin-bottom: 5px;
        }
    </style>

    <script>
        jQuery(document).ready(function($) {
            // Tab navigation
            $('.nav-tab').on('click', function(e) {
                e.preventDefault();
                
                // Get the target tab
                var targetTab = $(this).attr('href').substring(1);
                
                // Remove active class from all tabs and contents
                $('.nav-tab').removeClass('nav-tab-active');
                $('.mjt-tab-content').removeClass('active');
                
                // Add active class to current tab and content
                $(this).addClass('nav-tab-active');
                $('#' + targetTab).addClass('active');
            });
            
            // Copy shortcode functionality
            $('.mjt-copy-button').on('click', function() {
                var shortcode = $(this).data('shortcode');
                var tempInput = $('<input>');
                $('body').append(tempInput);
                tempInput.val(shortcode).select();
                document.execCommand('copy');
                tempInput.remove();
                
                // Visual feedback
                var originalIcon = $(this).html();
                $(this).html('<span class="dashicons dashicons-yes"></span>');
                
                setTimeout(function() {
                    $('.mjt-copy-button').html(originalIcon);
                }, 1500);
            });
        });
    </script>
    <?php
}

    /**
     * Register shortcode
     */
    public function register_shortcodes() {
        add_shortcode('jummah_times', array($this, 'render_jummah_times_card'));
    }

    /**
     * Fetch Jummah times from API
     */
    /**
     * Fetch Jummah times from API with improved error handling
     */
    private function get_jummah_times($mosque_slug) {
        if (empty($mosque_slug)) {
            error_log('Jummah Times Plugin: Empty mosque slug provided');
            return false;
        }

        $curl = curl_init();
        $base_url = 'https://www.secure-api.net/api/v1';
        $end_point = '/company/prayer/daily/schedule';
        $query_parameter = '?slug=' . urlencode($mosque_slug);
        $url = $base_url . $end_point . $query_parameter;

        // Log the URL we're trying to access for debugging
        error_log('Jummah Times Plugin: Attempting to fetch data from ' . $url);

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15, // Increased timeout
            CURLOPT_SSL_VERIFYPEER => false, // Disable SSL verification for problematic servers
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        $error = false;

        if (curl_errno($curl)) {
            $error = 'Jummah Times API Error: ' . curl_error($curl) . ' (Code: ' . curl_errno($curl) . ')';
            error_log($error);
        } else {
            $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if ($http_code != 200) {
                $error = 'Jummah Times API returned HTTP code: ' . $http_code;
                error_log($error);
            }
        }

        curl_close($curl);
        
        if ($error || !$response) {
            return false;
        }
        
        // Try to decode JSON and check for errors
        $data = json_decode($response);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('Jummah Times Plugin: JSON decode error: ' . json_last_error_msg());
            error_log('Jummah Times Plugin: Raw response: ' . substr($response, 0, 255));
            return false;
        }
        
        // Check if we got the expected data structure
        if (!isset($data->jummahTimes) || !is_array($data->jummahTimes) || empty($data->jummahTimes)) {
            error_log('Jummah Times Plugin: Invalid or empty response structure');
            error_log('Jummah Times Plugin: Response: ' . print_r($data, true));
            return false;
        }
        
        return $data->jummahTimes;
    }

    /**
     * Render Jummah times card
     */
    /**
     * Render Jummah times card with better error handling
     */
    public function render_jummah_times_card($atts) {
        // Get attributes
        $atts = shortcode_atts(array(
            'style' => get_option('masjid_jummah_times_card_style', 'modern'),
        ), $atts, 'jummah_times');
        
        $style = $atts['style'];
        
        // Use URL slug if provided, otherwise fall back to the stored option
        $mosque_slug = get_option('masjid_jummah_times_mosque_slug', '');
        $mosque_name = get_option('masjid_jummah_times_mosque_name', 'Your Mosque Name');
        
        // If we don't have a slug, show a message with better styling
        if (empty($mosque_slug)) {
            return '<div class="jummah-times-error" style="padding: 15px; border-left: 4px solid #dc3232; background: #f8f8f8; margin: 10px 0;">
                <strong>Configuration Required:</strong> Please set your mosque slug in the 
                <a href="' . admin_url('admin.php?page=masjid-jummah-times') . '">Jummah Times settings</a>.
            </div>';
        }
        
        $jummah_times = $this->get_jummah_times($mosque_slug);
        
        if (!$jummah_times) {
            $error_message = 'Error fetching Jummah times';
            if (!empty($mosque_name) && $mosque_name !== 'Your Mosque Name') {
                $error_message .= ' for ' . esc_html($mosque_name);
            }
            $error_message .= '.';
            
            return '<div class="jummah-times-error" style="padding: 15px; border-left: 4px solid #dc3232; background: #f8f8f8; margin: 10px 0;">
                <strong>API Connection Error:</strong> ' . $error_message . ' 
                Please verify your mosque slug is correct in the 
                <a href="' . admin_url('admin.php?page=masjid-jummah-times') . '">Jummah Times settings</a>.
            </div>';
        }
        
        // Start output buffering
        ob_start();
        
        // Current date
        $current_date = date('l, F j, Y');
        
        // Generate HTML based on style
        if ($style === 'modern') {
            $this->render_modern_card($mosque_name, $jummah_times, $current_date);
        } elseif ($style === 'classic') {
            $this->render_classic_card($mosque_name, $jummah_times, $current_date);
        } elseif ($style === 'minimal') {
            $this->render_minimal_card($mosque_name, $jummah_times, $current_date);
        } else {
            $this->render_modern_card($mosque_name, $jummah_times, $current_date);
        }
        
        return ob_get_clean();
    }
    
    /**
     * Render modern style card
     */
    private function render_modern_card($mosque_name, $jummah_times, $current_date) {
        ?>
        <div class="jummah-times-container modern">
          
            
            <div class="jummah-cards-container">
                <?php 
                $jummah_counter = 1; // Initialize counter
                foreach ($jummah_times as $index => $jummah): 
                ?>
                    <div class="jummah-card">
                      
                    
                        <div class="jummah-number-top">
                            Jumu'ah <?php echo $this->toRoman($jummah_counter); ?>
                        </div>

                        <div class="jummah-time">
                           <?php echo esc_html($jummah->jummahTime); ?><span> - </span> <?php echo esc_html($jummah->iqamahTime); ?>
                        </div>
                        <?php if (!empty($jummah->khateeb)): ?>
                            <div class="jummah-khateeb">
                                <span class="label"><i class="fas fa-user"></i> Khateeb:</span>
                                <span class="value"><?php echo esc_html($jummah->khateeb); ?></span>
                            </div>
                        <?php else: ?>
                            <div class="jummah-khateeb">
                                <span class="label"><i class="fas fa-user"></i> Khateeb:</span>
                                <span class="value">TBD</span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($jummah->khutbahTopic)): ?>
                            <div class="jummah-topic">
                                <span class="label"><i class="fas fa-book-open"></i> Topic:</span>
                                <span class="value"><?php echo esc_html($jummah->khutbahTopic); ?></span>
                            </div>
                        <?php else: ?>
                            <div class="jummah-topic">
                                <span class="label"><i class="fas fa-book-open"></i> Topic:</span>
                                <span class="value">TBD</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php 
                    $jummah_counter++; // Increment counter
                endforeach; 
                ?>
            </div>
            
        </div>
        <?php
    }
    
    /**
     * Convert an integer to a Roman numeral.
     * 
     * @param int $num The integer to convert.
     * @return string The Roman numeral representation.
     */
    private function toRoman($num) {
        $n = intval($num);
        $result = '';
    
        // Define Roman numeral mappings
        $lookup = [
            'M'  => 1000,
            'CM' => 900,
            'D'  => 500,
            'CD' => 400,
            'C'  => 100,
            'XC' => 90,
            'L'  => 50,
            'XL' => 40,
            'X'  => 10,
            'IX' => 9,
            'V'  => 5,
            'IV' => 4,
            'I'  => 1
        ];
    
        foreach ($lookup as $roman => $value) {
            while ($n >= $value) {
                $result .= $roman;
                $n -= $value;
            }
        }
    
        return $result;
    }
    
    
    
    /**
     * Render classic style card
     */
    private function render_classic_card($mosque_name, $jummah_times, $current_date) {
        ?>
        <div class="jummah-times-container classic">

            
            
            <div class="jummah-cards-container">
                <?php 
                $jummah_counter = 1; // Initialize counter
                foreach ($jummah_times as $index => $jummah): 
                ?>                    
                    <div class="jummah-card">
                      
                        <div class="jummah-time-badge">    
                            <div class="jummah-time-counter-classic">                            Jumu'ah <?php echo $this->toRoman($jummah_counter); ?>
                            </div>

                            <div class="jummah-time-classic-time">                          
                             <?php echo esc_html($jummah->jummahTime); ?><span> - </span> <?php echo esc_html($jummah->iqamahTime); ?>

                            </div>

                         </div>

                        <div class="jummah-details">
                            <?php if (!empty($jummah->khateeb)): ?>
                                <div class="jummah-khateeb">
                                    <span class="label">Khateeb:</span>
                                    <span class="value"><?php echo esc_html($jummah->khateeb); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($jummah->khutbahTopic)): ?>
                                <div class="jummah-topic">
                                    <span class="label">Topic:</span>
                                    <span class="value"><?php echo esc_html($jummah->khutbahTopic); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php 
                    $jummah_counter++; // Increment counter
                endforeach; 
                ?>                
            </div>




         


   
        </div>
        <?php
    }
    
    /**
     * Render minimal style card
     */
    private function render_minimal_card($mosque_name, $jummah_times, $current_date) {
        ?>
        <div class="jummah-times-container minimal">
            <div class="jummah-cards-container">
                <?php 
                $jummah_counter = 1; // Initialize counter
                foreach ($jummah_times as $index => $jummah): 
                ?>
                    <div class="jummah-card">
                        <div class="jummah-number-top">
                            Jumu'ah <?php echo $this->toRoman($jummah_counter); ?>
                        </div>
                        <div class="jummah-time">
                           <?php echo esc_html($jummah->jummahTime); ?><span> - </span> <?php echo esc_html($jummah->iqamahTime); ?>
                        </div>                    
                        <?php if (!empty($jummah->khateeb) || !empty($jummah->khutbahTopic)): ?>
                            <div class="jummah-separator"></div>
                            <div class="jummah-info">
                                <?php if (!empty($jummah->khateeb)): ?>
                                    <div class="jummah-khateeb"><?php echo esc_html($jummah->khateeb); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($jummah->khutbahTopic)): ?>
                                    <div class="jummah-topic">"<?php echo esc_html($jummah->khutbahTopic); ?>"</div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php 
                    $jummah_counter++; // Increment counter
                endforeach; 
                ?>            </div>
        </div>
              
      
        <?php
    }
}

// Initialize the plugin
$masjid_jummah_times = new Masjid_Jummah_Times();
