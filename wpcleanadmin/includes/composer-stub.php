<?php
/**
 * Composer Stub for Intelephense
 *
 * This file provides stub declarations for Composer classes
 * to enable proper IDE auto-completion and error checking.
 *
 * @package WPCleanAdmin
 * @version 1.8.0
 */

namespace Composer {
    if ( ! class_exists( 'InstalledVersions' ) ) {
        class InstalledVersions {
            public static function getInstalledPackages() {
                return array();
            }

            public static function getPackageInfo( $packageName ) {
                return array();
            }

            public static function getVersion( $packageName ) {
                return '';
            }

            public static function getReference( $packageName ) {
                return '';
            }

            public static function isInstalled( $packageName ) {
                return false;
            }

            public static function getRootPackage() {
                return array(
                    'name' => '',
                    'version' => '',
                    'reference' => '',
                );
            }
        }
    }
}

namespace {
    if ( ! defined( 'WPCA_COMPOSER_VENDOR_DIR' ) ) {
        define( 'WPCA_COMPOSER_VENDOR_DIR', '' );
    }

    if ( ! defined( 'WP_CONTENT_DIR' ) ) {
        define( 'WP_CONTENT_DIR', '' );
    }

    if ( ! defined( 'WPCA_COMPOSER_AUTOLOADER' ) ) {
        define( 'WPCA_COMPOSER_AUTOLOADER', '' );
    }

    if ( ! defined( 'WPCA_COMPOSER_BIN_DIR' ) ) {
        define( 'WPCA_COMPOSER_BIN_DIR', '' );
    }

    if ( ! defined( 'COMPOSER_VERSION' ) ) {
        define( 'COMPOSER_VERSION', '' );
    }
}

