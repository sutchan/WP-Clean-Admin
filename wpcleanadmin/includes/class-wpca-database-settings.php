<?php

/**
 * Database settings functionality for WP Clean Admin
 * 
 * @file wpcleanadmin/includes/class-wpca-database-settings.php
 * @version 1.7.15
 * @updated 2025-11-29
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * WPCA_Database_Settings class
 * Manages database optimization settings
 */
class WPCA_Database_Settings {
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->init();
    }
    
    /**
     * Initialize the settings class
     */
    private function init() {
        // Register hooks
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    /**
     * Add database settings page to admin menu
     */
    public function add_settings_page() {
        add_submenu_page(
            'wp-clean-admin',
            __('Database Settings', 'wp-clean-admin'),
            __('Database', 'wp-clean-admin'),
            'manage_options',
            'wp-clean-admin-database',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Register database settings
     */
    public function register_settings() {
        register_setting(
            'wpca_database_settings_group',
            'wpca_database_settings',
            array($this, 'validate_settings')
        );
        
        // Add settings sections
        add_settings_section(
            'wpca_database_cleanup_section',
            __('Database Cleanup Settings', 'wp-clean-admin'),
            array($this, 'render_cleanup_section'),
            'wp-clean-admin-database'
        );
        
        add_settings_section(
            'wpca_database_optimize_section',
            __('Database Optimization Settings', 'wp-clean-admin'),
            array($this, 'render_optimize_section'),
            'wp-clean-admin-database'
        );
        
        add_settings_section(
            'wpca_database_schedule_section',
            __('Scheduled Cleanup Settings', 'wp-clean-admin'),
            array($this, 'render_schedule_section'),
            'wp-clean-admin-database'
        );
        
        // Add settings fields
        add_settings_field(
            'wpca_database_cleanup_revisions',
            __('Clean up post revisions', 'wp-clean-admin'),
            array($this, 'render_cleanup_revisions_field'),
            'wp-clean-admin-database',
            'wpca_database_cleanup_section'
        );
        
        add_settings_field(
            'wpca_database_cleanup_auto_drafts',
            __('Clean up auto drafts', 'wp-clean-admin'),
            array($this, 'render_cleanup_auto_drafts_field'),
            'wp-clean-admin-database',
            'wpca_database_cleanup_section'
        );
        
        add_settings_field(
            'wpca_database_cleanup_trashed_posts',
            __('Clean up trashed posts', 'wp-clean-admin'),
            array($this, 'render_cleanup_trashed_posts_field'),
            'wp-clean-admin-database',
            'wpca_database_cleanup_section'
        );
        
        add_settings_field(
            'wpca_database_cleanup_spam_comments',
            __('Clean up spam comments', 'wp-clean-admin'),
            array($this, 'render_cleanup_spam_comments_field'),
            'wp-clean-admin-database',
            'wpca_database_cleanup_section'
        );
        
        add_settings_field(
            'wpca_database_cleanup_trash_comments',
            __('Clean up trashed comments', 'wp-clean-admin'),
            array($this, 'render_cleanup_trash_comments_field'),
            'wp-clean-admin-database',
            'wpca_database_cleanup_section'
        );
        
        add_settings_field(
            'wpca_database_cleanup_orphans',
            __('Clean up orphaned data', 'wp-clean-admin'),
            array($this, 'render_cleanup_orphans_field'),
            'wp-clean-admin-database',
            'wpca_database_cleanup_section'
        );
        
        add_settings_field(
            'wpca_database_cleanup_expired_transients',
            __('Clean up expired transients', 'wp-clean-admin'),
            array($this, 'render_cleanup_expired_transients_field'),
            'wp-clean-admin-database',
            'wpca_database_cleanup_section'
        );
        
        add_settings_field(
            'wpca_database_cleanup_oembed_cache',
            __('Clean up oEmbed cache', 'wp-clean-admin'),
            array($this, 'render_cleanup_oembed_cache_field'),
            'wp-clean-admin-database',
            'wpca_database_cleanup_section'
        );
        
        add_settings_field(
            'wpca_database_optimize_tables',
            __('Optimize database tables', 'wp-clean-admin'),
            array($this, 'render_optimize_tables_field'),
            'wp-clean-admin-database',
            'wpca_database_optimize_section'
        );
        
        add_settings_field(
            'wpca_database_schedule_frequency',
            __('Scheduled cleanup frequency', 'wp-clean-admin'),
            array($this, 'render_schedule_frequency_field'),
            'wp-clean-admin-database',
            'wpca_database_schedule_section'
        );
    }
    
    /**
     * Validate database settings
     * 
     * @param array $input Input settings array
     * @return array Validated settings array
     */
    public function validate_settings($input) {
        $validated = array();
        
        // Cleanup settings
        $validated['cleanup_revisions'] = isset($input['cleanup_revisions']) ? (int) $input['cleanup_revisions'] : 0;
        $validated['cleanup_auto_drafts'] = isset($input['cleanup_auto_drafts']) ? (int) $input['cleanup_auto_drafts'] : 0;
        $validated['cleanup_trashed_posts'] = isset($input['cleanup_trashed_posts']) ? (int) $input['cleanup_trashed_posts'] : 0;
        $validated['cleanup_spam_comments'] = isset($input['cleanup_spam_comments']) ? (int) $input['cleanup_spam_comments'] : 0;
        $validated['cleanup_trash_comments'] = isset($input['cleanup_trash_comments']) ? (int) $input['cleanup_trash_comments'] : 0;
        $validated['cleanup_orphans'] = isset($input['cleanup_orphans']) ? (int) $input['cleanup_orphans'] : 0;
        $validated['cleanup_expired_transients'] = isset($input['cleanup_expired_transients']) ? (int) $input['cleanup_expired_transients'] : 0;
        $validated['cleanup_oembed_cache'] = isset($input['cleanup_oembed_cache']) ? (int) $input['cleanup_oembed_cache'] : 0;
        
        // Optimization settings
        $validated['optimize_tables'] = isset($input['optimize_tables']) ? (int) $input['optimize_tables'] : 0;
        
        // Schedule settings
        $validated['schedule_frequency'] = isset($input['schedule_frequency']) && in_array($input['schedule_frequency'], array('daily', 'weekly', 'monthly', 'disabled')) ? $input['schedule_frequency'] : 'disabled';
        
        // Update scheduled cleanup based on settings
        $this->update_scheduled_cleanup($validated['schedule_frequency']);
        
        return $validated;
    }
    
    /**
     * Update scheduled cleanup based on settings
     * 
     * @param string $frequency Cleanup frequency
     */
    private function update_scheduled_cleanup($frequency) {
        if (class_exists('WPCA_Database')) {
            $database = WPCA_Database::get_instance();
            
            if ($frequency === 'disabled') {
                $database->remove_scheduled_cleanup();
            } else {
                $database->set_scheduled_cleanup($frequency);
            }
        }
    }
    
    /**
     * Render cleanup section
     */
    public function render_cleanup_section() {
        echo '<p>' . __('Configure which types of data to clean up from your WordPress database.', 'wp-clean-admin') . '</p>';
    }
    
    /**
     * Render optimize section
     */
    public function render_optimize_section() {
        echo '<p>' . __('Configure database table optimization settings.', 'wp-clean-admin') . '</p>';
    }
    
    /**
     * Render schedule section
     */
    public function render_schedule_section() {
        echo '<p>' . __('Configure automatic scheduled database cleanup.', 'wp-clean-admin') . '</p>';
    }
    
    /**
     * Render cleanup revisions field
     */
    public function render_cleanup_revisions_field() {
        $settings = $this->get_settings();
        $value = isset($settings['cleanup_revisions']) ? $settings['cleanup_revisions'] : 0;
        echo '<input type="checkbox" name="wpca_database_settings[cleanup_revisions]" value="1" ' . checked(1, $value, false) . ' />';
        echo '<label for="wpca_database_settings[cleanup_revisions]"> ' . __('Remove old post revisions', 'wp-clean-admin') . '</label>';
    }
    
    /**
     * Render cleanup auto drafts field
     */
    public function render_cleanup_auto_drafts_field() {
        $settings = $this->get_settings();
        $value = isset($settings['cleanup_auto_drafts']) ? $settings['cleanup_auto_drafts'] : 0;
        echo '<input type="checkbox" name="wpca_database_settings[cleanup_auto_drafts]" value="1" ' . checked(1, $value, false) . ' />';
        echo '<label for="wpca_database_settings[cleanup_auto_drafts]"> ' . __('Remove auto drafts', 'wp-clean-admin') . '</label>';
    }
    
    /**
     * Render cleanup trashed posts field
     */
    public function render_cleanup_trashed_posts_field() {
        $settings = $this->get_settings();
        $value = isset($settings['cleanup_trashed_posts']) ? $settings['cleanup_trashed_posts'] : 0;
        echo '<input type="checkbox" name="wpca_database_settings[cleanup_trashed_posts]" value="1" ' . checked(1, $value, false) . ' />';
        echo '<label for="wpca_database_settings[cleanup_trashed_posts]"> ' . __('Remove trashed posts', 'wp-clean-admin') . '</label>';
    }
    
    /**
     * Render cleanup spam comments field
     */
    public function render_cleanup_spam_comments_field() {
        $settings = $this->get_settings();
        $value = isset($settings['cleanup_spam_comments']) ? $settings['cleanup_spam_comments'] : 0;
        echo '<input type="checkbox" name="wpca_database_settings[cleanup_spam_comments]" value="1" ' . checked(1, $value, false) . ' />';
        echo '<label for="wpca_database_settings[cleanup_spam_comments]"> ' . __('Remove spam comments', 'wp-clean-admin') . '</label>';
    }
    
    /**
     * Render cleanup trash comments field
     */
    public function render_cleanup_trash_comments_field() {
        $settings = $this->get_settings();
        $value = isset($settings['cleanup_trash_comments']) ? $settings['cleanup_trash_comments'] : 0;
        echo '<input type="checkbox" name="wpca_database_settings[cleanup_trash_comments]" value="1" ' . checked(1, $value, false) . ' />';
        echo '<label for="wpca_database_settings[cleanup_trash_comments]"> ' . __('Remove trashed comments', 'wp-clean-admin') . '</label>';
    }
    
    /**
     * Render cleanup orphans field
     */
    public function render_cleanup_orphans_field() {
        $settings = $this->get_settings();
        $value = isset($settings['cleanup_orphans']) ? $settings['cleanup_orphans'] : 0;
        echo '<input type="checkbox" name="wpca_database_settings[cleanup_orphans]" value="1" ' . checked(1, $value, false) . ' />';
        echo '<label for="wpca_database_settings[cleanup_orphans]"> ' . __('Remove orphaned data (postmeta, commentmeta, etc.)', 'wp-clean-admin') . '</label>';
    }
    
    /**
     * Render cleanup expired transients field
     */
    public function render_cleanup_expired_transients_field() {
        $settings = $this->get_settings();
        $value = isset($settings['cleanup_expired_transients']) ? $settings['cleanup_expired_transients'] : 0;
        echo '<input type="checkbox" name="wpca_database_settings[cleanup_expired_transients]" value="1" ' . checked(1, $value, false) . ' />';
        echo '<label for="wpca_database_settings[cleanup_expired_transients]"> ' . __('Remove expired transients', 'wp-clean-admin') . '</label>';
    }
    
    /**
     * Render cleanup oembed cache field
     */
    public function render_cleanup_oembed_cache_field() {
        $settings = $this->get_settings();
        $value = isset($settings['cleanup_oembed_cache']) ? $settings['cleanup_oembed_cache'] : 0;
        echo '<input type="checkbox" name="wpca_database_settings[cleanup_oembed_cache]" value="1" ' . checked(1, $value, false) . ' />';
        echo '<label for="wpca_database_settings[cleanup_oembed_cache]"> ' . __('Remove old oEmbed cache', 'wp-clean-admin') . '</label>';
    }
    
    /**
     * Render optimize tables field
     */
    public function render_optimize_tables_field() {
        $settings = $this->get_settings();
        $value = isset($settings['optimize_tables']) ? $settings['optimize_tables'] : 0;
        echo '<input type="checkbox" name="wpca_database_settings[optimize_tables]" value="1" ' . checked(1, $value, false) . ' />';
        echo '<label for="wpca_database_settings[optimize_tables]"> ' . __('Automatically optimize database tables after cleanup', 'wp-clean-admin') . '</label>';
    }
    
    /**
     * Render schedule frequency field
     */
    public function render_schedule_frequency_field() {
        $settings = $this->get_settings();
        $value = isset($settings['schedule_frequency']) ? $settings['schedule_frequency'] : 'disabled';
        
        echo '<select name="wpca_database_settings[schedule_frequency]">';
        echo '<option value="disabled" ' . selected($value, 'disabled', false) . '>' . __('Disabled', 'wp-clean-admin') . '</option>';
        echo '<option value="daily" ' . selected($value, 'daily', false) . '>' . __('Daily', 'wp-clean-admin') . '</option>';
        echo '<option value="weekly" ' . selected($value, 'weekly', false) . '>' . __('Weekly', 'wp-clean-admin') . '</option>';
        echo '<option value="monthly" ' . selected($value, 'monthly', false) . '>' . __('Monthly', 'wp-clean-admin') . '</option>';
        echo '</select>';
    }
    
    /**
     * Get database settings
     * 
     * @return array Database settings
     */
    public function get_settings() {
        $settings = get_option('wpca_database_settings', array());
        
        // Default settings
        $defaults = array(
            'cleanup_revisions' => 1,
            'cleanup_auto_drafts' => 1,
            'cleanup_trashed_posts' => 1,
            'cleanup_spam_comments' => 1,
            'cleanup_trash_comments' => 1,
            'cleanup_orphans' => 1,
            'cleanup_expired_transients' => 1,
            'cleanup_oembed_cache' => 1,
            'optimize_tables' => 1,
            'schedule_frequency' => 'weekly'
        );
        
        return wp_parse_args($settings, $defaults);
    }
    
    /**
     * Render database settings page
     */
    public function render_settings_page() {
        ?>  
        <div class="wrap">
            <h1><?php _e('WP Clean Admin - Database Settings', 'wp-clean-admin'); ?></h1>
            
            <!-- Database Info -->
            <div class="wpca-database-info">
                <h2><?php _e('Database Information', 'wp-clean-admin'); ?></h2>
                <div id="wpca-database-info-container">
                    <p><?php _e('Loading database information...', 'wp-clean-admin'); ?></p>
                </div>
            </div>
            
            <!-- Cleanup Actions -->
            <div class="wpca-database-actions">
                <h2><?php _e('Database Actions', 'wp-clean-admin'); ?></h2>
                <div class="wpca-action-buttons">
                    <button id="wpca-run-cleanup" class="button button-primary">
                        <?php _e('Run Database Cleanup', 'wp-clean-admin'); ?>
                    </button>
                    <button id="wpca-optimize-tables" class="button button-secondary">
                        <?php _e('Optimize Database Tables', 'wp-clean-admin'); ?>
                    </button>
                    <div id="wpca-action-results"></div>
                </div>
            </div>
            
            <!-- Settings Form -->
            <form method="post" action="options.php">
                <?php
                settings_fields('wpca_database_settings_group');
                do_settings_sections('wp-clean-admin-database');
                submit_button();
                ?>
            </form>
            
            <!-- Cleanup Statistics -->
            <div class="wpca-cleanup-statistics">
                <h2><?php _e('Cleanup Statistics', 'wp-clean-admin'); ?></h2>
                <div id="wpca-cleanup-stats-container">
                    <p><?php _e('Loading cleanup statistics...', 'wp-clean-admin'); ?></p>
                </div>
            </div>
        </div>
        <?php
    }
}
