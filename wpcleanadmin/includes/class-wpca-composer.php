<?php
/**
 * WPCleanAdmin Composer Dependency Management Class
 *
 * @package WPCleanAdmin
 * @version 1.7.15
 * @author Sut
 * @author URI: https://github.com/sutchan
 * @since 1.7.15
 *
 * Note: This module is designed for version 2.0.0 when Composer support will be enabled.
 * Current version maintains zero dependencies as per project requirements.
 */

namespace WPCleanAdmin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WPCleanAdmin\Composer' ) ) {

    /**
     * Composer dependency management class
     *
     * Provides dependency management using Composer for version 2.0.0+
     *
     * @deprecated For v2.0.0 use only
     */
    class Composer {
        
        /**
         * Singleton instance
         *
         * @var Composer
         */
        /** @deprecated 仅用于 v2.0.0+，当前版本请勿使用 */
        private static $instance = null;
        
        /**
         * Composer installed status
         *
         * @var bool
         */
        private bool $composer_installed = false;
        
        /**
         * Package information
         *
         * @var array
         */
        private array $package_info = array();
        
        /**
         * Required packages
         *
         * @var array
         */
        private array $required_packages = array();
        
        /**
         * Optional packages
         *
         * @var array
         */
        private array $optional_packages = array();
        
        /**
         * Get singleton instance
         *
         * @return Composer
         */
        public static function getInstance() {
            if ( ! isset( self::$instance ) ) {
                trigger_error(
                    'WPCleanAdmin\Composer 已废弃，当前版本请勿使用。此功能仅用于 v2.0.0+。',
                    E_USER_DEPRECATED
                );
                self::$instance = new self();
            }
            // 已废弃：WPCleanAdmin\Composer 仅用于 v2.0.0+，当前版本请勿使用
            return self::$instance;
        }
        
        /**
         * Constructor
         */
        private function __construct() {
            $this->composer_installed = $this->check_composer_installed();
            $this->initialize_packages();
        }
        
        /**
         * Check if Composer is available
         *
         * @return bool
         */
        private function check_composer_installed(): bool {
            return defined( 'COMPOSER_VERSION' ) || class_exists( 'Composer\Autoload\ClassLoader' );
        }
        
        /**
         * Initialize package definitions
         *
         * @return void
         */
        private function initialize_packages() {
            $this->required_packages = array(
                'php' => array(
                    'version' => '>=7.4',
                    'description' => 'PHP 7.4 or higher',
                    'required' => true,
                ),
            );
            
            $this->optional_packages = array(
                'symfony/yaml' => array(
                    'version' => '^6.0',
                    'description' => 'YAML parsing and manipulation',
                    'required' => false,
                    'purpose' => 'Configuration file parsing',
                ),
                'league/container' => array(
                    'version' => '^4.0',
                    'description' => 'Dependency injection container',
                    'required' => false,
                    'purpose' => 'Service container for extensions',
                ),
            );
            
            $this->package_info = array(
                'name' => 'sutchan/wpcleanadmin',
                'description' => 'WordPress admin cleanup and optimization plugin',
                'type' => 'wordpress-plugin',
                'license' => 'GPL-3.0+',
                'authors' => array(
                    array(
                        'name' => 'Sut',
                        'email' => 'sut@example.com',
                        'homepage' => 'https://github.com/sutchan',
                    ),
                ),
                'require' => $this->required_packages,
                'suggest' => $this->optional_packages,
                'autoload' => array(
                    'psr-4' => array(
                        'WPCleanAdmin\\' => 'includes/',
                    ),
                ),
                'minimum-stability' => 'stable',
                'prefer-stable' => true,
            );
        }
        
        /**
         * Check if Composer mode is enabled
         *
         * @return bool
         */
        public function is_composer_mode_enabled() {
            return apply_filters( 'wpca_composer_mode_enabled', false );
        }
        
        /**
         * Check if Composer is available
         *
         * @return bool
         */
        public function is_composer_available() {
            return $this->composer_installed;
        }
        
        /**
         * Get package information
         *
         * @return array
         */
        public function get_package_info(): array {
            return $this->package_info;
        }
        
        /**
         * Get required packages
         *
         * @return array
         */
        public function get_required_packages() {
            return $this->required_packages;
        }
        
        /**
         * Get optional packages
         *
         * @return array
         */
        public function get_optional_packages() {
            return $this->optional_packages;
        }
        
        /**
         * Get installed packages
         *
         * @return array
         */
        public function get_installed_packages() {
            if ( ! $this->composer_installed ) {
                return array();
            }
            
            $packages = array();
            
            // Check for installed packages
            if ( class_exists( 'Composer\InstalledVersions' ) ) {
                try {
                    $installed = array();
                    if (method_exists('Composer\InstalledVersions', 'getInstalledPackages')) {
                        $installed = \Composer\InstalledVersions::getInstalledPackages();
                    } elseif (method_exists('Composer\InstalledVersions', 'getAllInstalledPackages')) {
                        // Composer\InstalledVersions 不存在 getAllInstalledPackages 方法，统一使用 getInstalledPackages
                        $installed = \Composer\InstalledVersions::getInstalledPackages();
                    }
                    foreach ( $installed as $package ) {
                        $packages[ $package ] = \Composer\InstalledVersions::getVersion( $package );
                    }
                } catch ( \Exception $e ) {
                    // Package not available
                }
            }
            
            return $packages;
        }
        
        /**
         * Check if package is installed
         *
         * @param string $package Package name
         * @return bool
         */
        public function is_package_installed( string $package ): bool {
            if ( ! $this->composer_installed ) {
                return false;
            }
            
            if ( class_exists( 'Composer\InstalledVersions' ) ) {
                try {
                    return \Composer\InstalledVersions::isInstalled( $package );
                } catch ( \Exception $e ) {
                    return false;
                }
            }
            
            return false;
        }
        
        /**
         * Get package version
         *
         * @param string $package Package name
         * @return string|null
         */
        public function get_package_version( $package ) {
            if ( ! $this->is_package_installed( $package ) ) {
                return null;
            }
            
            if ( class_exists( 'Composer\InstalledVersions' ) ) {
                try {
                    return \Composer\InstalledVersions::getVersion( $package );
                } catch ( \Exception $e ) {
                    return null;
                }
            }
            
            return null;
        }
        
        /**
         * Check PHP version requirement
         *
         * @return bool
         */
        public function check_php_requirement(): bool {
            $required_php = '7.4.0';
            return version_compare( PHP_VERSION, $required_php, '>=' );
        }
        
        /**
         * Get Composer configuration
         *
         * @return array
         */
        public function get_composer_config() {
            $config = array(
                'vendor_dir' => defined( 'WPCA_COMPOSER_VENDOR_DIR' ) ? WPCA_COMPOSER_VENDOR_DIR : WP_CONTENT_DIR . '/vendor',
                'autoload_file' => defined( 'WPCA_COMPOSER_AUTOLOADER' ) ? WPCA_COMPOSER_AUTOLOADER : null,
                'bin_dir' => defined( 'WPCA_COMPOSER_BIN_DIR' ) ? WPCA_COMPOSER_BIN_DIR : null,
            );
            
            return apply_filters( 'wpca_composer_config', $config );
        }
        
        /**
         * Load Composer autoloader
         *
         * @return bool
         */
        public function load_autoloader(): bool {
            $config = $this->get_composer_config();
            
            if ( ! empty( $config['autoload_file'] ) && file_exists( $config['autoload_file'] ) ) {
                require_once $config['autoload_file'];
                return true;
            }
            
            return false;
        }
        
        /**
         * Generate composer.json content
         *
         * @return string
         */
        public function generate_composer_json() {
            $json = array(
                'name' => $this->package_info['name'],
                'description' => $this->package_info['description'],
                'type' => $this->package_info['type'],
                'license' => $this->package_info['license'],
                'authors' => $this->package_info['authors'],
                'require' => array(),
                'suggest' => array(),
                'autoload' => $this->package_info['autoload'],
                'minimum-stability' => $this->package_info['minimum-stability'],
                'prefer-stable' => $this->package_info['prefer-stable'],
                'config' => array(
                    'optimize-autoloader' => true,
                    'sort-packages' => true,
                ),
            );
            
            // Add PHP requirement
            foreach ( $this->required_packages as $package => $info ) {
                if ( $package === 'php' ) {
                    $json['require'][ $package ] = $info['version'];
                }
            }
            
            // Add optional packages with version constraints
            foreach ( $this->optional_packages as $package => $info ) {
                $json['suggest'][ $package ] = $info['description'];
            }
            
            return json_encode( $json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
        }
        
        /**
         * Install dependencies
         *
         * @param array $packages Packages to install
         * @return array Result
         */
        public function install_dependencies( $packages = array() ) {
            if ( ! $this->is_composer_mode_enabled() ) {
                return array(
                    'success' => false,
                    'message' => __( 'Composer mode is not enabled. This feature is for v2.0.0+.', WPCA_TEXT_DOMAIN ),
                );
            }
            
            if ( ! current_user_can( 'manage_options' ) ) {
                return array(
                    'success' => false,
                    'message' => __( 'Permission denied.', WPCA_TEXT_DOMAIN ),
                );
            }
            
            return array(
                'success' => true,
                'message' => __( 'Dependencies installation simulated.', WPCA_TEXT_DOMAIN ),
                'packages' => $packages,
            );
        }
        
        /**
         * Remove dependencies
         *
         * @param array $packages Packages to remove
         * @return array Result
         */
        public function remove_dependencies( array $packages = array() ): array {
            if ( ! $this->is_composer_mode_enabled() ) {
                return array(
                    'success' => false,
                    'message' => __( 'Composer mode is not enabled. This feature is for v2.0.0+.', WPCA_TEXT_DOMAIN ),
                );
            }
            
            return array(
                'success' => true,
                'message' => __( 'Dependencies removal simulated.', WPCA_TEXT_DOMAIN ),
                'packages' => $packages,
            );
        }
        
        /**
         * Update dependencies
         *
         * @param array $packages Packages to update
         * @return array Result
         */
        public function update_dependencies( $packages = array() ) {
            if ( ! $this->is_composer_mode_enabled() ) {
                return array(
                    'success' => false,
                    'message' => __( 'Composer mode is not enabled. This feature is for v2.0.0+.', WPCA_TEXT_DOMAIN ),
                );
            }
            
            return array(
                'success' => true,
                'message' => __( 'Dependencies update simulated.', WPCA_TEXT_DOMAIN ),
                'packages' => $packages,
            );
        }
        
        /**
         * Get dependency status
         *
         * @return array
         */
        public function get_dependency_status() {
            $status = array(
                'composer_mode_enabled' => $this->is_composer_mode_enabled(),
                'composer_available' => $this->composer_installed,
                'php_requirement_met' => $this->check_php_requirement(),
                'installed_packages' => $this->get_installed_packages(),
                'required_packages' => $this->required_packages,
                'optional_packages' => $this->optional_packages,
            );
            
            return apply_filters( 'wpca_dependency_status', $status );
        }
        
        /**
         * Validate composer.json
         *
         * @param string $json JSON string
         * @return bool
         */
        public function validate_composer_json( string $json ): bool {
            $data = json_decode( $json, true );
            
            if ( json_last_error() !== JSON_ERROR_NONE ) {
                return false;
            }
            
            // Check required fields
            $required_fields = array( 'name', 'type', 'require' );
            foreach ( $required_fields as $field ) {
                if ( ! isset( $data[ $field ] ) ) {
                    return false;
                }
            }
            
            return true;
        }
        
        /**
         * Generate lock file content
         *
         * @return string
         */
        public function generate_lock_content() {
            $lock = array(
                '_readme' => 'This file is generated by WPCleanAdmin. Do not edit directly.',
                'content-hash' => md5( $this->generate_composer_json() ),
                'packages' => array(),
                'packages-dev' => array(),
                'aliases' => array(),
                'minimum-stability' => 'stable',
                'stability-flags' => array(),
                'prefer-stable' => true,
                'prefer-lowest' => false,
            );
            
            return json_encode( $lock, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
        }
        
        /**
         * Get autoloader optimization level
         *
         * @return int
         */
        public function get_optimization_level() {
            return (int) apply_filters( 'wpca_composer_optimization_level', 1 );
        }
        
        /**
         * Dump autoloader
         *
         * @return array Result
         */
        public function dump_autoloader() {
            if ( ! $this->is_composer_mode_enabled() ) {
                return array(
                    'success' => false,
                    'message' => __( 'Composer mode is not enabled.', WPCA_TEXT_DOMAIN ),
                );
            }
            
            return array(
                'success' => true,
                'message' => __( 'Autoloader dump simulated.', WPCA_TEXT_DOMAIN ),
                'optimization' => $this->get_optimization_level(),
            );
        }
        
        /**
         * Get compatibility report
         *
         * @return array
         */
        public function get_compatibility_report() {
            $report = array(
                'php_version' => PHP_VERSION,
                'php_compatible' => $this->check_php_requirement(),
                'composer_version' => defined( 'COMPOSER_VERSION' ) ? COMPOSER_VERSION : null,
                'composer_compatible' => $this->composer_installed,
                'extensions' => $this->get_required_extensions(),
                'recommendations' => array(),
            );
            
            // Add recommendations
            if ( ! $report['php_compatible' ] ) {
                $report['recommendations'][] = array(
                    'type' => 'warning',
                    'message' => __( 'PHP version 7.4 or higher is required for full Composer support.', WPCA_TEXT_DOMAIN ),
                );
            }
            
            if ( ! $report['composer_compatible' ] ) {
                $report['recommendations'][] = array(
                    'type' => 'info',
                    'message' => __( 'Composer is not detected. Run "composer install" to set up dependencies.', WPCA_TEXT_DOMAIN ),
                );
            }
            
            return $report;
        }
        
        /**
         * Get required PHP extensions
         *
         * @return array
         */
        private function get_required_extensions(): array {
            return array(
                'json' => extension_loaded( 'json' ),
                'mbstring' => extension_loaded( 'mbstring' ),
                'phar' => extension_loaded( 'phar' ),
            );
        }
    }

}
