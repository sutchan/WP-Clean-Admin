<?php
/**
 * WPCleanAdmin Elementor Integration Class
 *
 * @package WPCleanAdmin
 * @version 1.7.15
 * @author Sut
 * @author URI: https://github.com/sutchan
 * @since 1.7.15
 */
namespace WPCleanAdmin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WPCleanAdmin\Elementor' ) ) {

    /**
     * Elementor integration class
     *
     * Provides cleanup and optimization for Elementor page builder
     */
    class Elementor {
        
        /**
         * Singleton instance
         *
         * @var Elementor
         */
        private static ?Elementor $instance = null;
        
        /**
         * Elementor detected status
         *
         * @var bool
         */
        private bool $is_elementor_active = false;
        
        /**
         * Get singleton instance
         *
         * @return Elementor
         */
        public static function getInstance(): Elementor {
            if ( self::$instance === null ) {
                self::$instance = new self();
            }
            return self::$instance;
        }
        
        /**
         * Constructor
         */
        private function __construct() {
            $this->check_elementor_status();
            $this->register_hooks();
        }
        
        /**
         * Check if Elementor is active
         *
         * @return bool
         */
        private function check_elementor_status(): bool {
            $this->is_elementor_active = defined( 'ELEMENTOR_VERSION' );
            return $this->is_elementor_active;
        }
        
        /**
         * Register hooks
         *
         * @return void
         */
        private function register_hooks(): void {
            if ( $this->is_elementor_active ) {
                add_action( 'wpca_elementor_cleanup', array( $this, 'cleanup_elementor_cache' ) );
                add_action( 'wpca_elementor_optimize', array( $this, 'optimize_elementor_assets' ) );
            }
        }
        
        /**
         * Check if Elementor is active
         *
         * @return bool
         */
        public function is_elementor_active(): bool {
            return $this->is_elementor_active;
        }
        
        /**
         * Get Elementor version
         *
         * @return string|null
         */
        public function get_elementor_version(): ?string {
            if ( ! $this->is_elementor_active ) {
                return null;
            }
            return defined('ELEMENTOR_VERSION') ? ELEMENTOR_VERSION : null;
        }
        
        /**
         * Cleanup Elementor cache
         *
         * @return array
         */
        public function cleanup_elementor_cache(): array {
            if ( ! $this->is_elementor_active ) {
                return array(
                    'success' => false,
                    'message' => __( 'Elementor is not active.', WPCA_TEXT_DOMAIN ),
                    'count' => 0,
                );
            }
            
            $cleared = 0;
            
            if ( class_exists( '\Elementor\Plugin' ) ) {
                if ( class_exists( 'Elementor\Plugin' ) ) {
                    $elementor = \Elementor\Plugin::instance();
                } else {
                    $elementor = null;
                }
                
                if ( method_exists( $elementor->files_manager, 'clear_cache' ) ) {
                    $elementor->files_manager->clear_cache();
                    $cleared++;
                }
                
                if ( method_exists( $elementor->posts_css_manager, 'clear_cache' ) ) {
                    $elementor->posts_css_manager->clear_cache();
                    $cleared++;
                }
            }
            
            global $wpdb;
            
            $transients = $wpdb->get_col( "
                SELECT option_name 
                FROM {$wpdb->options} 
                WHERE option_name LIKE '_transient_elementor_%'
                OR option_name LIKE '_transient_timeout_elementor_%'
            " );
            
            foreach ( $transients as $transient ) {
                \delete_transient( str_replace( '_transient_', '', $transient ) );
                $cleared++;
            }
            
            return array(
                'success' => true,
                'message' => sprintf( 
                    __( 'Elementor cache cleared. %d items removed.', WPCA_TEXT_DOMAIN ),
                    $cleared
                ),
                'count' => $cleared,
            );
        }
        
        /**
         * Optimize Elementor assets
         *
         * @return array
         */
        public function optimize_elementor_assets(): array {
            if ( ! $this->is_elementor_active ) {
                return array(
                    'success' => false,
                    'message' => __( 'Elementor is not active.', WPCA_TEXT_DOMAIN ),
                );
            }
            
            $optimizations = array();
            
            if ( ! $this->disable_google_fonts() ) {
                $optimizations[] = __( 'Failed to disable Google Fonts.', WPCA_TEXT_DOMAIN );
            } else {
                $optimizations[] = __( 'Google Fonts loading disabled.', WPCA_TEXT_DOMAIN );
            }
            
            if ( ! $this->optimize_icons_loading() ) {
                $optimizations[] = __( 'Failed to optimize icons loading.', WPCA_TEXT_DOMAIN );
            } else {
                $optimizations[] = __( 'Icons loading optimized.', WPCA_TEXT_DOMAIN );
            }
            
            return array(
                'success' => true,
                'message' => __( 'Elementor optimizations applied.', WPCA_TEXT_DOMAIN ),
                'optimizations' => $optimizations,
            );
        }
        
        /**
         * Disable Elementor Google Fonts
         *
         * @return bool
         */
        public function disable_google_fonts(): bool {
            if ( ! $this->is_elementor_active ) {
                return false;
            }
            
            add_filter( 'elementor/fonts/print_google_fonts', '__return_false' );
            
            return true;
        }
        
        /**
         * Enable Elementor Google Fonts
         *
         * @return bool
         */
        public function enable_google_fonts(): bool {
            if ( ! $this->is_elementor_active ) {
                return false;
            }
            
            remove_filter( 'elementor/fonts/print_google_fonts', '__return_false' );
            
            return true;
        }
        
        /**
         * Optimize icons loading
         *
         * @return bool
         */
        public function optimize_icons_loading(): bool {
            if ( ! $this->is_elementor_active ) {
                return false;
            }
            
            add_action( 'elementor/frontend/after_enqueue_styles', array( $this, 'dequeue_elementor_icons' ) );
            
            return true;
        }
        
        /**
         * Dequeue Elementor icons
         *
         * @return void
         */
        public function dequeue_elementor_icons(): void {
            wp_dequeue_style( 'elementor-icons' );
        }
        
        /**
         * Disable Elementor responsive breakpoints
         *
         * @param array $breakpoints Breakpoints to keep
         * @return array
         */
        public function get_active_breakpoints( array $breakpoints = array() ): array {
            if ( ! $this->is_elementor_active ) {
                return $breakpoints;
            }
            
            return apply_filters( 'wpca_elementor_active_breakpoints', $breakpoints );
        }
        
        /**
         * Get Elementor documents count
         *
         * @return array
         */
        public function get_elementor_documents_count(): array {
            global $wpdb;
            
            $count = $wpdb->get_var( "
                SELECT COUNT(*) 
                FROM {$wpdb->postmeta} 
                WHERE meta_key = '_elementor_edit_mode'
                AND meta_value = 'builder'
           " );
            
            return array(
                'total' => (int) $count,
                'post_types' => $this->get_documents_by_post_type(),
            );
        }
        
        /**
         * Get Elementor documents by post type
         *
         * @return array
         */
        private function get_documents_by_post_type(): array {
            global $wpdb;
            
            $results = $wpdb->get_results( "
                SELECT p.post_type, COUNT(*) as count
                FROM {$wpdb->postmeta} m
                INNER JOIN {$wpdb->posts} p ON m.post_id = p.ID
                WHERE m.meta_key = '_elementor_edit_mode'
                AND m.meta_value = 'builder'
                AND p.post_status = 'publish'
                GROUP BY p.post_type
            ", ARRAY_A );
            
            $by_type = array();
            foreach ( $results as $row ) {
                $by_type[ $row['post_type'] ] = (int) $row['count'];
            }
            
            return $by_type;
        }
        
        /**
         * Get Elementor settings
         *
         * @return array
         */
        public function get_elementor_settings(): array {
            if ( ! $this->is_elementor_active ) {
                return array();
            }
            
            $settings = array(
                'is_active' => $this->is_elementor_active,
                'version' => $this->get_elementor_version(),
                'documents_count' => $this->get_elementor_documents_count(),
            );
            
            return apply_filters( 'wpca_elementor_settings', $settings );
        }
        
        /**
         * Check if post uses Elementor
         *
         * @param int $post_id Post ID
         * @return bool
         */
        public function is_using_elementor( int $post_id ): bool {
            $post_meta = get_post_meta( $post_id, '_elementor_edit_mode', true );
            return 'builder' === $post_meta;
        }
        
        /**
         * Get Elementor data for a post
         *
         * @param int $post_id Post ID
         * @return array|null
         */
        public function get_post_elementor_data( int $post_id ) {
            if ( ! $this->is_using_elementor( $post_id ) ) {
                return null;
            }
            
            $data = get_post_meta( $post_id, '_elementor_data', true );
            
            if ( empty( $data ) ) {
                return null;
            }
            
            return json_decode( $data, true );
        }
        
        /**
         * Cleanup Elementor data from post
         *
         * @param int $post_id Post ID
         * @return array
         */
        public function cleanup_post_elementor_data( int $post_id ): array {
            if ( ! $this->is_using_elementor( $post_id ) ) {
                return array(
                    'success' => false,
                    'message' => __( 'Post does not use Elementor.', WPCA_TEXT_DOMAIN ),
                );
            }
            
            \delete_post_meta( $post_id, '_elementor_edit_mode' );
            delete_post_meta( $post_id, '_elementor_data' );
            delete_post_meta( $post_id, '_elementor_css' );
            
            return array(
                'success' => true,
                'message' => __( 'Elementor data removed from post.', WPCA_TEXT_DOMAIN ),
                'post_id' => $post_id,
            );
        }
        
        /**
         * Export Elementor data
         *
         * @param int $post_id Post ID
         * @return array
         */
        public function export_elementor_data( int $post_id ): array {
            $data = $this->get_post_elementor_data( $post_id );
            
            if ( empty( $data ) ) {
                return array(
                    'success' => false,
                    'message' => __( 'No Elementor data found.', WPCA_TEXT_DOMAIN ),
                );
            }
            
            return array(
                'success' => true,
                'post_id' => $post_id,
                'data' => $data,
                'exported_at' => current_time( 'mysql' ),
            );
        }
        
        /**
         * Import Elementor data
         *
         * @param int $post_id Post ID
         * @param array $data Elementor data
         * @return array
         */
        public function import_elementor_data( int $post_id, array $data ): array {
            update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
            update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $data ) ) );
            
            return array(
                'success' => true,
                'message' => __( 'Elementor data imported.', WPCA_TEXT_DOMAIN ),
                'post_id' => $post_id,
            );
        }
        
        /**
         * Get Elementor widgets list
         *
         * @return array
         */
        public function get_widgets_list(): array {
            if ( ! $this->is_elementor_active ) {
                return array();
            }
            
            if ( ! class_exists( '\Elementor\Plugin' ) ) {
                return array();
            }
            
            $elementor = \Elementor\Plugin::instance();
            $widgets = $elementor->widgets_manager->get_widget_types();
            
            $widget_list = array();
            foreach ( $widgets as $widget ) {
                $widget_list[] = array(
                    'name' => $widget->get_name(),
                    'title' => $widget->get_title(),
                    'icon' => $widget->get_icon(),
                );
            }
            
            return $widget_list;
        }
        
        /**
         * Disable specific Elementor widgets
         *
         * @param array $widgets Widget names to disable
         * @return bool
         */
        public function disable_widgets( array $widgets ): bool {
            if ( ! $this->is_elementor_active ) {
                return false;
            }
            
            foreach ( $widgets as $widget ) {
                add_filter( "elementor/widget/{$widget}/should_register", '__return_false' );
            }
            
            return true;
        }
        
        /**
         * Get Elementor CSS methods count
         *
         * @param int $post_id Post ID
         * @return int
         */
        public function get_css_methods_count( int $post_id ): int {
            $data = $this->get_post_elementor_data( $post_id );
            
            if ( empty( $data ) ) {
                return 0;
            }
            
            return $this->count_css_methods_recursive( $data );
        }
        
        /**
         * Recursively count CSS methods in Elementor data
         *
         * @param array $elements Elements array
         * @return int
         */
        private function count_css_methods_recursive( array $elements ): int {
            $count = 0;
            
            foreach ( $elements as $element ) {
                if ( isset( $element['settings'] ) ) {
                    if ( isset( $element['settings']['_css_wrapper'] ) ) {
                        $count++;
                    }
                }
                
                if ( isset( $element['elements'] ) && is_array( $element['elements'] ) ) {
                    $count += $this->count_css_methods_recursive( $element['elements'] );
                }
            }
            
            return $count;
        }
        
        /**
         * Get Elementor template library count
         *
         * @return array
         */
        public function get_template_library_count(): array {
            global $wpdb;
            
            $templates = $wpdb->get_var( "
                SELECT COUNT(*) 
                FROM {$wpdb->posts} 
                WHERE post_type = 'elementor_library'
                AND post_status = 'publish'
            " );
            
            return array(
                'templates' => (int) $templates,
            );
        }
        
        /**
         * Disable Elementor container nesting
         *
         * @return bool
         */
        public function disable_container_nesting() {
            if ( ! $this->is_elementor_active ) {
                return false;
            }
            
            add_action( 'elementor/element/before_parse_controls', array( $this, 'remove_container_support' ) );
            
            return true;
        }
        
        /**
         * Remove container support from element
         *
         * @return void
         */
        public function remove_container_support() {
            // Remove container controls if needed
        }
        
        /**
         * Get Elementor performance recommendations
         *
         * @return array
         */
        public function get_performance_recommendations() {
            $recommendations = array();
            
            if ( ! $this->is_elementor_active ) {
                return $recommendations;
            }
            
            // Check for unused templates
            $library_count = $this->get_template_library_count();
            if ( $library_count['templates'] > 50 ) {
                $recommendations[] = array(
                    'type' => 'warning',
                    'message' => __( 'You have many Elementor templates. Consider deleting unused ones.', WPCA_TEXT_DOMAIN ),
                    'action' => 'elementor_library',
                );
            }
            
            // Check for complex documents
            $documents = $this->get_elementor_documents_count();
            if ( isset( $documents['total'] ) && $documents['total'] > 100 ) {
                $recommendations[] = array(
                    'type' => 'info',
                    'message' => __( 'Consider enabling Elementor cache to improve performance.', WPCA_TEXT_DOMAIN ),
                    'action' => 'cache',
                );
            }
            
            return $recommendations;
        }
        
        /**
         * Clear Elementor fonts cache
         *
         * @return array
         */
        public function clear_fonts_cache() {
            if ( ! $this->is_elementor_active ) {
                return array(
                    'success' => false,
                    'message' => __( 'Elementor is not active.', WPCA_TEXT_DOMAIN ),
                );
            }
            
            global $wpdb;
            
            $deleted = $wpdb->delete(
                $wpdb->postmeta,
                array( 'meta_key' => '_elementor_fonts_cache' ),
                array( '%s' )
            );
            
            return array(
                'success' => true,
                'message' => __( 'Elementor fonts cache cleared.', WPCA_TEXT_DOMAIN ),
                'deleted' => $deleted,
            );
        }
        
        /**
         * Get Elementor assets info
         *
         * @return array
         */
        public function get_assets_info(): array {
            global $wpdb;
            
            $assets = $wpdb->get_results( "
                SELECT post_id, meta_value
                FROM {$wpdb->postmeta}
                WHERE meta_key = '_elementor_css'
            ", ARRAY_A );
            
            $total_size = 0;
            foreach ( $assets as $asset ) {
                if ( isset( $asset['meta_value'] ) ) {
                    $css_data = json_decode( $asset['meta_value'], true );
                    if ( isset( $css_data['fonts'] ) ) {
                        $total_size += count( $css_data['fonts'] );
                    }
                }
            }
            
            return array(
                'cached_posts' => count( $assets ),
                'fonts_cached' => $total_size,
            );
        }
    }

}
