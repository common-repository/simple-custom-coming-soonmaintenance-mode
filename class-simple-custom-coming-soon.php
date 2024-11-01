<?php

if (!defined('ABSPATH')) {
    exit;
}

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
                wp_die(esc_html($this->get_coming_soon_content()), esc_html__('Coming Soon', 'simple-custom-coming-soon-maintenance-mode'));
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
                    plugin_dir_url(__FILE__) . '../css/coming-soon-style.css', 
                    array(), // Dependencies, if any
                    $version // Version number
                );
                add_filter('body_class', function($classes) {
                    $classes[] = 'coming-soon-mode';
                    return $classes;
                });
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
