<?php
/**
 * WPCleanAdmin Cleanup Helpers
 *
 * 共享的清理辅助方法（参数解析、附件删除包装），
 * 被 Content/Comments/Media Cleanup 类复用，避免重复。
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
 * 清理辅助 trait
 */
trait Cleanup_Helpers {

    /**
     * Wrapper for wp_parse_args function
     *
     * @param array|string $args Arguments to parse
     * @param array $defaults Default values
     * @return array Parsed arguments
     */
    protected function wp_parse_args( $args, $defaults ) {
        if ( function_exists( '\wp_parse_args' ) ) {
            return \wp_parse_args( $args, $defaults );
        }
        return array_merge( $defaults, (array) $args );
    }

    /**
     * Wrapper for wp_delete_attachment function
     *
     * @param int $post_id Post ID
     * @param bool $force_delete Force delete
     * @return mixed Deleted post or false
     */
    protected function wp_delete_attachment( $post_id, $force_delete = false ) {
        if ( function_exists( 'wp_delete_attachment' ) ) {
            return \wp_delete_attachment( $post_id, $force_delete );
        }
        return false;
    }
}
