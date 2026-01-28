<?php
/**
 * WPCleanAdmin Login Class
 *
 * @package WPCleanAdmin
 * @version 1.8.0
 * @author Sut
 * @author URI: https://github.com/sutchan
 * @since 1.7.15
 */
namespace WPCleanAdmin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Declare WordPress functions for IDE compatibility
if ( ! function_exists( 'set_transient' ) ) {
    function set_transient() {}
}
if ( ! function_exists( 'get_transient' ) ) {
    function get_transient() {}
}
if ( ! function_exists( 'is_ssl' ) ) {
    function is_ssl() {}
}
if ( ! function_exists( 'is_wp_error' ) ) {
    function is_wp_error() {}
}
if ( ! function_exists( 'wp_kses_post' ) ) {
    function wp_kses_post() {}
}
if ( ! function_exists( 'sanitize_html_class' ) ) {
    function sanitize_html_class() {}
}
if ( ! function_exists( 'update_user_meta' ) ) {
    function update_user_meta() {}
}
if ( ! function_exists( 'get_user_meta' ) ) {
    function get_user_meta() {}
}
if ( ! function_exists( 'get_userdata' ) ) {
    function get_userdata() {}
}
if ( ! function_exists( 'wp_verify_nonce' ) ) {
    function wp_verify_nonce() {}
}
if ( ! function_exists( 'wp_login_url' ) ) {
    function wp_login_url() {}
}

/**
 * Login class
 */
class Login {
    
    /**
     * Singleton instance
     *
     * @var Login
     */
    private static $instance;
    
    /**
     * Get singleton instance
     *
     * @return Login
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
     * Initialize the login module
     */
    public function init() {
        // Handle CAPTCHA image request
        if ( isset( $_GET['wpca_captcha'] ) && $_GET['wpca_captcha'] === '1' ) {
            $this->generate_captcha_image();
        }
        
        // Add login hooks
        if ( function_exists( 'add_action' ) && function_exists( 'add_filter' ) ) {
            \add_action( 'login_enqueue_scripts', array( $this, 'enqueue_login_scripts' ) );
            \add_filter( 'login_headerurl', array( $this, 'filter_login_header_url' ) );
            \add_filter( 'login_headertitle', array( $this, 'filter_login_header_title' ) );
            \add_action( 'login_footer', array( $this, 'add_login_footer_content' ) );
            \add_filter( 'login_body_class', array( $this, 'filter_login_body_class' ) );
            
            // Initialize two-factor authentication
            $this->init_two_factor_auth();
            
            // Initialize CAPTCHA
            $this->init_captcha();
        }
    }
    
    /**
     * Initialize CAPTCHA
     */
    private function init_captcha() {
        // Load settings
        $settings = wpca_get_settings();
        
        // Check if CAPTCHA is enabled
        if ( isset( $settings['login'] ) && isset( $settings['login']['login_captcha'] ) && $settings['login']['login_captcha'] ) {
            if ( function_exists( 'add_action' ) && function_exists( 'add_filter' ) ) {
                // Add CAPTCHA hooks
                \add_action( 'login_form', array( $this, 'render_captcha' ) );
                \add_filter( 'authenticate', array( $this, 'verify_captcha' ), 25, 3 );
            }
        }
    }
    
    /**
     * Generate CAPTCHA code
     *
     * @return string Generated CAPTCHA code
     */
    private function generate_captcha() {
        // Generate random code
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code = '';
        
        for ( $i = 0; $i < 6; $i++ ) {
            $code .= $characters[rand( 0, strlen( $characters ) - 1 )];
        }
        
        // Store in transient (wp_session doesn't exist in WordPress core)
        if ( function_exists( '\set_transient' ) && isset( $_SERVER['REMOTE_ADDR'] ) ) {
            $user_ip = $_SERVER['REMOTE_ADDR'];
            \set_transient( 'wpca_captcha_' . $user_ip, $code, 300 ); // 5 minutes
        }
        
        return $code;
    }
    
    /**
     * Generate CAPTCHA image
     */
    public function generate_captcha_image() {
        // Generate CAPTCHA code
        $code = $this->generate_captcha();
        
        // Create image
        $width = 120;
        $height = 40;
        
        $image = imagecreatetruecolor( $width, $height );
        
        // Set colors
        $bg_color = imagecolorallocate( $image, 240, 240, 240 );
        $text_color = imagecolorallocate( $image, 30, 30, 30 );
        $line_color = imagecolorallocate( $image, 150, 150, 150 );
        
        // Fill background
        imagefilledrectangle( $image, 0, 0, $width, $height, $bg_color );
        
        // Add noise lines
        for ( $i = 0; $i < 5; $i++ ) {
            imageline( $image, rand( 0, $width ), rand( 0, $height ), rand( 0, $width ), rand( 0, $height ), $line_color );
        }
        
        // Add text
        $font = 5;
        $text_width = imagefontwidth( $font ) * strlen( $code );
        $text_height = imagefontheight( $font );
        $x = ( $width - $text_width ) / 2;
        $y = ( $height - $text_height ) / 2;
        
        imagestring( $image, $font, $x, $y, $code, $text_color );
        
        // Output image
        header( 'Content-type: image/png' );
        imagepng( $image );
        imagedestroy( $image );
        exit;
    }
    
    /**
     * Render CAPTCHA form
     */
    public function render_captcha() {
        // Get current URL
        $current_url = ( function_exists( '\is_ssl' ) && \is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        
        // Generate CAPTCHA image URL
        $captcha_url = function_exists( '\add_query_arg' ) ? \add_query_arg( array( 'wpca_captcha' => '1' ), $current_url ) : $current_url;
        
        // Add cache buster
        $captcha_url = function_exists( '\add_query_arg' ) ? \add_query_arg( array( 't' => time() ), $captcha_url ) : $captcha_url;
        
        ?>        
        <div class="wpca-captcha-form">
            <p>
                <label for="wpca_captcha"><?php echo \esc_html( \__( 'CAPTCHA', WPCA_TEXT_DOMAIN ) ); ?></label>
                <br />
                <img src="<?php echo \esc_url( $captcha_url ); ?>" alt="CAPTCHA" class="wpca-captcha-image" />
                <br />
                <input type="text" name="wpca_captcha" id="wpca_captcha" class="input" value="" size="20" maxlength="6" autocomplete="off" placeholder="<?php echo \esc_attr( \__( 'Enter CAPTCHA code', WPCA_TEXT_DOMAIN ) ); ?>" />
                <br />
                <small><a href="<?php echo \esc_url( $current_url ); ?>" class="wpca-refresh-captcha"><?php echo \esc_html( \__( 'Refresh CAPTCHA', WPCA_TEXT_DOMAIN ) ); ?></a></small>
            </p>
        </div>
        <?php
    }
    
    /**
     * Verify CAPTCHA
     *
     * @param object $user User object or error
     * @param string $username Username
     * @param string $password Password
     * @return object Modified user or error
     */
    public function verify_captcha( $user, $username, $password ) {
        // Skip if user is already an error
        if ( function_exists( '\is_wp_error' ) && \is_wp_error( $user ) ) {
            return $user;
        }
        
        // Check if CAPTCHA is submitted
        if ( isset( $_POST['wpca_captcha'] ) ) {
            $submitted_code = function_exists( '\sanitize_text_field' ) ? \sanitize_text_field( $_POST['wpca_captcha'] ) : $_POST['wpca_captcha'];
            
            // Get stored CAPTCHA code
            $stored_code = '';
            
            // Use transient for storage (wp_session doesn't exist in WordPress core)
            if ( function_exists( '\get_transient' ) && isset( $_SERVER['REMOTE_ADDR'] ) ) {
                $user_ip = $_SERVER['REMOTE_ADDR'];
                $stored_code = \get_transient( 'wpca_captcha_' . $user_ip );
            }
            
            // Verify CAPTCHA code
            if ( empty( $submitted_code ) || strtoupper( $submitted_code ) !== strtoupper( $stored_code ) ) {
                return new \WP_Error( 'invalid_captcha', \__( 'Invalid CAPTCHA code. Please try again.', WPCA_TEXT_DOMAIN ) );
            }
        }
        
        return $user;
    }
    
    /**
     * Initialize two-factor authentication
     */
    private function init_two_factor_auth() {
        // Load settings
        $settings = wpca_get_settings();
        
        // Check if two-factor authentication is enabled
        if ( isset( $settings['security'] ) && isset( $settings['security']['two_factor_auth'] ) && $settings['security']['two_factor_auth'] ) {
            if ( function_exists( 'add_action' ) && function_exists( 'add_filter' ) ) {
                // Add two-factor authentication hooks
                \add_action( 'wp_authenticate_user', array( $this, 'check_two_factor_auth' ), 20, 2 );
                \add_action( 'login_form', array( $this, 'render_two_factor_form' ) );
                \add_action( 'wp_login_failed', array( $this, 'handle_two_factor_failure' ) );
                \add_action( 'user_register', array( $this, 'generate_two_factor_secret' ) );
                \add_action( 'profile_update', array( $this, 'update_two_factor_secret' ) );
            }
        }
    }
    
    /**
     * Enqueue login scripts and styles
     *
     * @uses wpca_get_settings() To retrieve plugin settings
     * @uses \wp_enqueue_style() To enqueue login styles
     * @uses \wp_enqueue_script() To enqueue login scripts
     * @return void
     */
    public function enqueue_login_scripts() {
        // Load settings
        $settings = wpca_get_settings();
        
        // Enqueue custom login styles if enabled
        if ( isset( $settings['login'] ) && isset( $settings['login']['custom_login_styles'] ) && $settings['login']['custom_login_styles'] ) {
            // Enqueue login CSS
            if ( function_exists( '\wp_enqueue_style' ) ) {
                \wp_enqueue_style(
                    'wpca-login',
                    WPCA_PLUGIN_URL . 'assets/css/wpca-login.css',
                    array(),
                    WPCA_VERSION
                );
            }
            
            // Enqueue login JS
            if ( function_exists( '\wp_enqueue_script' ) ) {
                \wp_enqueue_script(
                    'wpca-login',
                    WPCA_PLUGIN_URL . 'assets/js/wpca-login.js',
                    array( 'jquery' ),
                    WPCA_VERSION,
                    true
                );
            }
        }
    }
    
    /**
     * Filter login header URL
     *
     * @param string $url Login header URL
     * @return string Modified URL
     */
    public function filter_login_header_url( $url ) {
        // Load settings
        $settings = wpca_get_settings();
        
        // Change login header URL if custom URL is set
        if ( isset( $settings['login'] ) && isset( $settings['login']['login_header_url'] ) && ! empty( $settings['login']['login_header_url'] ) ) {
            return ( function_exists( 'esc_url' ) ? \esc_url( $settings['login']['login_header_url'] ) : $settings['login']['login_header_url'] );
        }
        
        return $url;
    }
    
    /**
     * Filter login header title
     *
     * @param string $title Login header title
     * @return string Modified title
     */
    public function filter_login_header_title( string $title ): string {
        // Load settings
        $settings = wpca_get_settings();
        
        // Change login header title if custom title is set
        if ( isset( $settings['login'] ) && isset( $settings['login']['login_header_title'] ) && ! empty( $settings['login']['login_header_title'] ) ) {
            return ( function_exists( 'esc_html' ) ? \esc_html( $settings['login']['login_header_title'] ) : $settings['login']['login_header_title'] );
        }
        
        return $title;
    }
    
    /**
     * Add login footer content
     */
    public function add_login_footer_content() {
        // Load settings
        $settings = wpca_get_settings();
        
        // Add custom footer content if set
        if ( isset( $settings['login'] ) && isset( $settings['login']['login_footer_content'] ) && ! empty( $settings['login']['login_footer_content'] ) ) {
            echo ( function_exists( '\wp_kses_post' ) ? \wp_kses_post( $settings['login']['login_footer_content'] ) : $settings['login']['login_footer_content'] );
        }
    }
    
    /**
     * Filter login body class
     *
     * @param array $classes Body classes
     * @return array Modified classes
     */
    public function filter_login_body_class( $classes ) {
        // Load settings
        $settings = wpca_get_settings();
        
        // Add custom body class if set
        if ( isset( $settings['login'] ) && isset( $settings['login']['login_body_class'] ) && ! empty( $settings['login']['login_body_class'] ) ) {
            $classes[] = ( function_exists( '\sanitize_html_class' ) ? \sanitize_html_class( $settings['login']['login_body_class'] ) : $settings['login']['login_body_class'] );
        }
        
        return $classes;
    }
    
    /**
     * Customize login page
     */
    public function customize_login_page(): void {
        // Load settings
        $settings = wpca_get_settings();
        
        // Customize login page based on settings
        if ( isset( $settings['login'] ) && function_exists( 'add_action' ) ) {
            // Change login logo
            if ( isset( $settings['login']['custom_login_logo'] ) && $settings['login']['custom_login_logo'] && isset( $settings['login']['login_logo_url'] ) && ! empty( $settings['login']['login_logo_url'] ) ) {
                \add_action( 'login_head', array( $this, 'add_custom_login_logo' ) );
            }
            
            // Change login background
            if ( isset( $settings['login']['custom_login_background'] ) && $settings['login']['custom_login_background'] && isset( $settings['login']['login_background_url'] ) && ! empty( $settings['login']['login_background_url'] ) ) {
                \add_action( 'login_head', array( $this, 'add_custom_login_background' ) );
            }
        }
    }
    
    /**
     * Add custom login logo
     */
    public function add_custom_login_logo(): void {
        // Load settings
        $settings = wpca_get_settings();
        
        if ( isset( $settings['login'] ) && isset( $settings['login']['login_logo_url'] ) && ! empty( $settings['login']['login_logo_url'] ) ) {
            $logo_url = ( function_exists( 'esc_url' ) ? \esc_url( $settings['login']['login_logo_url'] ) : $settings['login']['login_logo_url'] );
            $logo_width = isset( $settings['login']['login_logo_width'] ) ? ( function_exists( 'esc_attr' ) ? \esc_attr( $settings['login']['login_logo_width'] ) : $settings['login']['login_logo_width'] ) : '200px';
            $logo_height = isset( $settings['login']['login_logo_height'] ) ? ( function_exists( 'esc_attr' ) ? \esc_attr( $settings['login']['login_logo_height'] ) : $settings['login']['login_logo_height'] ) : '80px';
            
            echo "<style type='text/css'>
                #login h1 a {
                    background-image: url('{$logo_url}');
                    background-size: contain;
                    width: {$logo_width};
                    height: {$logo_height};
                }
            </style>";
        }
    }
    
    /**
     * Add custom login background
     */
    public function add_custom_login_background() {
        // Load settings
        $settings = wpca_get_settings();
        
        if ( isset( $settings['login'] ) && isset( $settings['login']['login_background_url'] ) && ! empty( $settings['login']['login_background_url'] ) ) {
            $background_url = ( function_exists( 'esc_url' ) ? \esc_url( $settings['login']['login_background_url'] ) : $settings['login']['login_background_url'] );
            $background_repeat = isset( $settings['login']['login_background_repeat'] ) ? ( function_exists( 'esc_attr' ) ? \esc_attr( $settings['login']['login_background_repeat'] ) : $settings['login']['login_background_repeat'] ) : 'no-repeat';
            $background_position = isset( $settings['login']['login_background_position'] ) ? ( function_exists( 'esc_attr' ) ? \esc_attr( $settings['login']['login_background_position'] ) : $settings['login']['login_background_position'] ) : 'center center';
            $background_size = isset( $settings['login']['login_background_size'] ) ? ( function_exists( 'esc_attr' ) ? \esc_attr( $settings['login']['login_background_size'] ) : $settings['login']['login_background_size'] ) : 'cover';
            
            echo "<style type='text/css'>
                body.login {
                    background-image: url('{$background_url}');
                    background-repeat: {$background_repeat};
                    background-position: {$background_position};
                    background-size: {$background_size};
                }
            </style>";
        }
    }
    
    /**
     * Restrict login attempts
     */
    public function restrict_login_attempts(): void {
        // Load settings
        $settings = wpca_get_settings();
        
        // Restrict login attempts if enabled
        if ( isset( $settings['login'] ) && isset( $settings['login']['restrict_login_attempts'] ) && $settings['login']['restrict_login_attempts'] ) {
            // Add login attempt restriction hooks
            if ( function_exists( 'add_filter' ) && function_exists( 'add_action' ) ) {
                \add_filter( 'authenticate', array( $this, 'check_login_attempts' ), 30, 3 );
                \add_action( 'wp_login_failed', array( $this, 'log_failed_login' ) );
            }
        }
    }
    
    /**
     * Check login attempts
     *
     * @param object $user User object or error
     * @param string $username Username
     * @param string $password Password
     * @return object Modified user or error
     */
    public function check_login_attempts( $user, $username, $password ) {
        // Load settings
        $settings = wpca_get_settings();
        
        // Get max login attempts
        $max_attempts = isset( $settings['login']['max_login_attempts'] ) ? intval( $settings['login']['max_login_attempts'] ) : 5;
        
        // Get lockout duration
        $lockout_duration = isset( $settings['login']['lockout_duration'] ) ? intval( $settings['login']['lockout_duration'] ) : 300;
        
        // Get user IP
        $user_ip = $_SERVER['REMOTE_ADDR'];
        
        // Get login attempts
        $login_attempts = ( function_exists( '\get_transient' ) ? \get_transient( 'wpca_login_attempts_' . $user_ip ) : 0 );
        
        // Check if user is locked out
        if ( $login_attempts >= $max_attempts ) {
            return new \WP_Error( 'too_many_attempts', \__( 'Too many login attempts. Please try again later.', WPCA_TEXT_DOMAIN ) );
        }
        
        return $user;
    }
    
    /**
     * Log failed login attempts
     *
     * @param string $username Username
     */
    public function log_failed_login( $username ) {
        // Get user IP
        $user_ip = $_SERVER['REMOTE_ADDR'];
        
        // Get login attempts
        $login_attempts = ( function_exists( '\get_transient' ) ? \get_transient( 'wpca_login_attempts_' . $user_ip ) : 0 );
        
        // Increment login attempts
        $login_attempts = $login_attempts ? $login_attempts + 1 : 1;
        
        // Set transient
        if ( function_exists( '\set_transient' ) ) {
            \set_transient( 'wpca_login_attempts_' . $user_ip, $login_attempts, 300 ); // 5 minutes
        }
    }
    
    /**
     * Generate two-factor authentication secret for user
     *
     * @param int $user_id User ID
     */
    public function generate_two_factor_secret( $user_id ) {
        // Generate random secret
        $secret = $this->create_random_secret();
        
        // Save secret to user meta
        if ( function_exists( '\update_user_meta' ) ) {
            \update_user_meta( $user_id, 'wpca_two_factor_secret', $secret );
            \update_user_meta( $user_id, 'wpca_two_factor_enabled', 1 );
        }
    }
    
    /**
     * Update two-factor authentication secret
     *
     * @param int $user_id User ID
     */
    public function update_two_factor_secret( $user_id ) {
        // Check if secret already exists
        if ( function_exists( '\get_user_meta' ) && ! \get_user_meta( $user_id, 'wpca_two_factor_secret', true ) ) {
            // Generate new secret if it doesn't exist
            $this->generate_two_factor_secret( $user_id );
        }
    }
    
    /**
     * Create random secret for two-factor authentication
     *
     * @return string Random secret
     * @noinspection PhpUnusedPrivateMethodInspection Used by test validation script
     */
    public function create_random_secret() {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        
        for ( $i = 0; $i < 16; $i++ ) {
            $secret .= $chars[rand( 0, strlen( $chars ) - 1 )];
        }
        
        return $secret;
    }
    
    /**
     * Generate QR code URL for two-factor authentication
     *
     * @param int $user_id User ID
     * @return string QR code URL
     */
    public function get_qr_code_url( $user_id ) {
        if ( ! function_exists( '\get_userdata' ) ) {
            return '';
        }
        
        $user = \get_userdata( $user_id );
        if ( ! $user ) {
            return '';
        }
        
        // Get secret
        $secret = function_exists( '\get_user_meta' ) ? \get_user_meta( $user_id, 'wpca_two_factor_secret', true ) : '';
        if ( ! $secret ) {
            return '';
        }
        
        // Get site name
        $site_name = function_exists( '\get_bloginfo' ) ? \get_bloginfo( 'name' ) : 'WordPress';
        
        // Generate otpauth URL
        $otpauth_url = 'otpauth://totp/' . urlencode( $site_name . ':' . $user->user_email ) . '?secret=' . $secret . '&issuer=' . urlencode( $site_name );
        
        // Return QR code URL
        return 'https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=' . urlencode( $otpauth_url );
    }
    
    /**
     * Check two-factor authentication code
     *
     * @param string $code Authentication code
     * @param int $user_id User ID
     * @return bool Whether the code is valid
     */
    private function verify_two_factor_code( $code, $user_id ) {
        // Get secret
        $secret = function_exists( '\get_user_meta' ) ? \get_user_meta( $user_id, 'wpca_two_factor_secret', true ) : '';
        if ( ! $secret ) {
            return false;
        }
        
        // Get current timestamp
        $timestamp = time();
        
        // Check code with time tolerance
        for ( $offset = -1; $offset <= 1; $offset++ ) {
            $time = floor( ( $timestamp + ( $offset * 30 ) ) / 30 );
            $otp = $this->generate_otp( $secret, $time );
            
            if ( $code === $otp ) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Generate OTP code from secret and counter
     *
     * @param string $secret Secret key
     * @param int $counter Counter
     * @return string OTP code
     */
    private function generate_otp( $secret, $counter ) {
        // Convert secret to binary
        $secret_bin = $this->base32_decode( $secret );
        
        // Pack counter into binary
        $counter_bin = str_pad( pack( 'N', $counter ), 8, chr( 0 ), STR_PAD_LEFT );
        
        // Calculate HMAC-SHA1
        $hash = hash_hmac( 'sha1', $counter_bin, $secret_bin, true );
        
        // Get offset
        $offset = ord( substr( $hash, -1 ) ) & 0x0F;
        
        // Get 4 bytes from hash
        $code = unpack( 'N', substr( $hash, $offset, 4 ) )[1];
        $code &= 0x7FFFFFFF;
        $code %= 1000000;
        
        // Format code to 6 digits
        return str_pad( $code, 6, '0', STR_PAD_LEFT );
    }
    
    /**
     * Base32 decode function
     *
     * @param string $str Base32 encoded string
     * @return string Decoded binary string
     */
    private function base32_decode( $str ) {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $str = strtoupper( $str );
        $str = str_replace( array( ' ', '\r', '\n', '\t' ), '', $str );
        
        $length = strlen( $str );
        $result = '';
        $buffer = 0;
        $buffer_length = 0;
        
        for ( $i = 0; $i < $length; $i++ ) {
            $char = $str[$i];
            $index = strpos( $chars, $char );
            
            if ( $index === false ) {
                continue;
            }
            
            $buffer = ( $buffer << 5 ) | $index;
            $buffer_length += 5;
            
            if ( $buffer_length >= 8 ) {
                $result .= chr( ( $buffer >> ( $buffer_length - 8 ) ) & 0xFF );
                $buffer_length -= 8;
            }
        }
        
        return $result;
    }
    
    /**
     * Render two-factor authentication form
     */
    public function render_two_factor_form() {
        if ( isset( $_REQUEST['wpca_two_factor'] ) && $_REQUEST['wpca_two_factor'] === '1' ) {
            ?>
            <div class="wpca-two-factor-form">
                <h2><?php echo \esc_html( \__( 'Two-Factor Authentication', WPCA_TEXT_DOMAIN ) ); ?></h2>
                <p><?php echo \esc_html( \__( 'Please enter the 6-digit code from your authenticator app.', WPCA_TEXT_DOMAIN ) ); ?></p>
                
                <label for="wpca_two_factor_code"><?php echo \esc_html( \__( 'Authentication Code', WPCA_TEXT_DOMAIN ) ); ?></label>
                <input type="text" name="wpca_two_factor_code" id="wpca_two_factor_code" class="input" value="" size="20" maxlength="6" autocomplete="off" placeholder="123456" />
                
                <input type="hidden" name="wpca_user_id" value="<?php echo \esc_attr( $_REQUEST['wpca_user_id'] ); ?>" />
                <input type="hidden" name="wpca_nonce" value="<?php echo \esc_attr( \wp_create_nonce( 'wpca_two_factor' ) ); ?>" />
            </div>
            <?php
        }
    }
    
    /**
     * Check two-factor authentication during login
     *
     * @param object $user User object
     * @param string $password Password
     * @return object Modified user or error
     */
    public function check_two_factor_auth( $user, $password ) {
        // Check if this is a two-factor authentication request
        if ( isset( $_POST['wpca_two_factor_code'] ) && isset( $_POST['wpca_user_id'] ) && isset( $_POST['wpca_nonce'] ) ) {
            // Verify nonce
            if ( ! function_exists( '\wp_verify_nonce' ) || ! \wp_verify_nonce( $_POST['wpca_nonce'], 'wpca_two_factor' ) ) {
                return new \WP_Error( 'invalid_nonce', \__( 'Invalid nonce.', WPCA_TEXT_DOMAIN ) );
            }
            
            // Get user ID
            $user_id = intval( $_POST['wpca_user_id'] );
            
            // Get user object
            if ( ! function_exists( '\get_userdata' ) ) {
                return new \WP_Error( 'no_user_data', \__( 'No user data function available.', WPCA_TEXT_DOMAIN ) );
            }
            
            $user = \get_userdata( $user_id );
            if ( ! $user ) {
                return new \WP_Error( 'invalid_user', \__( 'Invalid user.', WPCA_TEXT_DOMAIN ) );
            }
            
            // Get two-factor code
            $code = isset( $_POST['wpca_two_factor_code'] ) ? trim( $_POST['wpca_two_factor_code'] ) : '';
            
            // Verify code
            if ( ! $this->verify_two_factor_code( $code, $user_id ) ) {
                return new \WP_Error( 'invalid_code', \__( 'Invalid authentication code.', WPCA_TEXT_DOMAIN ) );
            }
            
            // Code is valid, allow login
            return $user;
        } elseif ( isset( $user->ID ) ) {
            // Check if two-factor is enabled for this user
            $two_factor_enabled = function_exists( '\get_user_meta' ) ? \get_user_meta( $user->ID, 'wpca_two_factor_enabled', true ) : false;
            
            if ( $two_factor_enabled ) {
                // Redirect to two-factor form
                if ( function_exists( '\add_filter' ) ) {
                    \add_filter( 'login_redirect', function( $redirect_to, $requested_redirect_to, $user ) {
                        return \add_query_arg( array(
                            'wpca_two_factor' => '1',
                            'wpca_user_id' => $user->ID,
                            'wpca_nonce' => \wp_create_nonce( 'wpca_two_factor' )
                        ), \wp_login_url( $redirect_to ) );
                    }, 10, 3 );
                }
            }
        }
        
        return $user;
    }
    
    /**
     * Handle two-factor authentication failure
     *
     * @param string $username Username
     */
    public function handle_two_factor_failure( $username ) {
        // Check if this is a two-factor authentication failure
        if ( isset( $_POST['wpca_two_factor_code'] ) ) {
            // Add error message
            if ( function_exists( '\add_action' ) ) {
                \add_action( 'login_message', function() {
                    return '<div id="login_error">' . \__( '<strong>ERROR:</strong> Invalid authentication code.', WPCA_TEXT_DOMAIN ) . '</div>';
                } );
            }
        }
    }
}

