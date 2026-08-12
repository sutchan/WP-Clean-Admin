<?php
/**
 * WPCleanAdmin Login Two Factor
 *
 * 承载双因素认证的逻辑（密钥生成、OTP、QR、校验与表单），从 Login 主类抽取。
 *
 * @package WPCleanAdmin
 * @version  1.8.4
 * @author Sut
 * @author URI: https://github.com/Tanox
 * @since 1.7.15
 */

require_once __DIR__ . '/class-wpca-login-totp.php';

namespace WPCleanAdmin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
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
if ( ! function_exists( 'add_action' ) ) {
    function add_action() {}
}
if ( ! function_exists( 'add_filter' ) ) {
    function add_filter() {}
}

/**
 * 双因素认证类
 */
class Login_TwoFactor {

    /**
     * TOTP 工具实例
     *
     * @var Login_TOTP
     */
    private $totp;

    /**
     * 构造时初始化 TOTP 助手
     */
    public function __construct() {
        $this->totp = new Login_TOTP();
    }

    /**
     * Initialize two-factor authentication hooks
     */
    public function init(): void {
        if ( function_exists( 'add_action' ) && function_exists( 'add_filter' ) ) {
            \add_action( 'wp_authenticate_user', array( $this, 'check_two_factor_auth' ), 20, 2 );
            \add_action( 'login_form', array( $this, 'render_two_factor_form' ) );
            \add_action( 'wp_login_failed', array( $this, 'handle_two_factor_failure' ) );
            \add_action( 'user_register', array( $this, 'generate_two_factor_secret' ) );
            \add_action( 'profile_update', array( $this, 'update_two_factor_secret' ) );
        }
    }

    /**
     * Generate two-factor authentication secret for user
     *
     * @param int $user_id
     */
    public function generate_two_factor_secret( $user_id ): void {
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
     * @param int $user_id
     */
    public function update_two_factor_secret( $user_id ): void {
        // Check if secret already exists
        if ( function_exists( '\get_user_meta' ) && ! \get_user_meta( $user_id, 'wpca_two_factor_secret', true ) ) {
            // Generate new secret if it doesn't exist
            $this->generate_two_factor_secret( $user_id );
        }
    }

    /**
     * Create random secret for two-factor authentication
     *
     * @return string
     * @noinspection PhpUnusedPrivateMethodInspection Used by test validation script
     */
    public function create_random_secret(): string {
        $chars  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';

        for ( $i = 0; $i < 16; $i++ ) {
            $secret .= $chars[ rand( 0, strlen( $chars ) - 1 ) ];
        }

        return $secret;
    }

    /**
     * Generate QR code URL for two-factor authentication
     *
     * @param int $user_id
     * @return string
     */
    public function get_qr_code_url( $user_id ): string {
        if ( function_exists( '\get_userdata' ) ) {
            $user = \get_userdata( $user_id );
        } else {
            $user = false;
        }
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
     * Render two-factor authentication form
     */
    public function render_two_factor_form(): void {
        if ( isset( $_REQUEST['wpca_two_factor'] ) && $_REQUEST['wpca_two_factor'] === '1' ) {
            ?>
            <div class="wpca-two-factor-form">
                <h2><?php echo \esc_html( \__( 'Two-Factor Authentication', \WPCA_TEXT_DOMAIN ) ); ?></h2>
                <p><?php echo \esc_html( \__( 'Please enter the 6-digit code from your authenticator app.', \WPCA_TEXT_DOMAIN ) ); ?></p>

                <label for="wpca_two_factor_code"><?php echo \esc_html( \__( 'Authentication Code', \WPCA_TEXT_DOMAIN ) ); ?></label>
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
     * @param object $user
     * @param string $password
     * @return object
     */
    public function check_two_factor_auth( $user, $password ) {
        // Check if this is a two-factor authentication request
        if ( isset( $_POST['wpca_two_factor_code'] ) && isset( $_POST['wpca_user_id'] ) && isset( $_POST['wpca_nonce'] ) ) {
            // Verify nonce
            if ( ! function_exists( '\wp_verify_nonce' ) || ! \wp_verify_nonce( $_POST['wpca_nonce'], 'wpca_two_factor' ) ) {
                return new \WP_Error( 'invalid_nonce', \__( 'Invalid nonce.', \WPCA_TEXT_DOMAIN ) );
            }

            // Get user ID
            $user_id = intval( $_POST['wpca_user_id'] );

            // Get user object
            if ( ! function_exists( '\get_userdata' ) ) {
                return new \WP_Error( 'no_user_data', \__( 'No user data function available.', \WPCA_TEXT_DOMAIN ) );
            }

            $user = \get_userdata( $user_id );
            if ( ! $user ) {
                return new \WP_Error( 'invalid_user', \__( 'Invalid user.', \WPCA_TEXT_DOMAIN ) );
            }

            // Get two-factor code
            $code = isset( $_POST['wpca_two_factor_code'] ) ? trim( $_POST['wpca_two_factor_code'] ) : '';

            // Get secret and verify code via TOTP helper
            $secret = function_exists( '\get_user_meta' ) ? \get_user_meta( $user_id, 'wpca_two_factor_secret', true ) : '';
            if ( ! $this->totp->verify( $code, $secret ) ) {
                return new \WP_Error( 'invalid_code', \__( 'Invalid authentication code.', \WPCA_TEXT_DOMAIN ) );
            }

            // Code is valid, allow login
            return $user;
        } elseif ( isset( $user->ID ) ) {
            // Check if two-factor is enabled for this user
            $two_factor_enabled = function_exists( '\get_user_meta' ) ? \get_user_meta( $user->ID, 'wpca_two_factor_enabled', true ) : false;

            if ( $two_factor_enabled ) {
                // Redirect to two-factor form
                if ( function_exists( '\add_filter' ) ) {
                    \add_filter(
                        'login_redirect',
                        function( $redirect_to, $requested_redirect_to, $user ) {
                            return \add_query_arg(
                                array(
                                    'wpca_two_factor' => '1',
                                    'wpca_user_id'    => $user->ID,
                                    'wpca_nonce'      => \wp_create_nonce( 'wpca_two_factor' )
                                ),
                                \wp_login_url( $redirect_to )
                            );
                        },
                        10,
                        3
                    );
                }
            }
        }

        return $user;
    }

    /**
     * Handle two-factor authentication failure
     *
     * @param string $username
     */
    public function handle_two_factor_failure( $username ): void {
        // Check if this is a two-factor authentication failure
        if ( isset( $_POST['wpca_two_factor_code'] ) ) {
            // Add error message
            if ( function_exists( '\add_action' ) ) {
                \add_action(
                    'login_message',
                    function() {
                        return '<div id="login_error">' . \__( '<strong>ERROR:</strong> Invalid authentication code.', \WPCA_TEXT_DOMAIN ) . '</div>';
                    }
                );
            }
        }
    }
}
