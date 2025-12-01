<?php
/**
 * i18n class for WP Clean Admin plugin
 *
 * @package WPCleanAdmin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

namespace WPCleanAdmin;

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
    public static function get_instance() {
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
        \add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
    }
    
    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        // Load plugin textdomain
        \load_plugin_textdomain(
            WPCA_TEXT_DOMAIN,
            false,
            \dirname( \plugin_basename( __FILE__ ) ) . '/../languages/'
        );
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
            return \_x( $string, $context, WPCA_TEXT_DOMAIN );
        }
        return \__( $string, WPCA_TEXT_DOMAIN );
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
        return \_n( $single, $plural, $number, WPCA_TEXT_DOMAIN );
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
        return \_nx( $single, $plural, $number, $context, WPCA_TEXT_DOMAIN );
    }
    
    /**
     * Get current locale
     *
     * @return string Current locale
     */
    public function get_locale() {
        return \get_locale();
    }
    
    /**
     * Get available languages
     *
     * @return array Available languages
     */
    public function get_available_languages() {
        return \get_available_languages( WPCA_PLUGIN_DIR . 'languages/' );
    }
    
    /**
     * Get current language
     *
     * @return string Current language
     */
    public function get_current_language() {
        $locale = \get_locale();
        return \substr( $locale, 0, 2 );
    }
    
    /**
     * Check if current language is RTL
     *
     * @return bool Whether current language is RTL
     */
    public function is_rtl() {
        return \is_rtl();
    }
}