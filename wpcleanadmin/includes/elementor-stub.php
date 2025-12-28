<?php
/**
 * Elementor Stub for Intelephense
 *
 * This file provides stub declarations for Elementor classes
 * to enable proper IDE auto-completion and error checking.
 *
 * @package WPCleanAdmin
 * @version 1.7.15
 */

namespace Elementor {
    if ( ! class_exists( 'Plugin' ) ) {
        class Plugin {
            public static $instance = null;

            public static function instance() {
                return null;
            }

            public $modules = null;

            public function get_modules_manager() {
                return null;
            }

            public $files_manager = null;

            public $breakpoints = null;

            public $schemes_manager = null;

            public $fonts_manager = null;

            public $dynamic_tags = null;

            public $icons_manager = null;

            public function revoke_caches() {}

            public function get_version() {
                return '';
            }

            public function get_safe_text() {
                return '';
            }

            public function get_text_direction() {
                return 'ltr';
            }
        }
    }
}

namespace Elementor\Modules\DynamicTags {
    if ( ! class_exists( 'Module' ) ) {
        class Module {
            public function register_tag( $tag_class ) {}
        }
    }
}

namespace Elementor\Modules\Fonts\Module {
    if ( ! class_exists( 'After_Save_Fonts' ) ) {
        class After_Save_Fonts {
            public function add_collection( $collection_name, $args = array(), $value = array() ) {}
        }
    }
}

namespace Elementor\Core\Breakpoints\Manager {
    if ( ! class_exists( 'Manager' ) ) {
        class Manager {
            public function has_custom_breakpoints() {
                return false;
            }
        }
    }
}
