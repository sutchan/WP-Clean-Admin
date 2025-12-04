<?php
/**
 * WPCleanAdmin I18n Class
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



/**
 * i18n class
 */
class i18n {
    
    /**
     * Singleton instance
     *
     * @var i18n
     */
    private static $instance;
    
    /**
     * Get singleton instance
     *
     * @return i18n
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
     * Initialize the i18n module
     */
    public function init() {
        // Add i18n hooks
        if ( function_exists( 'add_action' ) ) {
            \add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
        }
    }
    
    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        // Load plugin textdomain
        if ( function_exists( 'load_plugin_textdomain' ) && function_exists( 'plugin_basename' ) ) {
            \load_plugin_textdomain(
                WPCA_TEXT_DOMAIN,
                false,
                \dirname( \plugin_basename( __FILE__ ) ) . '/../languages/'
            );
        }
    }
    
    /**
     * Get translated string
     *
     * @param string $string String to translate
     * @param string $context Translation context
     * @return string Translated string
     */
    public function translate( $string, $context = '' ) {
        if ( ! empty( $context ) ) {
            return ( function_exists( '_x' ) ? \_x( $string, $context, WPCA_TEXT_DOMAIN ) : $string );
        }
        return ( function_exists( '__' ) ? \__( $string, WPCA_TEXT_DOMAIN ) : $string );
    }
    
    /**
     * Get translated plural string
     *
     * @param string $single Singular string
     * @param string $plural Plural string
     * @param int $number Number
     * @return string Translated string
     */
    public function translate_plural( $single, $plural, $number ) {
        return ( function_exists( '_n' ) ? \_n( $single, $plural, $number, WPCA_TEXT_DOMAIN ) : ( $number === 1 ? $single : $plural ) );
    }
    
    /**
     * Get translated string with context and plural
     *
     * @param string $single Singular string
     * @param string $plural Plural string
     * @param int $number Number
     * @param string $context Translation context
     * @return string Translated string
     */
    public function translate_plural_with_context( $single, $plural, $number, $context ) {
        return ( function_exists( '_nx' ) ? \_nx( $single, $plural, $number, $context, WPCA_TEXT_DOMAIN ) : ( $number === 1 ? $single : $plural ) );
    }
    
    /**
     * Get current locale
     *
     * @return string Current locale
     */
    public function get_locale() {
        return ( function_exists( 'get_locale' ) ? \get_locale() : 'en_US' );
    }
    
    /**
     * Get available languages
     *
     * @return array Available languages
     */
    public function get_available_languages() {
        return ( function_exists( 'get_available_languages' ) ? \get_available_languages( WPCA_PLUGIN_DIR . 'languages/' ) : array() );
    }
    
    /**
     * Get current language
     *
     * @return string Current language
     */
    public function get_current_language() {
        $locale = ( function_exists( 'get_locale' ) ? \get_locale() : 'en_US' );
        return substr( $locale, 0, 2 );
    }
    
    /**
     * Check if current language is RTL
     *
     * @return bool Whether current language is RTL
     */
    public function is_rtl() {
        return ( function_exists( 'is_rtl' ) ? \is_rtl() : false );
    }
}