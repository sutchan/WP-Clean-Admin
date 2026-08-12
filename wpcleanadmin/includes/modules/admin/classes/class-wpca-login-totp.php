<?php
/**
 * WPCleanAdmin Login TOTP
 *
 * 承载基于时间的一次性密码（TOTP）生成与校验逻辑，
 * 从 Login_TwoFactor 主类抽取，保持单一职责。
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
 * TOTP（基于时间的一次性密码）工具类
 */
class Login_TOTP {

    /**
     * Verify two-factor authentication code against user secret
     *
     * @param string $code
     * @param string $secret
     * @return bool
     */
    public function verify( string $code, string $secret ): bool {
        if ( ! $secret ) {
            return false;
        }

        $timestamp = time();

        // Check code with time tolerance (±30s)
        for ( $offset = -1; $offset <= 1; $offset++ ) {
            $time = floor( ( $timestamp + ( $offset * 30 ) ) / 30 );
            $otp  = $this->generate_otp( $secret, $time );

            if ( $code === $otp ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate OTP code from secret and counter
     *
     * @param string $secret
     * @param int    $counter
     * @return string
     */
    public function generate_otp( string $secret, int $counter ): string {
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
     * @param string $str
     * @return string
     */
    public function base32_decode( string $str ): string {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $str   = strtoupper( $str );
        $str   = str_replace( array( ' ', '\r', '\n', '\t' ), '', $str );

        $length        = strlen( $str );
        $result        = '';
        $buffer        = 0;
        $buffer_length = 0;

        for ( $i = 0; $i < $length; $i++ ) {
            $char  = $str[ $i ];
            $index = strpos( $chars, $char );

            if ( $index === false ) {
                continue;
            }

            $buffer        = ( $buffer << 5 ) | $index;
            $buffer_length += 5;

            if ( $buffer_length >= 8 ) {
                $result       .= chr( ( $buffer >> ( $buffer_length - 8 ) ) & 0xFF );
                $buffer_length -= 8;
            }
        }

        return $result;
    }
}
