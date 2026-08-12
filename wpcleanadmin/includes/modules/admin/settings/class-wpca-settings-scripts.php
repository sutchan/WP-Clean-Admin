<?php
/**
 * WPCleanAdmin Settings Scripts Class
 *
 * @package WPCleanAdmin\Modules\Admin\Settings
 * @version  1.8.4
 * @author Sut
 * @author URI: https://github.com/Tanox
 * @since 1.7.15
 */

require_once __DIR__ . '/class-wpca-settings-styles.php';

namespace WPCleanAdmin\Modules\Admin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'json_encode' ) ) {
    function json_encode() {}
}

/**
 * Settings Scripts class (renders JS + delegates CSS to Settings_Styles)
 */
class Settings_Scripts {

    /**
     * 内联样式渲染器
     *
     * @var Settings_Styles
     */
    private $styles;

    private static $instance = null;

    public static function getInstance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->styles = new Settings_Styles();
    }

    /**
     * Render settings page scripts and styles
     */
    public function render_scripts() {
        // Render inline CSS via dedicated styles class
        $this->styles->render();

        $text_domain = defined( 'WPCA_TEXT_DOMAIN' ) ? WPCA_TEXT_DOMAIN : 'wp-clean-admin';
        ?>
        <script type="text/javascript">
        (function($) {
            'use strict';

            $(document).ready(function() {
                // Settings tabs functionality
                const tabButtons = $('.wpca-tab-button');
                const tabContents = $('.wpca-tab-content');

                tabButtons.on('click', function() {
                    const tabId = $(this).data('tab');

                    // Remove active class from all tabs
                    tabButtons.removeClass('active');
                    tabContents.removeClass('active');

                    // Add active class to selected tab
                    $(this).addClass('active');
                    $(`#wpca-tab-${tabId}`).addClass('active');

                    // Scroll to top of settings
                    $('html, body').animate({
                        scrollTop: $('.wpca-settings-form').offset().top - 20
                    }, 300);
                });

                // Form submission handling
                $('#wpca-settings-form').on('submit', function(e) {
                    // Show saving message
                    $('#wpca-save-message').html('<span class="wpca-saving">' + <?php echo \json_encode( \__( 'Saving...', $text_domain ) ); ?> + '</span>');
                });

                // Add toggle functionality to setting sections
                $('.wpca-settings-section h3').on('click', function() {
                    const section = $(this).closest('.wpca-settings-section');
                    const content = section.nextUntil('.wpca-settings-section');

                    section.toggleClass('collapsed');
                    content.slideToggle();
                });
            });
        })(jQuery);
        </script>
        <?php
    }

    /**
     * Render styles (delegated to Settings_Styles)
     */
    public function render_styles() {
        $this->styles->render();
    }
}
