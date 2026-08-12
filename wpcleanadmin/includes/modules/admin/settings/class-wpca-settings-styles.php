<?php
/**
 * WPCleanAdmin Settings Styles
 *
 * 承载设置页面内联样式（CSS），从 Settings_Scripts 主类抽取。
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
 * 设置页面内联样式类
 */
class Settings_Styles {

    /**
     * Render the inline CSS for the settings page.
     */
    public function render(): void {
        ?>
        <style type="text/css">
            /* Settings page layout */
            .wpca-settings-wrap {
                margin: 20px;
                padding: 20px;
                background: #fff;
                border-radius: 8px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            }

            .wpca-settings-tabs {
                margin: 20px 0;
            }

            .wpca-tabs-nav {
                display: flex;
                gap: 10px;
                border-bottom: 1px solid #e1e1e1;
                padding-bottom: 10px;
                margin-bottom: 20px;
            }

            .wpca-tab-button {
                padding: 8px 16px;
                border: 1px solid #e1e1e1;
                background: #f8f9fa;
                border-radius: 4px;
                cursor: pointer;
                display: flex;
                align-items: center;
                gap: 5px;
                transition: all 0.3s ease;
            }

            .wpca-tab-button:hover {
                background: #fff;
                border-color: #007cba;
            }

            .wpca-tab-button.active {
                background: #007cba;
                color: #fff;
                border-color: #007cba;
            }

            .wpca-tab-content {
                display: none;
                animation: wpca-fade-in 0.3s ease;
            }

            .wpca-tab-content.active {
                display: block;
            }

            .wpca-settings-section {
                margin-bottom: 20px;
                border: 1px solid #e1e1e1;
                border-radius: 4px;
                overflow: hidden;
            }

            .wpca-settings-section h3 {
                margin: 0 0 15px;
                padding: 0;
                font-size: 16px;
                font-weight: 600;
                color: #333;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }

            .wpca-settings-section h3:after {
                content: '▼';
                font-size: 12px;
                color: #666;
                transition: transform 0.3s ease;
            }

            .wpca-settings-section.collapsed h3:after {
                transform: rotate(-90deg);
            }

            .wpca-settings-section .form-table {
                margin: 0;
                background: #fff;
                border: 1px solid #e1e1e1;
                border-radius: 4px;
                overflow: hidden;
            }

            .wpca-settings-section .form-table tr {
                border-bottom: 1px solid #f0f0f0;
                transition: background-color 0.2s ease;
            }

            .wpca-settings-section .form-table tr:last-child {
                border-bottom: none;
            }

            .wpca-settings-section .form-table tr:hover {
                background-color: #f8f9fa;
            }

            .wpca-settings-section .form-table th {
                padding: 12px 15px;
                width: 300px;
                font-weight: 500;
                color: #333;
                background: #fafafa;
                border-right: 1px solid #f0f0f0;
            }

            .wpca-settings-section .form-table td {
                padding: 12px 15px;
                color: #555;
            }

            .wpca-settings-submit {
                margin: 30px 0;
                padding: 20px;
                background: #f8f9fa;
                border: 1px solid #e1e1e1;
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            }

            #wpca-save-button {
                font-size: 14px;
                padding: 8px 20px;
                font-weight: 500;
            }

            .wpca-save-message {
                font-size: 14px;
            }

            .wpca-saving {
                color: #007cba;
                font-weight: 500;
            }

            /* Responsive Design */
            @media screen and (max-width: 782px) {
                .wpca-settings-wrap {
                    padding: 0 10px;
                }

                .wpca-tabs-nav {
                    flex-direction: column;
                }

                .wpca-tab-button {
                    justify-content: flex-start;
                    border-bottom: 1px solid #e1e1e1;
                }

                .wpca-tab-button.active {
                    border-bottom: 1px solid #e1e1e1;
                    border-left: 3px solid #007cba;
                }

                .wpca-settings-section .form-table {
                    display: block;
                }

                .wpca-settings-section .form-table tr {
                    display: block;
                    border-bottom: 1px solid #f0f0f0;
                }

                .wpca-settings-section .form-table th,
                .wpca-settings-section .form-table td {
                    display: block;
                    width: 100%;
                    border-right: none;
                    border-bottom: 1px solid #f0f0f0;
                }

                .wpca-settings-section .form-table th {
                    background: #fafafa;
                    padding-bottom: 8px;
                }

                .wpca-settings-section .form-table td {
                    padding-top: 8px;
                    padding-bottom: 12px;
                }

                .wpca-settings-submit {
                    flex-direction: column;
                    gap: 15px;
                    align-items: stretch;
                }

                #wpca-save-button {
                    width: 100%;
                    text-align: center;
                }
            }

            /* Animation */
            @keyframes wpca-fade-in {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        </style>
        <?php
    }
}
