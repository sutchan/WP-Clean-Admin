<?php
/**
 * WPCleanAdmin Settings Page
 *
 * 承载设置页面（Tab 布局 HTML）的渲染，从 Settings 主类抽取。
 *
 * @package WPCleanAdmin\Modules\Admin\Settings
 * @version  1.8.4
 * @author Sut
 * @author URI: https://github.com/Tanox
 * @since 1.7.15
 */

namespace WPCleanAdmin\Modules\Admin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 设置页面渲染类
 */
class Settings_Page {

    /**
     * Render the full settings page (tab layout + submit + scripts).
     */
    public function render(): void {
        $text_domain = defined( 'WPCA_TEXT_DOMAIN' ) ? WPCA_TEXT_DOMAIN : 'wp-clean-admin';
        ?>
        <div class="wrap wpca-settings-wrap">
            <h1><?php echo \esc_html( ( function_exists( 'get_admin_page_title' ) ? \get_admin_page_title() : 'WP Clean Admin' ) ); ?></h1>

            <form method="post" action="options.php" id="wpca-settings-form">
                <?php
                if ( function_exists( 'settings_fields' ) ) {
                    \settings_fields( 'wp-clean-admin' );
                }
                ?>

                <!-- Settings Tabs -->
                <div class="wpca-settings-tabs">
                    <div class="wpca-tabs-nav">
                        <button type="button" class="wpca-tab-button active" data-tab="general">
                            <span class="dashicons dashicons-admin-generic"></span>
                            <?php echo \esc_html( \__( 'General', $text_domain ) ); ?>
                        </button>
                        <button type="button" class="wpca-tab-button" data-tab="cleanup">
                            <span class="dashicons dashicons-clipboard"></span>
                            <?php echo \esc_html( \__( 'Cleanup', $text_domain ) ); ?>
                        </button>
                        <button type="button" class="wpca-tab-button" data-tab="performance">
                            <span class="dashicons dashicons-chart-line"></span>
                            <?php echo \esc_html( \__( 'Performance', $text_domain ) ); ?>
                        </button>
                        <button type="button" class="wpca-tab-button" data-tab="security">
                            <span class="dashicons dashicons-shield"></span>
                            <?php echo \esc_html( \__( 'Security', $text_domain ) ); ?>
                        </button>
                    </div>

                    <div class="wpca-tabs-content">
                        <!-- General Tab -->
                        <div class="wpca-tab-content active" id="wpca-tab-general">
                            <?php
                            if ( function_exists( 'do_settings_sections' ) ) {
                                \do_settings_sections( 'wp-clean-admin' );
                            }
                            ?>
                        </div>

                        <!-- Cleanup Tab -->
                        <div class="wpca-tab-content" id="wpca-tab-cleanup">
                            <?php
                            if ( function_exists( 'do_settings_sections' ) ) {
                                \do_settings_sections( 'wp-clean-admin' );
                            }
                            ?>
                        </div>

                        <!-- Performance Tab -->
                        <div class="wpca-tab-content" id="wpca-tab-performance">
                            <?php
                            if ( function_exists( 'do_settings_sections' ) ) {
                                \do_settings_sections( 'wp-clean-admin' );
                            }
                            ?>
                        </div>

                        <!-- Security Tab -->
                        <div class="wpca-tab-content" id="wpca-tab-security">
                            <?php
                            if ( function_exists( 'do_settings_sections' ) ) {
                                \do_settings_sections( 'wp-clean-admin' );
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="wpca-settings-submit">
                    <?php
                    if ( function_exists( 'submit_button' ) ) {
                        \submit_button( \__( 'Save Changes', $text_domain ), 'primary', 'submit', false, array( 'id' => 'wpca-save-button' ) );
                    }
                    ?>
                    <div class="wpca-save-message" id="wpca-save-message"></div>
                </div>
            </form>
        </div>

        <?php
        // Include scripts
        if ( file_exists( dirname( __FILE__ ) . '/class-wpca-settings-scripts.php' ) ) {
            require_once dirname( __FILE__ ) . '/class-wpca-settings-scripts.php';
        }

        // Render scripts using the scripts class
        if ( class_exists( 'WPCleanAdmin\Modules\Admin\Settings\Settings_Scripts' ) ) {
            $scripts = \WPCleanAdmin\Modules\Admin\Settings\Settings_Scripts::getInstance();
            $scripts->render_scripts();
        }
    }
}
