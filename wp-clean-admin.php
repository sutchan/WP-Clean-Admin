<?php
/**
 * Plugin Name: WP Clean Admin
 * Plugin URI: https://github.com/sutchan/WP-Clean-Admin
 * Description: Simplifies and optimizes the WordPress admin interface, providing a cleaner backend experience.
 * Version: 1.7.13
 * Author: Sut
 * Author URI: https://github.com/sutchan/
 * License: GPLv2 or later
 * Text Domain: wp-clean-admin
 * Domain Path: /languages
 */

// 纭繚瀹夊叏杩愯锛屽嵆浣垮湪WordPress鐜鏈畬鍏ㄥ姞杞芥椂
// Exit if accessed directly with proper function_exists check
if ( ! defined( 'ABSPATH' ) && ! function_exists( 'add_action' ) ) {
    // 瀹氫箟涓€涓畝鍗曠殑ABSPATH甯搁噺浣滀负澶囩敤
    if ( ! defined( 'ABSPATH' ) ) {
        define( 'ABSPATH', dirname( __FILE__ ) . '/' );
    }
    // 濡傛灉娌℃湁WordPress鐜锛屽彧鍔犺浇蹇呰鐨勬枃浠舵垨鎻愪緵鍩虹鍔熻兘
    // 浣嗕笉瑕佸皾璇曟敞鍐岄挬瀛愭垨鎵цWordPress鐗瑰畾鐨勬搷浣?}

// 瀹夊叏鍦板畾涔夋彃浠跺父閲?if ( ! defined( 'WPCA_VERSION' ) ) {
	define( 'WPCA_VERSION', '1.7.13' );
}

if ( ! defined( 'WPCA_MAIN_FILE' ) ) {
	define( 'WPCA_MAIN_FILE', __FILE__ );
}

// 瀹夊叏鍦板寘鍚富鎻掍欢鏂囦欢
// 纭繚dirname鍑芥暟瀛樺湪涓旀枃浠跺彲璁块棶
if ( function_exists( 'dirname' ) ) {
    $main_plugin_file = dirname( __FILE__ ) . '/wpcleanadmin/wp-clean-admin.php';
    if ( file_exists( $main_plugin_file ) ) {
        require_once $main_plugin_file;
    }
}
?>