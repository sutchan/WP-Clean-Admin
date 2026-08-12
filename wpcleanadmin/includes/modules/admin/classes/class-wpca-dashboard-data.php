<?php
/**
 * WPCleanAdmin Dashboard Data
 *
 * 承载仪表盘统计与系统信息的数据获取逻辑，从 Dashboard 主类抽取。
 *
 * @package WPCleanAdmin
 * @version  1.8.4
 * @author Sut
 * @author URI: https://github.com/Tanox
 * @since 1.7.15
 */

namespace WPCleanAdmin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 仪表盘数据类
 */
class Dashboard_Data {

    /**
     * Get dashboard statistics
     *
     * @return array Dashboard statistics
     * @global $wpdb WordPress database object
     */
    public function get_dashboard_stats(): array {
        global $wpdb;

        $stats = array();

        // Get database size
        $result = $wpdb->get_row(
            $wpdb->prepare( 'SELECT SUM(data_length + index_length) AS size FROM information_schema.TABLES WHERE table_schema = %s', $wpdb->dbname ),
            ARRAY_A
        );
        $stats['database_size'] = ( function_exists( '\size_format' ) ? \size_format( $result['size'], 2 ) : $result['size'] );

        // Get transients count
        $stats['transients'] = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '%transient%'" );

        // Get orphaned postmeta count
        $stats['orphaned_postmeta'] = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->postmeta} LEFT JOIN {$wpdb->posts} ON {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID WHERE {$wpdb->posts}.ID IS NULL" );

        // Get orphaned termmeta count
        $stats['orphaned_termmeta'] = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->termmeta} LEFT JOIN {$wpdb->terms} ON {$wpdb->termmeta}.term_id = {$wpdb->terms}.term_id WHERE {$wpdb->terms}.term_id IS NULL" );

        return $stats;
    }

    /**
     * Get system information
     *
     * @return array System information
     * @global $wp_version
     * @global $wpdb WordPress database object
     */
    public function get_system_info(): array {
        global $wp_version, $wpdb;

        $info = array();

        // WordPress information
        $info['wordpress'] = array(
            'version'      => $wp_version,
            'language'     => ( function_exists( '\get_locale' ) ? \get_locale() : 'en_US' ),
            'multisite'    => ( function_exists( '\is_multisite' ) && \is_multisite() ) ? \__( 'Yes', WPCA_TEXT_DOMAIN ) : \__( 'No', WPCA_TEXT_DOMAIN ),
            'debug_mode'   => defined( 'WP_DEBUG' ) && WP_DEBUG ? \__( 'Yes', WPCA_TEXT_DOMAIN ) : \__( 'No', WPCA_TEXT_DOMAIN ),
        );

        // Server information
        $info['server'] = array(
            'php_version'     => phpversion(),
            'mysql_version'   => $wpdb->db_version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'],
            'memory_limit'    => ini_get( 'memory_limit' ),
        );

        // Plugin information
        $info['plugin'] = array(
            'version' => WPCA_VERSION,
            'active'  => ( function_exists( '\is_plugin_active' ) && function_exists( '\plugin_basename' ) && \is_plugin_active( \plugin_basename( WPCA_PLUGIN_DIR . 'wp-clean-admin.php' ) ) ) ? \__( 'Yes', WPCA_TEXT_DOMAIN ) : \__( 'Yes', WPCA_TEXT_DOMAIN ),
        );

        return $info;
    }
}
