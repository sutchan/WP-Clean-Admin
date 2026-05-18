<?php
/**
 * WPCleanAdmin Diagnostics Class
 *
 * @package WPCleanAdmin
 * @version 1.8.0
 * @author Sut
 * @author URI: https://github.com/sutchan
 * @since 1.8.0
 */

namespace WPCleanAdmin;

require_once __DIR__ . '/wpca-wordpress-stubs.php';

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Diagnostics {

    private static $instance = null;

    private $checks = array();

    public static function getInstance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init();
    }

    public function init(): void {
        $this->register_checks();
    }

    private function register_checks(): void {
        $this->checks = array(
            'php_version' => array(
                'name' => __( 'PHP Version', WPCA_TEXT_DOMAIN ),
                'callback' => array( $this, 'check_php_version' ),
                'category' => 'server',
                'severity' => 'critical'
            ),
            'wp_version' => array(
                'name' => __( 'WordPress Version', WPCA_TEXT_DOMAIN ),
                'callback' => array( $this, 'check_wp_version' ),
                'category' => 'core',
                'severity' => 'critical'
            ),
            'mysql_version' => array(
                'name' => __( 'MySQL Version', WPCA_TEXT_DOMAIN ),
                'callback' => array( $this, 'check_mysql_version' ),
                'category' => 'server',
                'severity' => 'critical'
            ),
            'memory_limit' => array(
                'name' => __( 'Memory Limit', WPCA_TEXT_DOMAIN ),
                'callback' => array( $this, 'check_memory_limit' ),
                'category' => 'server',
                'severity' => 'high'
            ),
            'wp_debug' => array(
                'name' => __( 'Debug Mode', WPCA_TEXT_DOMAIN ),
                'callback' => array( $this, 'check_wp_debug' ),
                'category' => 'core',
                'severity' => 'medium'
            ),
            'plugin_conflicts' => array(
                'name' => __( 'Plugin Conflicts', WPCA_TEXT_DOMAIN ),
                'callback' => array( $this, 'check_plugin_conflicts' ),
                'category' => 'plugins',
                'severity' => 'high'
            ),
            'theme_conflicts' => array(
                'name' => __( 'Theme Conflicts', WPCA_TEXT_DOMAIN ),
                'callback' => array( $this, 'check_theme_conflicts' ),
                'category' => 'themes',
                'severity' => 'high'
            ),
            'file_permissions' => array(
                'name' => __( 'File Permissions', WPCA_TEXT_DOMAIN ),
                'callback' => array( $this, 'check_file_permissions' ),
                'category' => 'security',
                'severity' => 'critical'
            ),
            'database_tables' => array(
                'name' => __( 'Database Tables', WPCA_TEXT_DOMAIN ),
                'callback' => array( $this, 'check_database_tables' ),
                'category' => 'database',
                'severity' => 'high'
            ),
            'ssl_status' => array(
                'name' => __( 'SSL Status', WPCA_TEXT_DOMAIN ),
                'callback' => array( $this, 'check_ssl_status' ),
                'category' => 'security',
                'severity' => 'critical'
            ),
            'cache_status' => array(
                'name' => __( 'Cache Status', WPCA_TEXT_DOMAIN ),
                'callback' => array( $this, 'check_cache_status' ),
                'category' => 'performance',
                'severity' => 'medium'
            ),
            'rest_api' => array(
                'name' => __( 'REST API', WPCA_TEXT_DOMAIN ),
                'callback' => array( $this, 'check_rest_api' ),
                'category' => 'core',
                'severity' => 'medium'
            )
        );
    }

    public function run_all_checks(): array {
        $results = array(
            'status' => 'success',
            'data' => array(),
            'summary' => array(
                'total' => count( $this->checks ),
                'passed' => 0,
                'warning' => 0,
                'error' => 0
            )
        );

        foreach ( $this->checks as $check_id => $check ) {
            try {
                $result = call_user_func( $check['callback'] );
                $result['id'] = $check_id;
                $result['name'] = $check['name'];
                $result['category'] = $check['category'];
                $result['severity'] = $check['severity'];
                
                $results['data'][] = $result;
                
                if ( $result['status'] === 'pass' ) {
                    $results['summary']['passed']++;
                } elseif ( $result['status'] === 'warning' ) {
                    $results['summary']['warning']++;
                } else {
                    $results['summary']['error']++;
                }
            } catch ( \Exception $e ) {
                $results['data'][] = array(
                    'id' => $check_id,
                    'name' => $check['name'],
                    'category' => $check['category'],
                    'severity' => $check['severity'],
                    'status' => 'error',
                    'message' => __( 'Check failed to execute', WPCA_TEXT_DOMAIN ),
                    'details' => $e->getMessage()
                );
                $results['summary']['error']++;
            }
        }

        if ( $results['summary']['error'] > 0 ) {
            $results['status'] = 'error';
        } elseif ( $results['summary']['warning'] > 0 ) {
            $results['status'] = 'warning';
        }

        return $results;
    }

    public function run_check( string $check_id ) {
        if ( ! isset( $this->checks[$check_id] ) ) {
            return array(
                'status' => 'error',
                'message' => __( 'Check not found', WPCA_TEXT_DOMAIN )
            );
        }

        $check = $this->checks[$check_id];
        try {
            $result = call_user_func( $check['callback'] );
            $result['id'] = $check_id;
            $result['name'] = $check['name'];
            $result['category'] = $check['category'];
            $result['severity'] = $check['severity'];
            return $result;
        } catch ( \Exception $e ) {
            return array(
                'id' => $check_id,
                'name' => $check['name'],
                'category' => $check['category'],
                'severity' => $check['severity'],
                'status' => 'error',
                'message' => __( 'Check failed to execute', WPCA_TEXT_DOMAIN ),
                'details' => $e->getMessage()
            );
        }
    }

    public function check_php_version(): array {
        $current_version = phpversion();
        $min_version = '7.4';
        
        if ( version_compare( $current_version, $min_version, '>=' ) ) {
            return array(
                'status' => 'pass',
                'message' => sprintf( __( 'PHP version %s is supported', WPCA_TEXT_DOMAIN ), $current_version ),
                'details' => array(
                    'current' => $current_version,
                    'minimum' => $min_version,
                    'recommended' => '8.0+'
                )
            );
        }
        
        return array(
            'status' => 'error',
            'message' => sprintf( __( 'PHP version %s is outdated', WPCA_TEXT_DOMAIN ), $current_version ),
            'details' => array(
                'current' => $current_version,
                'minimum' => $min_version,
                'recommended' => '8.0+'
            ),
            'action' => __( 'Please upgrade PHP to version 7.4 or higher', WPCA_TEXT_DOMAIN )
        );
    }

    public function check_wp_version(): array {
        global $wp_version;
        $min_version = '5.8';
        
        if ( version_compare( $wp_version, $min_version, '>=' ) ) {
            return array(
                'status' => 'pass',
                'message' => sprintf( __( 'WordPress version %s is supported', WPCA_TEXT_DOMAIN ), $wp_version ),
                'details' => array(
                    'current' => $wp_version,
                    'minimum' => $min_version
                )
            );
        }
        
        return array(
            'status' => 'error',
            'message' => sprintf( __( 'WordPress version %s is outdated', WPCA_TEXT_DOMAIN ), $wp_version ),
            'details' => array(
                'current' => $wp_version,
                'minimum' => $min_version
            ),
            'action' => __( 'Please upgrade WordPress to version 5.8 or higher', WPCA_TEXT_DOMAIN )
        );
    }

    public function check_mysql_version(): array {
        global $wpdb;
        $current_version = $wpdb->db_version();
        $min_version = '5.6';
        
        if ( version_compare( $current_version, $min_version, '>=' ) ) {
            return array(
                'status' => 'pass',
                'message' => sprintf( __( 'MySQL version %s is supported', WPCA_TEXT_DOMAIN ), $current_version ),
                'details' => array(
                    'current' => $current_version,
                    'minimum' => $min_version,
                    'recommended' => '8.0+'
                )
            );
        }
        
        return array(
            'status' => 'error',
            'message' => sprintf( __( 'MySQL version %s is outdated', WPCA_TEXT_DOMAIN ), $current_version ),
            'details' => array(
                'current' => $current_version,
                'minimum' => $min_version,
                'recommended' => '8.0+'
            ),
            'action' => __( 'Please upgrade MySQL to version 5.6 or higher', WPCA_TEXT_DOMAIN )
        );
    }

    public function check_memory_limit(): array {
        $memory_limit = ini_get( 'memory_limit' );
        $min_limit = '256M';
        
        $current_bytes = $this->convert_to_bytes( $memory_limit );
        $min_bytes = $this->convert_to_bytes( $min_limit );
        
        if ( $current_bytes >= $min_bytes ) {
            return array(
                'status' => 'pass',
                'message' => sprintf( __( 'Memory limit %s is sufficient', WPCA_TEXT_DOMAIN ), $memory_limit ),
                'details' => array(
                    'current' => $memory_limit,
                    'minimum' => $min_limit,
                    'recommended' => '512M'
                )
            );
        }
        
        return array(
            'status' => 'warning',
            'message' => sprintf( __( 'Memory limit %s may be insufficient', WPCA_TEXT_DOMAIN ), $memory_limit ),
            'details' => array(
                'current' => $memory_limit,
                'minimum' => $min_limit,
                'recommended' => '512M'
            ),
            'action' => __( 'Consider increasing PHP memory limit in php.ini or .htaccess', WPCA_TEXT_DOMAIN )
        );
    }

    public function check_wp_debug(): array {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            return array(
                'status' => 'warning',
                'message' => __( 'WP_DEBUG is enabled', WPCA_TEXT_DOMAIN ),
                'details' => array(
                    'enabled' => true
                ),
                'action' => __( 'Disable WP_DEBUG on production sites for better performance and security', WPCA_TEXT_DOMAIN )
            );
        }
        
        return array(
            'status' => 'pass',
            'message' => __( 'WP_DEBUG is disabled', WPCA_TEXT_DOMAIN ),
            'details' => array(
                'enabled' => false
            )
        );
    }

    public function check_plugin_conflicts(): array {
        $conflicting_plugins = array();
        
        if ( function_exists( 'get_plugins' ) ) {
            $plugins = get_plugins();
            $active_plugins = get_option( 'active_plugins', array() );
            
            $known_conflicts = array(
                'wp-super-cache/wp-super-cache.php',
                'w3-total-cache/w3-total-cache.php',
                'wp-rocket/wp-rocket.php'
            );
            
            foreach ( $active_plugins as $plugin ) {
                if ( in_array( $plugin, $known_conflicts ) ) {
                    $conflicting_plugins[] = $plugins[$plugin]['Name'] ?? $plugin;
                }
            }
        }
        
        if ( empty( $conflicting_plugins ) ) {
            return array(
                'status' => 'pass',
                'message' => __( 'No known plugin conflicts detected', WPCA_TEXT_DOMAIN ),
                'details' => array(
                    'checked_plugins' => count( $active_plugins ?? array() ),
                    'conflicts_found' => 0
                )
            );
        }
        
        return array(
            'status' => 'warning',
            'message' => sprintf( __( '%d potential plugin conflicts detected', WPCA_TEXT_DOMAIN ), count( $conflicting_plugins ) ),
            'details' => array(
                'conflicting_plugins' => $conflicting_plugins
            ),
            'action' => __( 'Some plugins may conflict with performance optimizations. Test compatibility before production use.', WPCA_TEXT_DOMAIN )
        );
    }

    public function check_theme_conflicts(): array {
        $current_theme = wp_get_theme();
        $parent_theme = $current_theme->parent();
        
        $potential_conflicts = array();
        
        if ( $parent_theme ) {
            if ( version_compare( $parent_theme->get( 'Version' ), '1.0', '<' ) ) {
                $potential_conflicts[] = sprintf( __( 'Parent theme %s may be outdated', WPCA_TEXT_DOMAIN ), $parent_theme->get( 'Name' ) );
            }
        }
        
        if ( version_compare( $current_theme->get( 'Version' ), '1.0', '<' ) ) {
            $potential_conflicts[] = sprintf( __( 'Theme %s may be outdated', WPCA_TEXT_DOMAIN ), $current_theme->get( 'Name' ) );
        }
        
        if ( empty( $potential_conflicts ) ) {
            return array(
                'status' => 'pass',
                'message' => sprintf( __( 'Theme %s is compatible', WPCA_TEXT_DOMAIN ), $current_theme->get( 'Name' ) ),
                'details' => array(
                    'theme' => $current_theme->get( 'Name' ),
                    'version' => $current_theme->get( 'Version' ),
                    'parent' => $parent_theme ? $parent_theme->get( 'Name' ) : 'None'
                )
            );
        }
        
        return array(
            'status' => 'warning',
            'message' => __( 'Potential theme conflicts detected', WPCA_TEXT_DOMAIN ),
            'details' => array(
                'theme' => $current_theme->get( 'Name' ),
                'version' => $current_theme->get( 'Version' ),
                'issues' => $potential_conflicts
            ),
            'action' => __( 'Consider updating your theme to the latest version', WPCA_TEXT_DOMAIN )
        );
    }

    public function check_file_permissions(): array {
        $wp_content_dir = WP_CONTENT_DIR;
        $uploads_dir = WP_CONTENT_DIR . '/uploads';
        
        $issues = array();
        
        if ( ! is_writable( $wp_content_dir ) ) {
            $issues[] = sprintf( __( 'wp-content directory is not writable: %s', WPCA_TEXT_DOMAIN ), $wp_content_dir );
        }
        
        if ( ! is_writable( $uploads_dir ) ) {
            $issues[] = sprintf( __( 'uploads directory is not writable: %s', WPCA_TEXT_DOMAIN ), $uploads_dir );
        }
        
        if ( empty( $issues ) ) {
            return array(
                'status' => 'pass',
                'message' => __( 'File permissions are correct', WPCA_TEXT_DOMAIN ),
                'details' => array(
                    'wp_content_writable' => true,
                    'uploads_writable' => true
                )
            );
        }
        
        return array(
            'status' => 'error',
            'message' => sprintf( __( '%d file permission issues detected', WPCA_TEXT_DOMAIN ), count( $issues ) ),
            'details' => array(
                'issues' => $issues
            ),
            'action' => __( 'Fix file permissions to allow WordPress to write to necessary directories', WPCA_TEXT_DOMAIN )
        );
    }

    public function check_database_tables(): array {
        global $wpdb;
        
        $required_tables = array(
            $wpdb->posts,
            $wpdb->postmeta,
            $wpdb->comments,
            $wpdb->commentmeta,
            $wpdb->terms,
            $wpdb->term_taxonomy,
            $wpdb->term_relationships,
            $wpdb->users,
            $wpdb->usermeta,
            $wpdb->options
        );
        
        $missing_tables = array();
        
        foreach ( $required_tables as $table ) {
            if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table ) ) !== $table ) {
                $missing_tables[] = $table;
            }
        }
        
        if ( empty( $missing_tables ) ) {
            return array(
                'status' => 'pass',
                'message' => __( 'All required database tables exist', WPCA_TEXT_DOMAIN ),
                'details' => array(
                    'checked_tables' => count( $required_tables ),
                    'missing_tables' => 0
                )
            );
        }
        
        return array(
            'status' => 'error',
            'message' => sprintf( __( '%d database tables are missing', WPCA_TEXT_DOMAIN ), count( $missing_tables ) ),
            'details' => array(
                'missing_tables' => $missing_tables
            ),
            'action' => __( 'Restore missing database tables from backup or run WordPress repair', WPCA_TEXT_DOMAIN )
        );
    }

    public function check_ssl_status(): array {
        if ( function_exists( 'is_ssl' ) && is_ssl() ) {
            return array(
                'status' => 'pass',
                'message' => __( 'SSL is enabled', WPCA_TEXT_DOMAIN ),
                'details' => array(
                    'ssl_enabled' => true,
                    'site_url' => get_site_url()
                )
            );
        }
        
        return array(
            'status' => 'warning',
            'message' => __( 'SSL is not enabled', WPCA_TEXT_DOMAIN ),
            'details' => array(
                'ssl_enabled' => false,
                'site_url' => get_site_url()
            ),
            'action' => __( 'Enable SSL/TLS for better security', WPCA_TEXT_DOMAIN )
        );
    }

    public function check_cache_status(): array {
        $cache_plugins = array(
            'wp-super-cache/wp-super-cache.php',
            'w3-total-cache/w3-total-cache.php',
            'wp-rocket/wp-rocket.php',
            'wp-fastest-cache/wpFastestCache.php'
        );
        
        $active_plugins = get_option( 'active_plugins', array() );
        $cache_active = false;
        $cache_plugin = '';
        
        foreach ( $cache_plugins as $plugin ) {
            if ( in_array( $plugin, $active_plugins ) ) {
                $cache_active = true;
                $cache_plugin = $plugin;
                break;
            }
        }
        
        if ( $cache_active ) {
            return array(
                'status' => 'pass',
                'message' => sprintf( __( 'Caching plugin %s is active', WPCA_TEXT_DOMAIN ), $cache_plugin ),
                'details' => array(
                    'cache_active' => true,
                    'plugin' => $cache_plugin
                )
            );
        }
        
        return array(
            'status' => 'warning',
            'message' => __( 'No caching plugin detected', WPCA_TEXT_DOMAIN ),
            'details' => array(
                'cache_active' => false
            ),
            'action' => __( 'Consider installing a caching plugin for better performance', WPCA_TEXT_DOMAIN )
        );
    }

    public function check_rest_api(): array {
        $rest_api_enabled = true;
        
        if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
            $rest_api_enabled = false;
        }
        
        if ( function_exists( 'get_option' ) ) {
            $disabled = get_option( 'wpca_disable_rest_api', false );
            if ( $disabled ) {
                $rest_api_enabled = false;
            }
        }
        
        if ( $rest_api_enabled ) {
            return array(
                'status' => 'pass',
                'message' => __( 'REST API is enabled', WPCA_TEXT_DOMAIN ),
                'details' => array(
                    'enabled' => true
                )
            );
        }
        
        return array(
            'status' => 'warning',
            'message' => __( 'REST API is disabled', WPCA_TEXT_DOMAIN ),
            'details' => array(
                'enabled' => false
            ),
            'action' => __( 'Some plugins may require REST API to be enabled', WPCA_TEXT_DOMAIN )
        );
    }

    private function convert_to_bytes( string $value ): int {
        $value = trim( $value );
        $last = strtolower( $value[strlen( $value ) - 1] );
        
        switch ( $last ) {
            case 'g':
                return (int) $value * 1024 * 1024 * 1024;
            case 'm':
                return (int) $value * 1024 * 1024;
            case 'k':
                return (int) $value * 1024;
            default:
                return (int) $value;
        }
    }

    public function get_checks(): array {
        return $this->checks;
    }

    public function get_categories(): array {
        $categories = array(
            'core' => __( 'Core', WPCA_TEXT_DOMAIN ),
            'server' => __( 'Server', WPCA_TEXT_DOMAIN ),
            'database' => __( 'Database', WPCA_TEXT_DOMAIN ),
            'security' => __( 'Security', WPCA_TEXT_DOMAIN ),
            'performance' => __( 'Performance', WPCA_TEXT_DOMAIN ),
            'plugins' => __( 'Plugins', WPCA_TEXT_DOMAIN ),
            'themes' => __( 'Themes', WPCA_TEXT_DOMAIN )
        );
        
        return $categories;
    }
}