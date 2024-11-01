<?php
/*
Plugin Name: Simple Custom Coming Soon/Maintenance Mode
Description: A customizable Coming Soon/Maintenance Mode plugin.
Version: 1.0
Author: Sachinraj CP
Text Domain: simple-custom-coming-soon-maintenance-mode
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Stable Tag: 1.0
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Include core plugin class file
if (!class_exists('SCCS_Coming_Soon')) {
    include_once plugin_dir_path(__FILE__) . 'class-simple-custom-coming-soon-maintenance-mode.php';
}

// Initialize the plugin
function sccs_coming_soon_init() {
    $plugin = new SCCS_Coming_Soon();
    $plugin->run();
}
add_action('plugins_loaded', 'sccs_coming_soon_init');

// Register settings
function sccs_register_settings() {
    register_setting(
        'sccs_settings_group', // Option group name
        'sccs_option_name',    // Option name in the database
        'sccs_sanitize_options' // Sanitization callback function
    );
}
add_action('admin_init', 'sccs_register_settings');

// Example sanitization function
function sccs_sanitize_options($input) {
    $sanitized_input = array();

    // Sanitize individual fields
    if (isset($input['field_one'])) {
        $sanitized_input['field_one'] = sanitize_text_field($input['field_one']);
    }
    if (isset($input['field_two'])) {
        $sanitized_input['field_two'] = intval($input['field_two']);
    }

    return $sanitized_input;
}

// Define the main plugin class
class SCCS_Coming_Soon {

    public function run() {
        add_action('template_redirect', array($this, 'enable_coming_soon_mode'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
    }

    public function enable_coming_soon_mode() {
        if (!current_user_can('manage_options') && !is_user_logged_in()) {
            $is_coming_soon_enabled = get_option('sccs_coming_soon_enabled');
            if ($is_coming_soon_enabled) {
                // Get the content to show
                $content = $this->get_coming_soon_content();
                
                // Use WordPress functions to display the proper content
                // get_header(); // Display the header template
                echo $content; // Display coming soon message
                // get_footer(); // Display the footer template
                exit; // Prevent the rest of the page from rendering
            }
        }
    }
    
    public function enqueue_styles() {
        if (!current_user_can('manage_options') && !is_user_logged_in()) {
            $is_coming_soon_enabled = get_option('sccs_coming_soon_enabled');
            if ($is_coming_soon_enabled) {
                $version = '1.0.0'; // Define your version number here
                wp_enqueue_style(
                    'sccs-coming-soon-style', 
                    plugin_dir_url(__FILE__) . 'css/coming-soon-style.css', // Correct CSS file path
                    array(), 
                    $version // Version number
                );
            }
        }
    }

    public function get_coming_soon_content() {
        ob_start();
        ?>
        <div class="coming-soon-wrapper">
            <h1><?php esc_html_e('Coming Soon', 'simple-custom-coming-soon-maintenance-mode'); ?></h1>
            <p><?php echo esc_html(get_option('sccs_coming_soon_message')); ?></p>
        </div>
        <?php
        return ob_get_clean();
    }

    public function add_admin_menu() {
        add_options_page(
            esc_html__('Coming Soon Settings', 'simple-custom-coming-soon-maintenance-mode'), 
            esc_html__('Coming Soon', 'simple-custom-coming-soon-maintenance-mode'), 
            'manage_options', 
            'sccs-coming-soon', 
            array($this, 'settings_page')
        );
    }

    public function register_settings() {
        register_setting('sccs_coming_soon_settings', 'sccs_coming_soon_enabled', array(
            'sanitize_callback' => 'absint' // Sanitize checkbox to ensure it's an integer (0 or 1)
        ));
        register_setting('sccs_coming_soon_settings', 'sccs_coming_soon_message', array(
            'sanitize_callback' => 'sanitize_textarea_field' // Sanitize textarea input
        ));
    }

    public function settings_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Coming Soon/Maintenance Mode', 'simple-custom-coming-soon-maintenance-mode'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('sccs_coming_soon_settings');
                do_settings_sections('sccs_coming_soon_settings');
                ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Enable Coming Soon Mode', 'simple-custom-coming-soon-maintenance-mode'); ?></th>
                        <td><input type="checkbox" name="sccs_coming_soon_enabled" value="1" <?php checked(1, get_option('sccs_coming_soon_enabled'), true); ?> /></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Custom Message', 'simple-custom-coming-soon-maintenance-mode'); ?></th>
                        <td><textarea name="sccs_coming_soon_message" rows="5" cols="50"><?php echo esc_textarea(get_option('sccs_coming_soon_message')); ?></textarea></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}
?>
<?php
echo '<style>
.coming-soon-wrapper h1 {
    font-size: 100px !important;
    text-align: center !important;
    padding-top: 300px !important;
    color: white !important;
}
.coming-soon-wrapper p {
    text-align: center !important;
    font-size: 28px !important;
    margin-top: -40px !important;
    color: white !important;
}
.coming-soon-wrapper {
    padding-bottom: 170px;
    background: rgb(145, 151, 208);
    background: linear-gradient(90deg, rgba(145, 151, 208, 1) 13%, rgba(255, 124, 116, 1) 98%);
    border-radius: 30px;
    margin: 30px;
}
    @media only screen and (min-width: 1920px){
    .coming-soon-wrapper {
    padding-bottom: 500px;
}
}

</style>';

?>