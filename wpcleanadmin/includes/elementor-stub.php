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

namespace {
    if ( ! class_exists( '\Elementor\Plugin' ) ) {
        class Elementor_Plugin_Stub {
            public static $instance = null;

            public static function instance() {
                return new self();
            }

            public $modules;

            public function get_modules_manager() {
                return new \stdClass();
            }

            public $files_manager;

            public $breakpoints;

            public $schemes_manager;

            public $fonts_manager;

            public $dynamic_tags;

            public $icons_manager;

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

            public function optimize_icons_loading() {
                return true;
            }
        }
    }

    if ( ! class_exists( '\Elementor\Modules\DynamicTags\Module' ) ) {
        class Elementor_DynamicTags_Module_Stub {
            public function register_tag( $tag_class ) {}
        }
    }

    if ( ! class_exists( '\Elementor\Modules\Fonts\Module\After_Save_Fonts' ) ) {
        class Elementor_Fonts_After_Save_Fonts_Stub {
            public function add_collection( $collection_name, $args = array(), $value = array() ) {}
        }
    }

    if ( ! class_exists( '\Elementor\Core\Breakpoints\Manager' ) ) {
        class Elementor_Breakpoints_Manager_Stub {
            public function has_custom_breakpoints() {
                return false;
            }
        }
    }
}

namespace Elementor\Plugin {
    if ( ! class_exists( __NAMESPACE__ . '\Plugin' ) ) {
        class Plugin {
            public static $instance = null;

            public static function instance() {
                return new self();
            }

            public $modules;

            public function get_modules_manager() {
                return new \stdClass();
            }

            public $files_manager;

            public $breakpoints;

            public $schemes_manager;

            public $fonts_manager;

            public $dynamic_tags;

            public $icons_manager;

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

            public function optimize_icons_loading() {
                return true;
            }
        }
    }
}

namespace Elementor\Modules\DynamicTags {
    if ( ! class_exists( __NAMESPACE__ . '\Module' ) ) {
        class Module {
            public function register_tag( $tag_class ) {}
        }
    }
}

namespace Elementor\Modules\Fonts\Module {
    if ( ! class_exists( __NAMESPACE__ . '\After_Save_Fonts' ) ) {
        class After_Save_Fonts {
            public function add_collection( $collection_name, $args = array(), $value = array() ) {}
        }
    }
}

namespace Elementor\Core\Breakpoints\Manager {
    if ( ! class_exists( __NAMESPACE__ . '\Manager' ) ) {
        class Manager {
            public function has_custom_breakpoints() {
                return false;
            }
        }
    }
}
