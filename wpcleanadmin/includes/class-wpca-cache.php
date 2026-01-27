<?php
/**
 * WPCleanAdmin Cache Manager
 *
 * @package WPCleanAdmin
 * @version 1.8.0
 * @author Sut
 * @since 1.8.0
 */

namespace WPCleanAdmin;

/**
 * Cache manager class
 */
class Cache {
    /**
     * Singleton instance
     *
     * @var Cache
     */
    private static $instance = null;
    
    /**
     * Memory cache
     *
     * @var array
     */
    private $memory_cache = array();
    
    /**
     * Cache enabled status
     *
     * @var bool
     */
    private $cache_enabled = true;
    
    /**
     * Cache expiration time (in seconds)
     *
     * @var int
     */
    private $cache_expiration = 3600;
    
    /**
     * Get singleton instance
     *
     * @return Cache
     */
    public static function getInstance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init();
    }
    
    /**
     * Initialize cache manager
     */
    public function init() {
        // Load cache settings
        $this->load_cache_settings();
        
        // Clean expired cache on init
        $this->clean_expired_cache();
    }
    
    /**
     * Load cache settings
     */
    private function load_cache_settings() {
        $settings = ( function_exists( 'get_option' ) ? \get_option( 'wpca_settings', array() ) : array() );
        
        if ( isset( $settings['performance'] ) ) {
            if ( isset( $settings['performance']['cache_enabled'] ) ) {
                $this->cache_enabled = (bool) $settings['performance']['cache_enabled'];
            }
            
            if ( isset( $settings['performance']['cache_expiration'] ) ) {
                $this->cache_expiration = (int) $settings['performance']['cache_expiration'];
            }
        }
        
        // Set default expiration if not set
        if ( $this->cache_expiration <= 0 ) {
            $this->cache_expiration = 3600;
        }
    }
    
    /**
     * Get cache
     *
     * @param string $key Cache key
     * @param mixed $default Default value if cache not found
     * @param string $type Cache type (memory, database, file)
     * @return mixed
     */
    public function get( string $key, $default = null, string $type = 'memory' ) {
        if ( ! $this->cache_enabled ) {
            return $default;
        }
        
        switch ( $type ) {
            case 'memory':
                return $this->get_memory_cache( $key, $default );
            case 'database':
                return $this->get_database_cache( $key, $default );
            case 'file':
                return $this->get_file_cache( $key, $default );
            default:
                return $this->get_memory_cache( $key, $default );
        }
    }
    
    /**
     * Set cache
     *
     * @param string $key Cache key
     * @param mixed $value Cache value
     * @param int $expiration Expiration time in seconds
     * @param string $type Cache type (memory, database, file)
     * @return bool
     */
    public function set( string $key, $value, int $expiration = 0, string $type = 'memory' ): bool {
        if ( ! $this->cache_enabled ) {
            return false;
        }
        
        if ( $expiration <= 0 ) {
            $expiration = $this->cache_expiration;
        }
        
        switch ( $type ) {
            case 'memory':
                return $this->set_memory_cache( $key, $value, $expiration );
            case 'database':
                return $this->set_database_cache( $key, $value, $expiration );
            case 'file':
                return $this->set_file_cache( $key, $value, $expiration );
            default:
                return $this->set_memory_cache( $key, $value, $expiration );
        }
    }
    
    /**
     * Delete cache
     *
     * @param string $key Cache key
     * @param string $type Cache type (memory, database, file)
     * @return bool
     */
    public function delete( string $key, string $type = 'memory' ): bool {
        switch ( $type ) {
            case 'memory':
                return $this->delete_memory_cache( $key );
            case 'database':
                return $this->delete_database_cache( $key );
            case 'file':
                return $this->delete_file_cache( $key );
            default:
                return $this->delete_memory_cache( $key );
        }
    }
    
    /**
     * Clear all cache
     *
     * @param string $type Cache type (memory, database, file, all)
     * @return bool
     */
    public function clear( string $type = 'all' ): bool {
        switch ( $type ) {
            case 'memory':
                return $this->clear_memory_cache();
            case 'database':
                return $this->clear_database_cache();
            case 'file':
                return $this->clear_file_cache();
            case 'all':
                return $this->clear_memory_cache() && 
                       $this->clear_database_cache() && 
                       $this->clear_file_cache();
            default:
                return false;
        }
    }
    
    /**
     * Get memory cache
     *
     * @param string $key Cache key
     * @param mixed $default Default value if cache not found
     * @return mixed
     */
    private function get_memory_cache( string $key, $default = null ) {
        if ( isset( $this->memory_cache[ $key ] ) ) {
            $cache = $this->memory_cache[ $key ];
            
            // Check if cache has expired
            if ( isset( $cache['expiration'] ) && $cache['expiration'] < \time() ) {
                unset( $this->memory_cache[ $key ] );
                return $default;
            }
            
            return $cache['value'];
        }
        
        return $default;
    }
    
    /**
     * Set memory cache
     *
     * @param string $key Cache key
     * @param mixed $value Cache value
     * @param int $expiration Expiration time in seconds
     * @return bool
     */
    private function set_memory_cache( string $key, $value, int $expiration ): bool {
        $this->memory_cache[ $key ] = array(
            'value' => $value,
            'expiration' => \time() + $expiration
        );
        
        return true;
    }
    
    /**
     * Delete memory cache
     *
     * @param string $key Cache key
     * @return bool
     */
    private function delete_memory_cache( string $key ): bool {
        if ( isset( $this->memory_cache[ $key ] ) ) {
            unset( $this->memory_cache[ $key ] );
            return true;
        }
        
        return false;
    }
    
    /**
     * Clear memory cache
     *
     * @return bool
     */
    private function clear_memory_cache(): bool {
        $this->memory_cache = array();
        return true;
    }
    
    /**
     * Get database cache
     *
     * @param string $key Cache key
     * @param mixed $default Default value if cache not found
     * @return mixed
     */
    private function get_database_cache( string $key, $default = null ) {
        if ( ! function_exists( 'get_option' ) ) {
            return $default;
        }
        
        $cache_key = 'wpca_cache_' . $key;
        $cache = \get_option( $cache_key, false );
        
        if ( $cache !== false ) {
            // Check if cache has expired
            if ( isset( $cache['expiration'] ) && $cache['expiration'] < \time() ) {
                \delete_option( $cache_key );
                return $default;
            }
            
            return $cache['value'];
        }
        
        return $default;
    }
    
    /**
     * Set database cache
     *
     * @param string $key Cache key
     * @param mixed $value Cache value
     * @param int $expiration Expiration time in seconds
     * @return bool
     */
    private function set_database_cache( string $key, $value, int $expiration ): bool {
        if ( ! function_exists( 'update_option' ) ) {
            return false;
        }
        
        $cache_key = 'wpca_cache_' . $key;
        $cache = array(
            'value' => $value,
            'expiration' => \time() + $expiration,
            'created_at' => \time()
        );
        
        return \update_option( $cache_key, $cache, false );
    }
    
    /**
     * Delete database cache
     *
     * @param string $key Cache key
     * @return bool
     */
    private function delete_database_cache( string $key ): bool {
        if ( ! function_exists( 'delete_option' ) ) {
            return false;
        }
        
        $cache_key = 'wpca_cache_' . $key;
        return \delete_option( $cache_key );
    }
    
    /**
     * Clear database cache
     *
     * @return bool
     */
    private function clear_database_cache(): bool {
        if ( ! function_exists( 'get_options' ) && ! function_exists( 'delete_option' ) ) {
            return false;
        }
        
        // Get all cache options
        global $wpdb;
        if ( isset( $wpdb ) ) {
            $cache_keys = $wpdb->get_col( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE 'wpca_cache_%'" );
            
            foreach ( $cache_keys as $cache_key ) {
                \delete_option( $cache_key );
            }
        }
        
        return true;
    }
    
    /**
     * Get file cache
     *
     * @param string $key Cache key
     * @param mixed $default Default value if cache not found
     * @return mixed
     */
    private function get_file_cache( string $key, $default = null ) {
        $cache_file = $this->get_cache_file_path( $key );
        
        if ( \file_exists( $cache_file ) ) {
            $cache_data = \file_get_contents( $cache_file );
            $cache = \unserialize( $cache_data );
            
            // Check if cache has expired
            if ( isset( $cache['expiration'] ) && $cache['expiration'] < \time() ) {
                \unlink( $cache_file );
                return $default;
            }
            
            return $cache['value'];
        }
        
        return $default;
    }
    
    /**
     * Set file cache
     *
     * @param string $key Cache key
     * @param mixed $value Cache value
     * @param int $expiration Expiration time in seconds
     * @return bool
     */
    private function set_file_cache( string $key, $value, int $expiration ): bool {
        $cache_file = $this->get_cache_file_path( $key );
        $cache_dir = \dirname( $cache_file );
        
        // Create cache directory if it doesn't exist
        if ( ! \is_dir( $cache_dir ) ) {
            \wp_mkdir_p( $cache_dir );
        }
        
        $cache = array(
            'value' => $value,
            'expiration' => \time() + $expiration,
            'created_at' => \time()
        );
        
        return \file_put_contents( $cache_file, \serialize( $cache ) ) !== false;
    }
    
    /**
     * Delete file cache
     *
     * @param string $key Cache key
     * @return bool
     */
    private function delete_file_cache( string $key ): bool {
        $cache_file = $this->get_cache_file_path( $key );
        
        if ( \file_exists( $cache_file ) ) {
            return \unlink( $cache_file );
        }
        
        return false;
    }
    
    /**
     * Clear file cache
     *
     * @return bool
     */
    private function clear_file_cache(): bool {
        $cache_dir = $this->get_cache_directory();
        
        if ( \is_dir( $cache_dir ) ) {
            $files = \glob( $cache_dir . '/*' );
            
            foreach ( $files as $file ) {
                if ( \is_file( $file ) ) {
                    \unlink( $file );
                }
            }
        }
        
        return true;
    }
    
    /**
     * Get cache file path
     *
     * @param string $key Cache key
     * @return string
     */
    private function get_cache_file_path( string $key ): string {
        $cache_dir = $this->get_cache_directory();
        $key_hash = \md5( $key );
        
        return $cache_dir . '/' . $key_hash . '.cache';
    }
    
    /**
     * Get cache directory
     *
     * @return string
     */
    private function get_cache_directory(): string {
        $cache_dir = WPCA_PLUGIN_DIR . 'cache';
        
        // Create cache directory if it doesn't exist
        if ( ! \is_dir( $cache_dir ) ) {
            \wp_mkdir_p( $cache_dir );
        }
        
        return $cache_dir;
    }
    
    /**
     * Clean expired cache
     */
    private function clean_expired_cache() {
        // Clean expired file cache
        $this->clean_expired_file_cache();
        
        // Clean expired database cache (done via cron)
    }
    
    /**
     * Clean expired file cache
     */
    private function clean_expired_file_cache() {
        $cache_dir = $this->get_cache_directory();
        
        if ( \is_dir( $cache_dir ) ) {
            $files = \glob( $cache_dir . '/*' );
            
            foreach ( $files as $file ) {
                if ( \is_file( $file ) ) {
                    $cache_data = \file_get_contents( $file );
                    $cache = \unserialize( $cache_data );
                    
                    if ( isset( $cache['expiration'] ) && $cache['expiration'] < \time() ) {
                        \unlink( $file );
                    }
                }
            }
        }
    }
    
    /**
     * Set cache enabled status
     *
     * @param bool $enabled Enabled status
     */
    public function set_cache_enabled( bool $enabled ) {
        $this->cache_enabled = $enabled;
    }
    
    /**
     * Get cache enabled status
     *
     * @return bool
     */
    public function get_cache_enabled(): bool {
        return $this->cache_enabled;
    }
    
    /**
     * Set cache expiration time
     *
     * @param int $expiration Expiration time in seconds
     */
    public function set_cache_expiration( int $expiration ) {
        $this->cache_expiration = $expiration;
    }
    
    /**
     * Get cache expiration time
     *
     * @return int
     */
    public function get_cache_expiration(): int {
        return $this->cache_expiration;
    }
}
