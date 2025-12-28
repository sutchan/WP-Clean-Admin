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

// Fix for Elementor\Plugin type
namespace Elementor {
    if ( ! class_exists( 'Plugin' ) ) {
        class Plugin {
            public static $instance = null;

            public static function instance() {
                return new self();
            }

            public $modules;
            public $files_manager;
            public $posts_css_manager;
            public $widgets_manager;
            public $breakpoints;
            public $schemes_manager;
            public $fonts_manager;
            public $dynamic_tags;
            public $icons_manager;

            public function get_modules_manager() {
                return new \stdClass();
            }

            public function revoke_caches() {}
            public function get_version() { return ''; }
            public function get_safe_text() { return ''; }
            public function get_text_direction() { return 'ltr'; }
            public function optimize_icons_loading() { return true; }
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
            public function has_custom_breakpoints() { return false; }
        }
    }
}
