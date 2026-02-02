<?php
/**
 * WPCleanAdmin Settings Scripts Class
 *
 * @package WPCleanAdmin\Modules\Admin\Settings
 * @version 1.8.0
 * @author Sut
 * @author URI: https://github.com/sutchan
 * @since 1.7.15
 */

namespace WPCleanAdmin\Modules\Admin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Settings_Scripts {
    
    private static $instance = null;
    
    public static function getInstance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {}
    
    public function render_scripts() {
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
        
        <style type="text/css">
            .wpca-settings-wrap {
                max-width: 1200px;
                margin: 0 auto;
            }
            
            .wpca-settings-tabs {
                margin: 20px 0;
                background: #fff;
                border: 1px solid #e1e1e1;
                border-radius: 8px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                overflow: hidden;
            }
            
            .wpca-tabs-nav {
                display: flex;
                flex-wrap: wrap;
                background: #f8f9fa;
                border-bottom: 1px solid #e1e1e1;
                padding: 0;
                margin: 0;
            }
            
            .wpca-tab-button {
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 12px 20px;
                background: transparent;
                border: none;
                border-bottom: 3px solid transparent;
                cursor: pointer;
                transition: all 0.3s ease;
                font-size: 14px;
                font-weight: 500;
                color: #555;
            }
            
            .wpca-tab-button:hover {
                background: rgba(0, 124, 186, 0.05);
                color: #007cba;
            }
            
            .wpca-tab-button.active {
                background: #fff;
                color: #007cba;
                border-bottom-color: #007cba;
            }
            
            .wpca-tab-button .dashicons {
                font-size: 16px;
                width: 16px;
                height: 16px;
            }
            
            .wpca-tabs-content {
                padding: 20px;
            }
            
            .wpca-tab-content {
                display: none;
            }
            
            .wpca-tab-content.active {
                display: block;
                animation: wpca-fade-in 0.3s ease;
            }
            
            .wpca-settings-section {
                margin-bottom: 20px;
                padding: 20px;
                background: #f8f9fa;
                border: 1px solid #e1e1e1;
                border-radius: 6px;
                transition: all 0.3s ease;
            }
            
            .wpca-settings-section:hover {
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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