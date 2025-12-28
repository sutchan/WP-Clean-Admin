<?php
/**
 * Functional Tests for Login Module
 *
 * @package WPCleanAdmin
 * @version 1.7.15
 * @since 1.7.15
 */

require_once __DIR__ . '/WPCA_Functional_TestCase.php';

/**
 * Functional test class for Login module
 *
 * Tests the complete login functionality including
 * login customization, security features, and user authentication
 */
class LoginFunctionalTest extends WPCA_Functional_TestCase {
    
    /**
     * Test login URL customization
     *
     * @return void
     */
    public function test_login_url_customization() {
        $this->simulate_admin_page_load();
        
        $login = \WPCleanAdmin\Login::getInstance();
        
        // Test custom_login_url method
        $result = $login->custom_login_url( 'secure-login' );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test login logo customization
     *
     * @return void
     */
    public function test_login_logo_customization() {
        $this->simulate_admin_page_load();
        
        $login = \WPCleanAdmin\Login::getInstance();
        
        // Test custom_login_logo method
        $result = $login->custom_login_logo( 'https://example.com/logo.png' );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test login logo URL customization
     *
     * @return void
     */
    public function test_login_logo_url_customization() {
        $this->simulate_admin_page_load();
        
        $login = \WPCleanAdmin\Login::getInstance();
        
        // Test custom_login_logo_url method
        $result = $login->custom_login_logo_url( 'https://example.com' );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test login title customization
     *
     * @return void
     */
    public function test_login_title_customization() {
        $this->simulate_admin_page_load();
        
        $login = \WPCleanAdmin\Login::getInstance();
        
        // Test custom_login_title method
        $result = $login->custom_login_title( 'Welcome to Our Site' );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test login message customization
     *
     * @return void
     */
    public function test_login_message_customization() {
        $this->simulate_admin_page_load();
        
        $login = \WPCleanAdmin\Login::getInstance();
        
        // Test custom_login_message method
        $result = $login->custom_login_message( 'Please login to continue' );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test background customization
     *
     * @return void
     */
    public function test_background_customization() {
        $this->simulate_admin_page_load();
        
        $login = \WPCleanAdmin\Login::getInstance();
        
        // Test custom_login_background method
        $result = $login->custom_login_background( 'https://example.com/bg.jpg' );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test CSS customization
     *
     * @return void
     */
    public function test_css_customization() {
        $this->simulate_admin_page_load();
        
        $login = \WPCleanAdmin\Login::getInstance();
        
        // Test custom_login_css method
        $css = '.login h1 { color: red; }';
        $result = $login->custom_login_css( $css );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test login redirect
     *
     * @return void
     */
    public function test_login_redirect() {
        $this->simulate_admin_page_load();
        
        $login = \WPCleanAdmin\Login::getInstance();
        
        // Test set_login_redirect method
        $result = $login->set_login_redirect( '/dashboard', 'administrator' );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test logout redirect
     *
     * @return void
     */
    public function test_logout_redirect() {
        $this->simulate_admin_page_load();
        
        $login = \WPCleanAdmin\Login::getInstance();
        
        // Test set_logout_redirect method
        $result = $login->set_logout_redirect( home_url() );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test login attempt limiting
     *
     * @return void
     */
    public function test_login_attempt_limiting() {
        $this->simulate_admin_page_load();
        
        $login = \WPCleanAdmin\Login::getInstance();
        
        // Test limit_login_attempts method
        $result = $login->limit_login_attempts( 5, 15 );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test two-factor authentication enable
     *
     * @return void
     */
    public function test_two_factor_auth_enable() {
        $this->simulate_admin_page_load();
        
        $login = \WPCleanAdmin\Login::getInstance();
        
        // Test enable_two_factor method
        $result = $login->enable_two_factor( true );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test two-factor authentication methods
     *
     * @return void
     */
    public function test_two_factor_auth_methods() {
        $this->simulate_admin_page_load();
        
        $login = \WPCleanAdmin\Login::getInstance();
        
        // Test set_two_factor_methods method
        $result = $login->set_two_factor_methods( array( 'email', 'totp' ) );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test login lockout duration
     *
     * @return void
     */
    public function test_login_lockout_duration() {
        $this->simulate_admin_page_load();
        
        $login = \WPCleanAdmin\Login::getInstance();
        
        // Test set_lockout_duration method
        $result = $login->set_lockout_duration( 30 );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test IP whitelist
     *
     * @return void
     */
    public function test_ip_whitelist() {
        $this->simulate_admin_page_load();
        
        $login = \WPCleanAdmin\Login::getInstance();
        
        // Test add_ip_whitelist method
        $result = $login->add_ip_whitelist( '192.168.1.1' );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test IP blacklist
     *
     * @return void
     */
    public function test_ip_blacklist() {
        $this->simulate_admin_page_load();
        
        $login = \WPCleanAdmin\Login::getInstance();
        
        // Test add_ip_blacklist method
        $result = $login->add_ip_blacklist( '10.0.0.1' );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test password requirements
     *
     * @return void
     */
    public function test_password_requirements() {
        $this->simulate_admin_page_load();
        
        $login = \WPCleanAdmin\Login::getInstance();
        
        // Test set_password_requirements method
        $requirements = array(
            'min_length' => 8,
            'require_uppercase' => true,
            'require_number' => true,
            'require_special' => true,
        );
        
        $result = $login->set_password_requirements( $requirements );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test password expiration
     *
     * @return void
     */
    public function test_password_expiration() {
        $this->simulate_admin_page_load();
        
        $login = \WPCleanAdmin\Login::getInstance();
        
        // Test set_password_expiration method
        $result = $login->set_password_expiration( 90 );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test remember me functionality
     *
     * @return void
     */
    public function test_remember_me_functionality() {
        $this->simulate_admin_page_load();
        
        $login = \WPCleanAdmin\Login::getInstance();
        
        // Test disable_remember_me method
        $result = $login->disable_remember_me( false );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test login notifications
     *
     * @return void
     */
    public function test_login_notifications() {
        $this->simulate_admin_page_load();
        
        $login = \WPCleanAdmin\Login::getInstance();
        
        // Test enable_login_notification method
        $result = $login->enable_login_notification( true );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test failed login notification
     *
     * @return void
     */
    public function test_failed_login_notification() {
        $this->simulate_admin_page_load();
        
        $login = \WPCleanAdmin\Login::getInstance();
        
        // Test enable_failed_login_notification method
        $result = $login->enable_failed_login_notification( true );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test custom login footer
     *
     * @return void
     */
    public function test_custom_login_footer() {
        $this->simulate_admin_page_load();
        
        $login = \WPCleanAdmin\Login::getInstance();
        
        // Test custom_login_footer method
        $result = $login->custom_login_footer( '<p>Custom Footer Text</p>' );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test login settings export
     *
     * @return void
     */
    public function test_login_settings_export() {
        $this->simulate_admin_page_load();
        
        $login = \WPCleanAdmin\Login::getInstance();
        
        // Test export_login_settings method
        $export = $login->export_login_settings();
        $this->assertIsArray( $export );
        $this->assertArrayHasKey( 'logo', $export );
        $this->assertArrayHasKey( 'background', $export );
    }
    
    /**
     * Test login settings import
     *
     * @return void
     */
    public function test_login_settings_import() {
        $this->simulate_admin_page_load();
        
        $login = \WPCleanAdmin\Login::getInstance();
        
        // Test import_login_settings method
        $settings = array(
            'logo' => '',
            'background' => '',
            'custom_css' => '',
        );
        
        $result = $login->import_login_settings( $settings );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test login settings reset
     *
     * @return void
     */
    public function test_login_settings_reset() {
        $this->simulate_admin_page_load();
        
        $login = \WPCleanAdmin\Login::getInstance();
        
        // Test reset_login_settings method
        $result = $login->reset_login_settings();
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
    
    /**
     * Test get_login_settings method
     *
     * @return void
     */
    public function test_get_login_settings() {
        $this->simulate_admin_page_load();
        
        $login = \WPCleanAdmin\Login::getInstance();
        
        $settings = $login->get_login_settings();
        $this->assertIsArray( $settings );
    }
    
    /**
     * Test save_login_settings method
     *
     * @return void
     */
    public function test_save_login_settings() {
        $this->simulate_admin_page_load();
        
        $login = \WPCleanAdmin\Login::getInstance();
        
        $settings = array(
            'custom_login_url' => '',
            'custom_login_logo' => '',
            'custom_login_title' => '',
        );
        
        $result = $login->save_login_settings( $settings );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'success', $result );
    }
}
