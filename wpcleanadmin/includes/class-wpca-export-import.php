<?php
if (!defined('ABSPATH')) exit;

class WPCA_Export_Import {
    public function __construct() {
        add_action('admin_init', array($this, 'init'));
    }

    public function init() {
        // Handle export/import actions
        add_action('admin_post_wpca_export_settings', array($this, 'export_settings'));
        add_action('admin_post_wpca_import_settings', array($this, 'import_settings'));
    }

    public function export_settings() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        $settings = get_option('wpca_settings');
        header('Content-Disposition: attachment; filename="wpca-settings-export.json"');
        header('Content-Type: application/json');
        echo json_encode($settings);
        exit;
    }

    public function import_settings() {
        if (!current_user_can('manage_options') || !isset($_FILES['wpca_import_file'])) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        $file = $_FILES['wpca_import_file']['tmp_name'];
        $settings = json_decode(file_get_contents($file), true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            update_option('wpca_settings', $settings);
            wp_redirect(admin_url('options-general.php?page=wp_clean_admin&imported=1'));
        } else {
            wp_redirect(admin_url('options-general.php?page=wp_clean_admin&import_error=1'));
        }
        exit;
    }
}