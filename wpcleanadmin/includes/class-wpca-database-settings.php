<?php
/**
 * WP Clean Admin Database Settings
 * 
 * Provides settings page and UI for database optimization features.
 * Handles database optimization settings, cleanup configuration, and scheduler setup.
 * 
 * @package WP_Clean_Admin
 * @since 1.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * WPCA_Database_Settings class.
 * 
 * Manages database optimization settings page and UI components.
 */
class WPCA_Database_Settings {

    /**
     * Instance of the class.
     *
     * @var WPCA_Database_Settings
     */
    protected static $instance = null;

    /**
     * Database component instance.
     *
     * @var WPCA_Database
     */
    protected $database;

    /**
     * Settings page slug.
     *
     * @var string
     */
    protected $page_slug = 'wpca-database';

    /**
     * Current tab.
     *
     * @var string
     */
    protected $current_tab = 'optimization';

    /**
     * Available tabs.
     *
     * @var array
     */
    protected $tabs = array();

    /**
     * Initialize the class.
     */
    public function __construct() {
        // Initialize database component
        if ( class_exists( 'WPCA_Database' ) ) {
            $this->database = WPCA_Database::get_instance();
        }

        // Initialize tabs
        $this->init_tabs();

        // Register hooks
        add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }

    /**
     * Initialize tabs.
     */
    public function init_tabs() {
        $this->tabs = array(
            'optimization' => array(
                'title'    => esc_html__( 'Table Optimization', 'wp-clean-admin' ),
                'callback' => array( $this, 'render_optimization_tab' ),
                'icon'     => 'dashicons-database',
            ),
            'cleanup' => array(
                'title'    => esc_html__( 'Database Cleanup', 'wp-clean-admin' ),
                'callback' => array( $this, 'render_cleanup_tab' ),
                'icon'     => 'dashicons-trash',
            ),
            'schedule' => array(
                'title'    => esc_html__( 'Scheduled Tasks', 'wp-clean-admin' ),
                'callback' => array( $this, 'render_schedule_tab' ),
                'icon'     => 'dashicons-clock',
            ),
            'info' => array(
                'title'    => esc_html__( 'Database Info', 'wp-clean-admin' ),
                'callback' => array( $this, 'render_info_tab' ),
                'icon'     => 'dashicons-info',
            ),
        );
    }

    /**
     * Add the settings page.
     */
    public function add_settings_page() {
        add_submenu_page(
            'wpca-settings',
            esc_html__( 'Database Optimization', 'wp-clean-admin' ),
            esc_html__( 'Database', 'wp-clean-admin' ),
            'manage_options',
            $this->page_slug,
            array( $this, 'render_settings_page' ),
            20
        );
    }

    /**
     * Register settings.
     */
    public function register_settings() {
        // Database optimization settings
        register_setting(
            'wpca-database-optimization',
            'wpca_auto_optimize_tables',
            array(
                'type'              => 'boolean',
                'sanitize_callback' => 'absint',
                'default'           => false,
            )
        );

        register_setting(
            'wpca-database-optimization',
            'wpca_optimize_interval',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => 'weekly',
            )
        );

        register_setting(
            'wpca-database-optimization',
            'wpca_tables_to_optimize',
            array(
                'type'              => 'array',
                'sanitize_callback' => array( $this, 'sanitize_table_list' ),
                'default'           => array(),
            )
        );

        // Database cleanup settings
        register_setting(
            'wpca-database-cleanup',
            'wpca_enable_auto_cleanup',
            array(
                'type'              => 'boolean',
                'sanitize_callback' => 'absint',
                'default'           => false,
            )
        );

        register_setting(
            'wpca-database-cleanup',
            'wpca_cleanup_interval',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => 'weekly',
            )
        );

        register_setting(
            'wpca-database-cleanup',
            'wpca_cleanup_revisions',
            array(
                'type'              => 'boolean',
                'sanitize_callback' => 'absint',
                'default'           => false,
            )
        );

        register_setting(
            'wpca-database-cleanup',
            'wpca_revision_days',
            array(
                'type'              => 'integer',
                'sanitize_callback' => 'absint',
                'default'           => 30,
            )
        );

        register_setting(
            'wpca-database-cleanup',
            'wpca_cleanup_auto_drafts',
            array(
                'type'              => 'boolean',
                'sanitize_callback' => 'absint',
                'default'           => false,
            )
        );

        register_setting(
            'wpca-database-cleanup',
            'wpca_cleanup_trashed_posts',
            array(
                'type'              => 'boolean',
                'sanitize_callback' => 'absint',
                'default'           => false,
            )
        );

        register_setting(
            'wpca-database-cleanup',
            'wpca_cleanup_spam_comments',
            array(
                'type'              => 'boolean',
                'sanitize_callback' => 'absint',
                'default'           => false,
            )
        );

        register_setting(
            'wpca-database-cleanup',
            'wpca_cleanup_trashed_comments',
            array(
                'type'              => 'boolean',
                'sanitize_callback' => 'absint',
                'default'           => false,
            )
        );

        register_setting(
            'wpca-database-cleanup',
            'wpca_cleanup_pingbacks_trackbacks',
            array(
                'type'              => 'boolean',
                'sanitize_callback' => 'absint',
                'default'           => false,
            )
        );

        register_setting(
            'wpca-database-cleanup',
            'wpca_cleanup_orphaned_postmeta',
            array(
                'type'              => 'boolean',
                'sanitize_callback' => 'absint',
                'default'           => false,
            )
        );

        register_setting(
            'wpca-database-cleanup',
            'wpca_cleanup_orphaned_commentmeta',
            array(
                'type'              => 'boolean',
                'sanitize_callback' => 'absint',
                'default'           => false,
            )
        );

        register_setting(
            'wpca-database-cleanup',
            'wpca_cleanup_orphaned_relationships',
            array(
                'type'              => 'boolean',
                'sanitize_callback' => 'absint',
                'default'           => false,
            )
        );

        register_setting(
            'wpca-database-cleanup',
            'wpca_cleanup_orphaned_usermeta',
            array(
                'type'              => 'boolean',
                'sanitize_callback' => 'absint',
                'default'           => false,
            )
        );

        register_setting(
            'wpca-database-cleanup',
            'wpca_cleanup_expired_transients',
            array(
                'type'              => 'boolean',
                'sanitize_callback' => 'absint',
                'default'           => false,
            )
        );

        register_setting(
            'wpca-database-cleanup',
            'wpca_cleanup_all_transients',
            array(
                'type'              => 'boolean',
                'sanitize_callback' => 'absint',
                'default'           => false,
            )
        );

        register_setting(
            'wpca-database-cleanup',
            'wpca_cleanup_oembed_caches',
            array(
                'type'              => 'boolean',
                'sanitize_callback' => 'absint',
                'default'           => false,
            )
        );
    }

    /**
     * Sanitize table list.
     *
     * @param array $tables Table list to sanitize.
     * @return array Sanitized table list.
     */
    public function sanitize_table_list( $tables ) {
        if ( ! is_array( $tables ) ) {
            return array();
        }

        $sanitized = array();
        foreach ( $tables as $table ) {
            $safe_table = $this->sanitize_table_name( $table );
            if ( ! empty( $safe_table ) ) {
                $sanitized[] = $safe_table;
            }
        }

        return $sanitized;
    }

    /**
     * Sanitize table name.
     *
     * @param string $table_name Table name to sanitize.
     * @return string Sanitized table name.
     */
    public function sanitize_table_name( $table_name ) {
        // Only allow alphanumeric characters, underscores, and table prefix
        global $wpdb;
        $prefix = $wpdb->prefix;
        
        $sanitized = preg_replace( '/[^a-zA-Z0-9_]/', '', $table_name );
        
        // Ensure table name starts with the WordPress prefix
        if ( strpos( $sanitized, $prefix ) === 0 ) {
            return $sanitized;
        }
        
        return '';
    }

    /**
     * Enqueue scripts.
     *
     * @param string $hook_suffix Current admin page hook suffix.
     */
    public function enqueue_scripts( $hook_suffix ) {
        if ( 'wpca-settings_page_wpca-database' !== $hook_suffix ) {
            return;
        }

        wp_enqueue_script( 'wpca-database', WPCA_URL . 'assets/js/wpca-database.js', array( 'jquery' ), WPCA_VERSION, true );

        wp_localize_script( 'wpca-database', 'wpca_database', array(
            'ajax_url'                   => admin_url( 'admin-ajax.php' ),
            'nonce'                      => wp_create_nonce( 'wpca-database' ),
            'optimizeTables'             => esc_html__( 'Optimize Tables', 'wp-clean-admin' ),
            'cleanupDatabase'            => esc_html__( 'Cleanup Database', 'wp-clean-admin' ),
            'loading'                    => esc_html__( 'Loading...', 'wp-clean-admin' ),
            'optimizing'                 => esc_html__( 'Optimizing tables...', 'wp-clean-admin' ),
            'cleaning'                   => esc_html__( 'Cleaning database...', 'wp-clean-admin' ),
            'optimizeSuccess'            => esc_html__( 'Successfully optimized %d of %d tables.', 'wp-clean-admin' ),
            'optimizeFailed'             => esc_html__( 'Table optimization failed.', 'wp-clean-admin' ),
            'selectCleanupItemsFirst'    => esc_html__( 'Please select at least one cleanup item.', 'wp-clean-admin' ),
            'confirmCleanup'             => esc_html__( 'Are you sure you want to run the selected cleanup tasks? This cannot be undone.', 'wp-clean-admin' ),
            'cleanupSuccess'             => esc_html__( 'Successfully removed %d items from the database.', 'wp-clean-admin' ),
            'cleanupFailed'              => esc_html__( 'Database cleanup failed.', 'wp-clean-admin' ),
        ));
    }

    /**
     * Render the settings page.
     */
    public function render_settings_page() {
        $this->current_tab = isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], $this->tabs ) ? $_GET['tab'] : 'optimization';
        
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Database Optimization', 'wp-clean-admin' ); ?></h1>
            
            <div class="wpca-settings-tabs">
                <nav class="nav-tab-wrapper">
                    <?php foreach ( $this->tabs as $tab_id => $tab ) : ?>
                        <a href="?page=<?php echo esc_attr( $this->page_slug ); ?>&tab=<?php echo esc_attr( $tab_id ); ?>" 
                           class="nav-tab<?php echo $this->current_tab === $tab_id ? ' nav-tab-active' : ''; ?>">
                            <span class="dashicons <?php echo esc_attr( $tab['icon'] ); ?>"></span>
                            <?php echo esc_html( $tab['title'] ); ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
                
                <div class="wpca-tab-content">
                    <?php call_user_func( $this->tabs[$this->current_tab]['callback'] ); ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render optimization tab.
     */
    public function render_optimization_tab() {
        // Get current settings
        $auto_optimize_enabled = get_option( 'wpca_auto_optimize_tables', false );
        $optimize_interval = get_option( 'wpca_optimize_interval', 'weekly' );
        $selected_tables = get_option( 'wpca_tables_to_optimize', array() );
        
        // Get WordPress tables
        global $wpdb;
        $tables = $wpdb->get_col( 'SHOW TABLES' );
        
        ?>
        <div class="wpca-settings-section">
            <h2><?php esc_html_e( 'Table Optimization', 'wp-clean-admin' ); ?></h2>
            <p><?php esc_html_e( 'Optimize database tables to reclaim unused space and improve performance.', 'wp-clean-admin' ); ?></p>
            
            <div class="wpca-optimization-actions">
                <button type="button" id="wpca-optimize-tables" class="button button-primary button-large">
                    <span class="dashicons dashicons-database"></span>
                    <?php esc_html_e( 'Optimize All Tables', 'wp-clean-admin' ); ?>
                </button>
                <div id="wpca-optimization-results" class="wpca-results"></div>
            </div>
            
            <div class="wpca-optimization-tables">
                <h3><?php esc_html_e( 'Select Tables to Optimize', 'wp-clean-admin' ); ?></h3>
                <p><?php esc_html_e( 'Choose which tables to include in scheduled optimizations:', 'wp-clean-admin' ); ?></p>
                
                <form method="post" action="options.php">
                    <?php settings_fields( 'wpca-database-optimization' ); ?>
                    
                    <table class="widefat fixed">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'Table', 'wp-clean-admin' ); ?></th>
                                <th><?php esc_html_e( 'Include', 'wp-clean-admin' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $tables as $table ) : ?>
                                <tr>
                                    <td><?php echo esc_html( $table ); ?></td>
                                    <td>
                                        <input type="checkbox" 
                                               name="wpca_tables_to_optimize[]" 
                                               value="<?php echo esc_attr( $table ); ?>" 
                                               <?php checked( in_array( $table, $selected_tables ) ); ?> 
                                        />
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div class="wpca-settings-submit">
                        <?php submit_button( esc_html__( 'Save Table Selection', 'wp-clean-admin' ) ); ?>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }

    /**
     * Render cleanup tab.
     */
    public function render_cleanup_tab() {
        // Get current settings
        $cleanup_settings = array(
            'revisions'              => get_option( 'wpca_cleanup_revisions', false ),
            'revision_days'          => get_option( 'wpca_revision_days', 30 ),
            'auto_drafts'            => get_option( 'wpca_cleanup_auto_drafts', false ),
            'trashed_posts'          => get_option( 'wpca_cleanup_trashed_posts', false ),
            'spam_comments'          => get_option( 'wpca_cleanup_spam_comments', false ),
            'trashed_comments'       => get_option( 'wpca_cleanup_trashed_comments', false ),
            'pingbacks_trackbacks'   => get_option( 'wpca_cleanup_pingbacks_trackbacks', false ),
            'orphaned_postmeta'      => get_option( 'wpca_cleanup_orphaned_postmeta', false ),
            'orphaned_commentmeta'   => get_option( 'wpca_cleanup_orphaned_commentmeta', false ),
            'orphaned_relationships' => get_option( 'wpca_cleanup_orphaned_relationships', false ),
            'orphaned_usermeta'      => get_option( 'wpca_cleanup_orphaned_usermeta', false ),
            'expired_transients'     => get_option( 'wpca_cleanup_expired_transients', false ),
            'all_transients'         => get_option( 'wpca_cleanup_all_transients', false ),
            'oembed_caches'          => get_option( 'wpca_cleanup_oembed_caches', false ),
        );
        
        ?>
        <div class="wpca-settings-section">
            <h2><?php esc_html_e( 'Database Cleanup', 'wp-clean-admin' ); ?></h2>
            <p><?php esc_html_e( 'Clean up unnecessary data from your database to reduce size and improve performance.', 'wp-clean-admin' ); ?></p>
            <p class="description"><?php esc_html_e( 'Warning: Cleanup operations are irreversible. Please backup your database before proceeding.', 'wp-clean-admin' ); ?></p>
            
            <div class="wpca-cleanup-actions">
                <button type="button" id="wpca-cleanup-database" class="button button-primary button-large">
                    <span class="dashicons dashicons-trash"></span>
                    <?php esc_html_e( 'Run Selected Cleanup Tasks', 'wp-clean-admin' ); ?>
                </button>
                <div id="wpca-cleanup-results" class="wpca-results"></div>
            </div>
            
            <div class="wpca-cleanup-options">
                <h3><?php esc_html_e( 'Cleanup Options', 'wp-clean-admin' ); ?></h3>
                <p><?php esc_html_e( 'Select which types of data to clean up:', 'wp-clean-admin' ); ?></p>
                
                <form method="post" action="options.php">
                    <?php settings_fields( 'wpca-database-cleanup' ); ?>
                    
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row">
                                <label for="wpca_cleanup_revisions"><?php esc_html_e( 'Post Revisions', 'wp-clean-admin' ); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" id="wpca_cleanup_revisions" name="wpca_cleanup_revisions" value="1" <?php checked( $cleanup_settings['revisions'] ); ?> />
                                <label for="wpca_cleanup_revisions"><?php esc_html_e( 'Remove old post revisions', 'wp-clean-admin' ); ?></label>
                                <div class="wpca-cleanup-options-indent">
                                    <label for="wpca_revision_days"><?php esc_html_e( 'Keep revisions from the last', 'wp-clean-admin' ); ?> </label>
                                    <input type="number" id="wpca_revision_days" name="wpca_revision_days" min="1" value="<?php echo esc_attr( $cleanup_settings['revision_days'] ); ?>" />
                                    <label for="wpca_revision_days"><?php esc_html_e( 'days', 'wp-clean-admin' ); ?></label>
                                </div>
                            </td>
                        </tr>
                        
                        <tr valign="top">
                            <th scope="row">
                                <label for="wpca_cleanup_auto_drafts"><?php esc_html_e( 'Auto Drafts', 'wp-clean-admin' ); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" id="wpca_cleanup_auto_drafts" name="wpca_cleanup_auto_drafts" value="1" <?php checked( $cleanup_settings['auto_drafts'] ); ?> />
                                <label for="wpca_cleanup_auto_drafts"><?php esc_html_e( 'Remove auto-saved drafts', 'wp-clean-admin' ); ?></label>
                            </td>
                        </tr>
                        
                        <tr valign="top">
                            <th scope="row">
                                <label for="wpca_cleanup_trashed_posts"><?php esc_html_e( 'Trashed Posts', 'wp-clean-admin' ); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" id="wpca_cleanup_trashed_posts" name="wpca_cleanup_trashed_posts" value="1" <?php checked( $cleanup_settings['trashed_posts'] ); ?> />
                                <label for="wpca_cleanup_trashed_posts"><?php esc_html_e( 'Remove posts in trash', 'wp-clean-admin' ); ?></label>
                            </td>
                        </tr>
                        
                        <tr valign="top">
                            <th scope="row">
                                <label for="wpca_cleanup_spam_comments"><?php esc_html_e( 'Spam Comments', 'wp-clean-admin' ); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" id="wpca_cleanup_spam_comments" name="wpca_cleanup_spam_comments" value="1" <?php checked( $cleanup_settings['spam_comments'] ); ?> />
                                <label for="wpca_cleanup_spam_comments"><?php esc_html_e( 'Remove spam comments', 'wp-clean-admin' ); ?></label>
                            </td>
                        </tr>
                        
                        <tr valign="top">
                            <th scope="row">
                                <label for="wpca_cleanup_trashed_comments"><?php esc_html_e( 'Trashed Comments', 'wp-clean-admin' ); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" id="wpca_cleanup_trashed_comments" name="wpca_cleanup_trashed_comments" value="1" <?php checked( $cleanup_settings['trashed_comments'] ); ?> />
                                <label for="wpca_cleanup_trashed_comments"><?php esc_html_e( 'Remove comments in trash', 'wp-clean-admin' ); ?></label>
                            </td>
                        </tr>
                        
                        <tr valign="top">
                            <th scope="row">
                                <label for="wpca_cleanup_pingbacks_trackbacks"><?php esc_html_e( 'Pingbacks/Trackbacks', 'wp-clean-admin' ); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" id="wpca_cleanup_pingbacks_trackbacks" name="wpca_cleanup_pingbacks_trackbacks" value="1" <?php checked( $cleanup_settings['pingbacks_trackbacks'] ); ?> />
                                <label for="wpca_cleanup_pingbacks_trackbacks"><?php esc_html_e( 'Remove pingbacks and trackbacks', 'wp-clean-admin' ); ?></label>
                            </td>
                        </tr>
                        
                        <tr valign="top">
                            <th scope="row">
                                <label for="wpca_cleanup_orphaned_postmeta"><?php esc_html_e( 'Orphaned Post Meta', 'wp-clean-admin' ); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" id="wpca_cleanup_orphaned_postmeta" name="wpca_cleanup_orphaned_postmeta" value="1" <?php checked( $cleanup_settings['orphaned_postmeta'] ); ?> />
                                <label for="wpca_cleanup_orphaned_postmeta"><?php esc_html_e( 'Remove post meta with no associated post', 'wp-clean-admin' ); ?></label>
                            </td>
                        </tr>
                        
                        <tr valign="top">
                            <th scope="row">
                                <label for="wpca_cleanup_orphaned_commentmeta"><?php esc_html_e( 'Orphaned Comment Meta', 'wp-clean-admin' ); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" id="wpca_cleanup_orphaned_commentmeta" name="wpca_cleanup_orphaned_commentmeta" value="1" <?php checked( $cleanup_settings['orphaned_commentmeta'] ); ?> />
                                <label for="wpca_cleanup_orphaned_commentmeta"><?php esc_html_e( 'Remove comment meta with no associated comment', 'wp-clean-admin' ); ?></label>
                            </td>
                        </tr>
                        
                        <tr valign="top">
                            <th scope="row">
                                <label for="wpca_cleanup_orphaned_relationships"><?php esc_html_e( 'Orphaned Relationships', 'wp-clean-admin' ); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" id="wpca_cleanup_orphaned_relationships" name="wpca_cleanup_orphaned_relationships" value="1" <?php checked( $cleanup_settings['orphaned_relationships'] ); ?> />
                                <label for="wpca_cleanup_orphaned_relationships"><?php esc_html_e( 'Remove term relationships with no associated post', 'wp-clean-admin' ); ?></label>
                            </td>
                        </tr>
                        
                        <tr valign="top">
                            <th scope="row">
                                <label for="wpca_cleanup_orphaned_usermeta"><?php esc_html_e( 'Orphaned User Meta', 'wp-clean-admin' ); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" id="wpca_cleanup_orphaned_usermeta" name="wpca_cleanup_orphaned_usermeta" value="1" <?php checked( $cleanup_settings['orphaned_usermeta'] ); ?> />
                                <label for="wpca_cleanup_orphaned_usermeta"><?php esc_html_e( 'Remove user meta with no associated user', 'wp-clean-admin' ); ?></label>
                            </td>
                        </tr>
                        
                        <tr valign="top">
                            <th scope="row">
                                <label for="wpca_cleanup_expired_transients"><?php esc_html_e( 'Expired Transients', 'wp-clean-admin' ); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" id="wpca_cleanup_expired_transients" name="wpca_cleanup_expired_transients" value="1" <?php checked( $cleanup_settings['expired_transients'] ); ?> />
                                <label for="wpca_cleanup_expired_transients"><?php esc_html_e( 'Remove expired transients', 'wp-clean-admin' ); ?></label>
                            </td>
                        </tr>
                        
                        <tr valign="top">
                            <th scope="row">
                                <label for="wpca_cleanup_all_transients"><?php esc_html_e( 'All Transients', 'wp-clean-admin' ); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" id="wpca_cleanup_all_transients" name="wpca_cleanup_all_transients" value="1" <?php checked( $cleanup_settings['all_transients'] ); ?> />
                                <label for="wpca_cleanup_all_transients"><?php esc_html_e( 'Remove all transients (use with caution)', 'wp-clean-admin' ); ?></label>
                            </td>
                        </tr>
                        
                        <tr valign="top">
                            <th scope="row">
                                <label for="wpca_cleanup_oembed_caches"><?php esc_html_e( 'oEmbed Caches', 'wp-clean-admin' ); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" id="wpca_cleanup_oembed_caches" name="wpca_cleanup_oembed_caches" value="1" <?php checked( $cleanup_settings['oembed_caches'] ); ?> />
                                <label for="wpca_cleanup_oembed_caches"><?php esc_html_e( 'Remove oEmbed cache entries', 'wp-clean-admin' ); ?></label>
                            </td>
                        </tr>
                    </table>
                    
                    <div class="wpca-settings-submit">
                        <?php submit_button( esc_html__( 'Save Cleanup Options', 'wp-clean-admin' ) ); ?>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }

    /**
     * Render schedule tab.
     */
    public function render_schedule_tab() {
        // Get current settings
        $auto_optimize_enabled = get_option( 'wpca_auto_optimize_tables', false );
        $optimize_interval = get_option( 'wpca_optimize_interval', 'weekly' );
        $auto_cleanup_enabled = get_option( 'wpca_enable_auto_cleanup', false );
        $cleanup_interval = get_option( 'wpca_cleanup_interval', 'weekly' );
        
        // Get available intervals
        $intervals = array(
            'daily'   => esc_html__( 'Daily', 'wp-clean-admin' ),
            'weekly'  => esc_html__( 'Weekly', 'wp-clean-admin' ),
            'monthly' => esc_html__( 'Monthly', 'wp-clean-admin' ),
        );
        
        ?>
        <div class="wpca-settings-section">
            <h2><?php esc_html_e( 'Scheduled Tasks', 'wp-clean-admin' ); ?></h2>
            <p><?php esc_html_e( 'Configure automatic database maintenance tasks to run on a schedule.', 'wp-clean-admin' ); ?></p>
            
            <div class="wpca-schedule-options">
                <h3><?php esc_html_e( 'Automatic Table Optimization', 'wp-clean-admin' ); ?></h3>
                
                <form method="post" action="options.php">
                    <?php settings_fields( 'wpca-database-optimization' ); ?>
                    
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row">
                                <label for="wpca_auto_optimize_tables"><?php esc_html_e( 'Enable Automatic Optimization', 'wp-clean-admin' ); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" id="wpca_auto_optimize_tables" name="wpca_auto_optimize_tables" value="1" <?php checked( $auto_optimize_enabled ); ?> />
                                <label for="wpca_auto_optimize_tables"><?php esc_html_e( 'Run table optimization automatically', 'wp-clean-admin' ); ?></label>
                            </td>
                        </tr>
                        
                        <tr valign="top">
                            <th scope="row">
                                <label for="wpca_optimize_interval"><?php esc_html_e( 'Optimization Interval', 'wp-clean-admin' ); ?></label>
                            </th>
                            <td>
                                <select id="wpca_optimize_interval" name="wpca_optimize_interval" <?php disabled( ! $auto_optimize_enabled ); ?>>
                                    <?php foreach ( $intervals as $value => $label ) : ?>
                                        <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $optimize_interval, $value ); ?>><?php echo esc_html( $label ); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                    </table>
                    
                    <h3><?php esc_html_e( 'Automatic Database Cleanup', 'wp-clean-admin' ); ?></h3>
                    
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row">
                                <label for="wpca_enable_auto_cleanup"><?php esc_html_e( 'Enable Automatic Cleanup', 'wp-clean-admin' ); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" id="wpca_enable_auto_cleanup" name="wpca_enable_auto_cleanup" value="1" <?php checked( $auto_cleanup_enabled ); ?> />
                                <label for="wpca_enable_auto_cleanup"><?php esc_html_e( 'Run database cleanup automatically', 'wp-clean-admin' ); ?></label>
                                <p class="description"><?php esc_html_e( 'Cleanup tasks will be performed based on your selected options.', 'wp-clean-admin' ); ?></p>
                            </td>
                        </tr>
                        
                        <tr valign="top">
                            <th scope="row">
                                <label for="wpca_cleanup_interval"><?php esc_html_e( 'Cleanup Interval', 'wp-clean-admin' ); ?></label>
                            </th>
                            <td>
                                <select id="wpca_cleanup_interval" name="wpca_cleanup_interval" <?php disabled( ! $auto_cleanup_enabled ); ?>>
                                    <?php foreach ( $intervals as $value => $label ) : ?>
                                        <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $cleanup_interval, $value ); ?>><?php echo esc_html( $label ); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                    </table>
                    
                    <div class="wpca-settings-submit">
                        <?php submit_button( esc_html__( 'Save Schedule Settings', 'wp-clean-admin' ) ); ?>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }

    /**
     * Render info tab.
     */
    public function render_info_tab() {
        // Get database information
        $db_info = array();
        $cleanup_stats = array();
        
        if ( $this->database && method_exists( $this->database, 'get_database_info' ) ) {
            $db_info = $this->database->get_database_info();
        }
        
        if ( $this->database && method_exists( $this->database, 'get_cleanup_stats' ) ) {
            $cleanup_stats = $this->database->get_cleanup_stats();
        }
        
        ?>
        <div class="wpca-settings-section">
            <h2><?php esc_html_e( 'Database Information', 'wp-clean-admin' ); ?></h2>
            <p><?php esc_html_e( 'Current database statistics and information.', 'wp-clean-admin' ); ?></p>
            
            <div class="wpca-database-info">
                <h3><?php esc_html_e( 'General Information', 'wp-clean-admin' ); ?></h3>
                
                <table class="widefat fixed">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Property', 'wp-clean-admin' ); ?></th>
                            <th><?php esc_html_e( 'Value', 'wp-clean-admin' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php esc_html_e( 'Database Version', 'wp-clean-admin' ); ?></td>
                            <td><?php echo isset( $db_info['version'] ) ? esc_html( $db_info['version'] ) : esc_html__( 'Not available', 'wp-clean-admin' ); ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e( 'Table Count', 'wp-clean-admin' ); ?></td>
                            <td><?php echo isset( $db_info['table_count'] ) ? esc_html( $db_info['table_count'] ) : esc_html__( 'Not available', 'wp-clean-admin' ); ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e( 'Database Size', 'wp-clean-admin' ); ?></td>
                            <td><?php echo isset( $db_info['size'] ) ? esc_html( $db_info['size'] ) : esc_html__( 'Not available', 'wp-clean-admin' ); ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e( 'Database Charset', 'wp-clean-admin' ); ?></td>
                            <td><?php echo isset( $db_info['charset'] ) ? esc_html( $db_info['charset'] ) : esc_html__( 'Not available', 'wp-clean-admin' ); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="wpca-cleanup-stats">
                <h3><?php esc_html_e( 'Cleanup Statistics', 'wp-clean-admin' ); ?></h3>
                <p><?php esc_html_e( 'Current counts of cleanup items in your database:', 'wp-clean-admin' ); ?></p>
                
                <table class="widefat fixed">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Item', 'wp-clean-admin' ); ?></th>
                            <th><?php esc_html_e( 'Count', 'wp-clean-admin' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php esc_html_e( 'Post Revisions', 'wp-clean-admin' ); ?></td>
                            <td><?php echo isset( $cleanup_stats['revision_posts'] ) ? esc_html( $cleanup_stats['revision_posts'] ) : esc_html__( 'Not available', 'wp-clean-admin' ); ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e( 'Auto Drafts', 'wp-clean-admin' ); ?></td>
                            <td><?php echo isset( $cleanup_stats['auto_drafts'] ) ? esc_html( $cleanup_stats['auto_drafts'] ) : esc_html__( 'Not available', 'wp-clean-admin' ); ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e( 'Trashed Posts', 'wp-clean-admin' ); ?></td>
                            <td><?php echo isset( $cleanup_stats['trashed_posts'] ) ? esc_html( $cleanup_stats['trashed_posts'] ) : esc_html__( 'Not available', 'wp-clean-admin' ); ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e( 'Spam Comments', 'wp-clean-admin' ); ?></td>
                            <td><?php echo isset( $cleanup_stats['spam_comments'] ) ? esc_html( $cleanup_stats['spam_comments'] ) : esc_html__( 'Not available', 'wp-clean-admin' ); ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e( 'Trashed Comments', 'wp-clean-admin' ); ?></td>
                            <td><?php echo isset( $cleanup_stats['trashed_comments'] ) ? esc_html( $cleanup_stats['trashed_comments'] ) : esc_html__( 'Not available', 'wp-clean-admin' ); ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e( 'Pingbacks/Trackbacks', 'wp-clean-admin' ); ?></td>
                            <td><?php echo isset( $cleanup_stats['pingbacks_trackbacks'] ) ? esc_html( $cleanup_stats['pingbacks_trackbacks'] ) : esc_html__( 'Not available', 'wp-clean-admin' ); ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e( 'Transients', 'wp-clean-admin' ); ?></td>
                            <td><?php echo isset( $cleanup_stats['transients'] ) ? esc_html( $cleanup_stats['transients'] ) : esc_html__( 'Not available', 'wp-clean-admin' ); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    /**
     * Get the singleton instance of the class.
     *
     * @return WPCA_Database_Settings The instance of the class.
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self;
        }
        
        return self::$instance;
    }
}

/**
 * Initialize the database settings class.
 */
function wpca_init_database_settings() {
    return WPCA_Database_Settings::get_instance();
}
