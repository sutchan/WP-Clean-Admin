<?php
/**
 * WP Clean Admin - AJAX Handler Class
 * Handles AJAX requests for the plugin
 */

/**
 * AJAX handler class for WP Clean Admin
 */
class WPCA_Ajax {
    
    /**
     * 保存登录页面设置
     */
    public function save_login_settings() {
        // 验证请求
        if ( ! $this->validate_ajax_request( 'wpca_save_login_settings' ) ) {
            return;
        }
        
        // 获取并验证设置数据
        if ( ! isset( $_POST['login_settings'] ) || ! is_array( $_POST['login_settings'] ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid data structure', 'wp-clean-admin' ) ), 400 );
            return;
        }
        
        $login_settings = $_POST['login_settings'];
        $sanitized_settings = array();
        
        // 清理设置数据
        if ( isset( $login_settings['login_style'] ) ) {
            $sanitized_settings['login_style'] = sanitize_text_field( $login_settings['login_style'] );
        }
        
        if ( isset( $login_settings['login_logo_url'] ) ) {
            $sanitized_settings['login_logo_url'] = esc_url_raw( $login_settings['login_logo_url'] );
        }
        
        if ( isset( $login_settings['login_background_url'] ) ) {
            $sanitized_settings['login_background_url'] = esc_url_raw( $login_settings['login_background_url'] );
        }
        
        if ( isset( $login_settings['login_custom_css'] ) ) {
            $sanitized_settings['login_custom_css'] = wp_kses_post( $login_settings['login_custom_css'] );
        }
        
        if ( isset( $login_settings['login_elements'] ) && is_array( $login_settings['login_elements'] ) ) {
            $sanitized_settings['login_elements'] = array_map( 'intval', $login_settings['login_elements'] );
        }
        
        // 获取现有选项并合并
        if ( class_exists( 'WPCA_Settings' ) && method_exists( 'WPCA_Settings', 'get_options' ) ) {
            $options = WPCA_Settings::get_options();
            $updated_options = array_merge( $options, $sanitized_settings );
            
            // 保存更新后的设置
            if ( method_exists( 'WPCA_Settings', 'update_options' ) ) {
                if ( WPCA_Settings::update_options( $updated_options ) ) {
                    wp_send_json_success( array( 'message' => __( 'Login settings saved successfully', 'wp-clean-admin' ) ) );
                } else {
                    wp_send_json_error( array( 'message' => __( 'Failed to save login settings', 'wp-clean-admin' ) ) );
                }
            } else {
                wp_send_json_error( array( 'message' => __( 'Settings update method not available', 'wp-clean-admin' ) ) );
            }
        } else {
            wp_send_json_error( array( 'message' => __( 'Settings class not available', 'wp-clean-admin' ) ) );
        }
    }
    
    /**
     * 获取登录页面设置
     */
    public function get_login_settings() {
        // 验证请求
        if ( ! $this->validate_ajax_request( 'wpca_get_login_settings' ) ) {
            return;
        }
        
        // 获取设置
        if ( class_exists( 'WPCA_Settings' ) && method_exists( 'WPCA_Settings', 'get_options' ) ) {
            $options = WPCA_Settings::get_options();
            
            // 提取登录相关设置
            $login_settings = array(
                'login_style' => $options['login_style'] ?? 'default',
                'login_logo_url' => $options['login_logo_url'] ?? '',
                'login_background_url' => $options['login_background_url'] ?? '',
                'login_custom_css' => $options['login_custom_css'] ?? '',
                'login_elements' => $options['login_elements'] ?? array(
                    'language_switcher' => 1,
                    'home_link' => 1,
                    'register_link' => 1,
                    'remember_me' => 1
                )
            );
            
            wp_send_json_success( array( 'login_settings' => $login_settings ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Settings class not available', 'wp-clean-admin' ) ) );
        }
    }
    
    /**
     * 重置登录页面设置
     */
    public function reset_login_settings() {
        // 验证请求
        if ( ! $this->validate_ajax_request( 'wpca_reset_login_settings' ) ) {
            return;
        }
        
        // 获取现有选项并移除登录相关设置
        if ( class_exists( 'WPCA_Settings' ) && method_exists( 'WPCA_Settings', 'get_options' ) ) {
            $options = WPCA_Settings::get_options();
            
            // 删除登录相关设置
            $login_settings_keys = array(
                'login_style',
                'login_logo_url',
                'login_background_url',
                'login_custom_css',
                'login_elements'
            );
            
            foreach ( $login_settings_keys as $key ) {
                if ( isset( $options[ $key ] ) ) {
                    unset( $options[ $key ] );
                }
            }
            
            // 保存更新后的设置
            if ( method_exists( 'WPCA_Settings', 'update_options' ) ) {
                if ( WPCA_Settings::update_options( $options ) ) {
                    wp_send_json_success( array( 'message' => __( 'Login settings reset successfully', 'wp-clean-admin' ) ) );
                } else {
                    wp_send_json_error( array( 'message' => __( 'Failed to reset login settings', 'wp-clean-admin' ) ) );
                }
            } else {
                wp_send_json_error( array( 'message' => __( 'Settings update method not available', 'wp-clean-admin' ) ) );
            }
        } else {
            wp_send_json_error( array( 'message' => __( 'Settings class not available', 'wp-clean-admin' ) ) );
        }
    }
    
    /**
     * Constructor
     */
    public function __construct() {
        // Initialize hooks
        $this->init_hooks();
    }
    
    /**
     * Initialize AJAX hooks
     */
    public function init_hooks() {
        if ( function_exists( 'add_action' ) ) {
            // Public AJAX actions (available to both logged in and non-logged in users)
            add_action( 'wp_ajax_nopriv_wpca_get_public_data', array( $this, 'get_public_data' ) );
            
            // Admin AJAX actions (only available to logged in users with proper permissions)
            add_action( 'wp_ajax_wpca_toggle_menu', array( $this, 'toggle_menu' ) );
            add_action( 'wp_ajax_wpca_update_menu_order', array( $this, 'update_menu_order' ) );
            add_action( 'wp_ajax_wpca_reset_menu', array( $this, 'reset_menu' ) ); // 添加重置菜单方法
            add_action( 'wp_ajax_wpca_reset_menu_order', array( $this, 'reset_menu_order' ) ); // 添加重置菜单顺序方法
            add_action( 'wp_ajax_wpca_reset_settings', array( $this, 'reset_settings' ) );
            add_action( 'wp_ajax_wpca_save_settings', array( $this, 'save_settings' ) );
            add_action( 'wp_ajax_wpca_get_settings', array( $this, 'get_settings' ) );
            add_action( 'wp_ajax_wpca_update_dashboard_widgets', array( $this, 'update_dashboard_widgets' ) );
            // 登录页面自定义相关AJAX操作
            add_action( 'wp_ajax_wpca_save_login_settings', array( $this, 'save_login_settings' ) );
            add_action( 'wp_ajax_wpca_get_login_settings', array( $this, 'get_login_settings' ) );
            add_action( 'wp_ajax_wpca_reset_login_settings', array( $this, 'reset_login_settings' ) );
        }
    }
    
    /**
     * Validate AJAX request
     * @param string $action - The action name to validate
     * @return bool - True if valid, false otherwise
     */
    protected function validate_ajax_request( $action ) {
        // 确保action参数有效
        if ( ! is_string( $action ) || empty( $action ) ) {
            if ( function_exists( 'wp_send_json_error' ) ) {
                wp_send_json_error( array( 'message' => 'Invalid validation action' ), 400 );
            }
            return false;
        }
        
        // 检查是否是AJAX请求，兼容旧版本WordPress
        if ( ( function_exists( 'wp_doing_ajax' ) && ! wp_doing_ajax() ) || 
             ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
            if ( function_exists( 'wp_send_json_error' ) ) {
                wp_send_json_error( array( 'message' => 'Invalid request' ), 400 );
            }
            return false;
        }
        
        // 验证nonce，使用传入的action参数
        if ( ! isset( $_POST['nonce'] ) || 
             ( function_exists( 'wp_verify_nonce' ) && ! wp_verify_nonce( $_POST['nonce'], $action ) ) ) {
            if ( function_exists( 'wp_send_json_error' ) ) {
                wp_send_json_error( array( 'message' => 'Invalid nonce' ), 403 );
            }
            return false;
        }
        
        // 检查用户权限，优先使用WPCA_Permissions类的方法
        if ( class_exists( 'WPCA_Permissions' ) && method_exists( 'WPCA_Permissions', 'current_user_can' ) ) {
            // 使用类的权限检查方法，确保用户有管理选项权限
            if ( ! WPCA_Permissions::current_user_can( 'manage_options' ) ) {
                if ( function_exists( 'wp_send_json_error' ) ) {
                    wp_send_json_error( array( 'message' => 'Insufficient permissions' ), 403 );
                }
                return false;
            }
        } else if ( function_exists( 'current_user_can' ) ) {
            // 后备方案：使用WordPress原生函数检查权限
            if ( ! current_user_can( 'manage_options' ) ) {
                if ( function_exists( 'wp_send_json_error' ) ) {
                    wp_send_json_error( array( 'message' => 'Insufficient permissions' ), 403 );
                }
                return false;
            }
        } else {
            // 如果没有权限检查函数，拒绝请求以确保安全
            if ( function_exists( 'wp_send_json_error' ) ) {
                wp_send_json_error( array( 'message' => 'Permission check unavailable' ), 500 );
            }
            return false;
        }
        
        return true;
    }
    
    /**
 * Toggle menu visibility
 */
    public function toggle_menu() {
        try {
            // 增强的AJAX请求验证
            if ( ! $this->validate_ajax_request( 'wpca_admin_nonce' ) ) {
                // 如果验证失败，确保结束请求
                if ( function_exists( 'wp_die' ) ) {
                    wp_die();
                }
                return;
            }
            
            // 严格的参数验证
            if ( ! isset( $_POST['slug'] ) || ! is_string( $_POST['slug'] ) || empty( $_POST['slug'] ) || ! isset( $_POST['state'] ) ) {
                throw new Exception('Missing or invalid required parameters', 'missing_parameters');
            }
            
            // 更严格的数据清理
            $slug = function_exists( 'sanitize_text_field' ) ? sanitize_text_field( $_POST['slug'] ) : filter_var( $_POST['slug'], FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES );
            $state = intval( $_POST['state'] );
            
            // 安全验证menu slug
            if ( empty( $slug ) ) {
                throw new Exception('Invalid menu slug', 'invalid_slug');
            }
            
            // 增强的格式验证（防止注入攻击）
            if ( ! preg_match( '/^[a-zA-Z0-9_\-]+$/', $slug ) ) {
                throw new Exception('Invalid menu slug format', 'invalid_slug_format');
            }
            
            // 获取当前设置，检查必要函数是否可用
            if ( ! function_exists( 'get_option' ) ) {
                throw new Exception('Required WordPress functions not available', 'functions_unavailable');
            }
            
            $settings = get_option( 'wpca_settings', array() );
            
            // 确保数组结构正确 - 使用menu_toggles与WPCA_Menu_Customizer类保持一致
            if ( ! isset( $settings['menu_toggles'] ) || ! is_array( $settings['menu_toggles'] ) ) {
                $settings['menu_toggles'] = array();
            }
            
            // 更新菜单可见性
            $settings['menu_toggles'][$slug] = $state;
            
            // 保存更新后的设置
            if ( ! function_exists( 'update_option' ) ) {
                throw new Exception('Required WordPress functions not available', 'functions_unavailable');
            }
            
            $save_result = update_option( 'wpca_settings', $settings );
            
            // 清除缓存
            if ( function_exists( 'wp_cache_delete' ) ) {
                wp_cache_delete( 'wpca_settings', 'options' );
            }
            
            // 记录成功更新
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG && function_exists( 'error_log' ) ) {
                error_log( 'WP Clean Admin - Menu item "' . $slug . '" visibility toggled to ' . ( $state === 0 ? 'hidden' : 'visible' ) );
            }
            
            if ( ! $save_result ) {
                throw new Exception('Failed to update menu visibility', 'update_failed');
            }
            
            // 返回成功响应
            if ( function_exists( 'wp_send_json_success' ) ) {
                wp_send_json_success( array( 
                    'message' => 'Menu visibility updated',
                    'slug' => $slug,
                    'state' => $state
                ) );
            }
        } catch ( Exception $e ) {
            $code = method_exists( $e, 'getCode' ) && $e->getCode() ? $e->getCode() : 'toggle_menu_error';
            $message = $e->getMessage();
            
            // 记录错误
            if ( function_exists( 'error_log' ) ) {
                error_log( 'WP Clean Admin - Toggle Menu Error (' . $code . '): ' . $message );
            }
            
            // 发送错误响应
            if ( function_exists( 'wp_send_json_error' ) ) {
                wp_send_json_error( array( 
                    'code' => $code,
                    'message' => $message
                ), 400 );
            }
        }
        
        // 确保请求被正确终止
        if ( function_exists( 'wp_die' ) ) {
            wp_die();
        }
    }
    
    /**
     * Update menu order
     */
    public function update_menu_order() {
        try {
            // 增强的AJAX请求验证
            if ( ! $this->validate_ajax_request( 'wpca_admin_nonce' ) ) {
                if ( function_exists( 'wp_die' ) ) {
                    wp_die();
                }
                return;
            }
            
            // 严格的输入验证
            if ( ! isset( $_POST['menu_order'] ) ) {
                if ( function_exists( 'wp_send_json_error' ) ) {
                    wp_send_json_error( array( 'message' => 'Missing menu order data' ) );
                }
                wp_die();
            }
            
            // Get and sanitize menu order data
            $menu_order = ( function_exists( 'json_decode' ) && function_exists( 'stripslashes' ) ) ? 
                json_decode( stripslashes( $_POST['menu_order'] ), true ) : array();
            
            if ( ! is_array( $menu_order ) ) {
                if ( function_exists( 'wp_send_json_error' ) ) {
                    wp_send_json_error( array( 'message' => 'Invalid menu order data format' ) );
                }
                wp_die();
            }
            
            // 验证数组不为空
            if ( empty( $menu_order ) ) {
                if ( function_exists( 'wp_send_json_error' ) ) {
                    wp_send_json_error( array( 'message' => 'Empty menu order array' ) );
                }
                wp_die();
            }
            
            // 安全地清理每个菜单项
            $sanitized_order = array();
            foreach ( $menu_order as $slug ) {
                // 验证每个元素是字符串
                if ( ! is_string( $slug ) || empty( $slug ) ) {
                    continue; // 跳过无效条目
                }
                
                $sanitized = function_exists( 'sanitize_text_field' ) ? sanitize_text_field( $slug ) : filter_var( $slug, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES );
                // 确保清理后的值不为空
                if ( ! empty( $sanitized ) ) {
                    $sanitized_order[] = $sanitized;
                }
            }
            
            // 检查清理后数组是否为空
            if ( empty( $sanitized_order ) ) {
                if ( function_exists( 'wp_send_json_error' ) ) {
                    wp_send_json_error( array( 'message' => 'No valid menu items provided' ) );
                }
                wp_die();
            }
            
            // 检查函数是否存在
            if ( ! function_exists( 'get_option' ) || ! function_exists( 'update_option' ) ) {
                if ( function_exists( 'wp_send_json_error' ) ) {
                    wp_send_json_error( array( 'message' => 'Required WordPress functions not available' ) );
                }
                wp_die();
            }
            
            // Get current settings
            $settings = get_option( 'wpca_settings', array() );
            
            // Update menu order
            $settings['menu_order'] = $sanitized_order;
            
            // Save updated settings
            $success = update_option( 'wpca_settings', $settings );
            
            // 清除缓存
            if ( function_exists( 'wp_cache_delete' ) ) {
                wp_cache_delete( 'wpca_settings', 'options' );
            }
            
            if ( $success ) {
                if ( function_exists( 'wp_send_json_success' ) ) {
                    wp_send_json_success( array( 'message' => 'Menu order updated' ) );
                }
            } else {
                if ( function_exists( 'wp_send_json_error' ) ) {
                    wp_send_json_error( array( 'message' => 'Failed to update settings' ) );
                }
            }
        } catch ( Exception $e ) {
            // 改进的错误处理
            if ( function_exists( 'error_log' ) ) {
                error_log( 'WPCA Error in update_menu_order: ' . $e->getMessage() );
            }
            
            if ( function_exists( 'wp_send_json_error' ) ) {
                wp_send_json_error( array( 'message' => 'An unexpected error occurred' ) );
            }
        }
        
        // 确保请求终止
        if ( function_exists( 'wp_die' ) ) {
            wp_die();
        }
    }
    
    /**
     * Reset settings to default
     */
    public function reset_settings() {
        try {
            // 增强的AJAX请求验证
            if ( ! $this->validate_ajax_request( 'wpca_admin_nonce' ) ) {
                // 如果验证失败，确保结束请求
                if ( function_exists( 'wp_die' ) ) {
                    wp_die();
                }
                return;
            }
            
            // 检查必要函数是否存在
            if ( ! function_exists( 'update_option' ) ) {
                if ( function_exists( 'wp_send_json_error' ) ) {
                    wp_send_json_error( array( 'message' => 'Required WordPress function not available' ) );
                }
                if ( function_exists( 'wp_die' ) ) {
                    wp_die();
                }
                return;
            }
            
            // Define default settings
            $default_settings = array(
                'version'             => WPCA_VERSION,
                'menu_order'          => array(),
                'submenu_order'       => array(),
                'menu_toggles'        => array(),
                'dashboard_widgets'   => array(),
                'login_style'         => 'default',
                'custom_admin_bar'    => 0,
                'disable_help_tabs'   => 0,
                'cleanup_header'      => 0,
                'minify_admin_assets' => 0
            );
            
            // 验证默认设置结构正确
            if ( ! is_array( $default_settings ) ) {
                if ( function_exists( 'wp_send_json_error' ) ) {
                    wp_send_json_error( array( 'message' => 'Invalid default settings structure' ) );
                }
                if ( function_exists( 'wp_die' ) ) {
                    wp_die();
                }
                return;
            }
            
            // Reset settings
            $success = update_option( 'wpca_settings', $default_settings );
            
            if ( $success ) {
                // 清除缓存
                if ( function_exists( 'wp_cache_delete' ) ) {
                    wp_cache_delete( 'wpca_settings', 'options' );
                }
                
                // 记录设置重置操作
                if ( defined( 'WP_DEBUG' ) && WP_DEBUG && function_exists( 'error_log' ) ) {
                    error_log( 'WP Clean Admin - Settings have been reset to defaults' );
                }
                
                if ( function_exists( 'wp_send_json_success' ) ) {
                    wp_send_json_success( array( 'message' => 'Settings reset to default' ) );
                }
            } else {
                if ( function_exists( 'wp_send_json_error' ) ) {
                    wp_send_json_error( array( 'message' => 'Failed to reset settings' ) );
                }
            }
        } catch ( Exception $e ) {
            // 改进的错误处理
            $error_message = $e->getMessage();
            if ( function_exists( 'error_log' ) ) {
                error_log( 'WPCA Error in reset_settings: ' . $error_message );
            }
            
            if ( function_exists( 'wp_send_json_error' ) ) {
                wp_send_json_error( array( 'message' => 'An unexpected error occurred' ) );
            }
        }
        
        // 确保请求终止
        if ( function_exists( 'wp_die' ) ) {
            wp_die();
        }
    }
    
    /**
     * Save plugin settings
     */
    public function save_settings() {
        try {
            // 增强的AJAX请求验证
            if ( ! $this->validate_ajax_request( 'wpca_admin_nonce' ) ) {
                // 如果验证失败，确保结束请求
                if ( function_exists( 'wp_die' ) ) {
                    wp_die();
                }
                return;
            }
            
            // 检查必要函数是否存在
            if ( ! function_exists( 'update_option' ) || ! function_exists( 'get_option' ) ) {
                if ( function_exists( 'wp_send_json_error' ) ) {
                    wp_send_json_error( array( 'message' => 'Required WordPress function not available' ) );
                }
                if ( function_exists( 'wp_die' ) ) {
                    wp_die();
                }
                return;
            }
            
            // 严格的输入验证
            if ( ! isset( $_POST['settings'] ) ) {
                throw new Exception('Missing settings data', 'missing_settings');
            }
            
            // Get and sanitize settings data
            $new_settings = ( function_exists( 'json_decode' ) && function_exists( 'stripslashes' ) ) ? 
                json_decode( stripslashes( $_POST['settings'] ), true ) : array();
            
            if ( ! is_array( $new_settings ) ) {
                throw new Exception('Invalid settings data format', 'invalid_format');
            }
            
            // 获取当前设置
            $current_settings = get_option( 'wpca_settings', array() );
            
            // 定义安全的设置键白名单
            $allowed_settings = array(
                'custom_admin_bar',
                'disable_help_tabs',
                'cleanup_header',
                'minify_admin_assets',
                'login_style',
                'menu_order',
                'submenu_order',
                'menu_toggles',
                'dashboard_widgets'
            );
            
            // 安全处理每个设置项
            $sanitized_settings = array();
            foreach ( $new_settings as $key => $value ) {
                // 仅处理白名单内的设置键
                if ( in_array( $key, $allowed_settings ) ) {
                    // 根据设置类型进行适当的清理
                    if ( $key === 'login_style' ) {
                        // 字符串类型设置
                        $sanitized_settings[$key] = function_exists( 'sanitize_text_field' ) ? 
                            sanitize_text_field( $value ) : filter_var( $value, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES );
                    } else if ( in_array( $key, array( 'menu_order', 'submenu_order' ) ) && is_array( $value ) ) {
                        // 数组类型设置 - 对于简单数组
                        $sanitized_array = array();
                        foreach ( $value as $item ) {
                            if ( is_string( $item ) ) {
                                $sanitized_item = function_exists( 'sanitize_text_field' ) ? 
                                    sanitize_text_field( $item ) : filter_var( $item, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES );
                                if ( ! empty( $sanitized_item ) ) {
                                    $sanitized_array[] = $sanitized_item;
                                }
                            }
                        }
                        $sanitized_settings[$key] = $sanitized_array;
                    } else if ( $key === 'menu_toggles' && is_array( $value ) ) {
                        // 关联数组类型设置 - 对于menu_toggles
                        $sanitized_array = array();
                        foreach ( $value as $slug => $state ) {
                            // 清理键值对
                            if ( is_string( $slug ) && ( $state === 0 || $state === 1 ) ) {
                                $sanitized_slug = function_exists( 'sanitize_text_field' ) ? 
                                    sanitize_text_field( $slug ) : filter_var( $slug, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES );
                                if ( ! empty( $sanitized_slug ) ) {
                                    $sanitized_array[$sanitized_slug] = intval( $state ); // 确保state是整数
                                }
                            }
                        }
                        $sanitized_settings[$key] = $sanitized_array;
                    } else if ( $key === 'dashboard_widgets' && is_array( $value ) ) {
                        // 仪表盘小部件设置
                        $sanitized_widgets = array();
                        foreach ( $value as $widget_id => $is_visible ) {
                            $sanitized_id = function_exists( 'sanitize_text_field' ) ? 
                                sanitize_text_field( $widget_id ) : filter_var( $widget_id, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES );
                            $sanitized_widgets[ $sanitized_id ] = (bool) $is_visible;
                        }
                        $sanitized_settings[$key] = $sanitized_widgets;
                    } else {
                        // 布尔/整数值设置
                        $sanitized_settings[$key] = (int)(bool)$value;
                    }
                }
            }
            
            // 合并清理后的新设置与当前设置
            $merged_settings = array_merge( $current_settings, $sanitized_settings );
            
            // 确保保留版本信息
            $merged_settings['version'] = isset( $current_settings['version'] ) ? $current_settings['version'] : WPCA_VERSION;
            
            // 保存选项
            $success = update_option( 'wpca_settings', $merged_settings );
            
            if ( $success ) {
                // 清除缓存
                if ( function_exists( 'wp_cache_delete' ) ) {
                    wp_cache_delete( 'wpca_settings', 'options' );
                }
                
                // 记录设置保存操作
                if ( defined( 'WP_DEBUG' ) && WP_DEBUG && function_exists( 'error_log' ) ) {
                    error_log( 'WP Clean Admin - Settings have been updated' );
                }
                
                if ( function_exists( 'wp_send_json_success' ) ) {
                    wp_send_json_success( array( 'message' => 'Settings saved successfully' ) );
                }
            } else {
                throw new Exception('Failed to save settings', 'save_failed');
            }
        } catch ( Exception $e ) {
            $code = method_exists( $e, 'getCode' ) && $e->getCode() ? $e->getCode() : 'save_settings_error';
            $message = $e->getMessage();
            
            // 记录错误
            if ( function_exists( 'error_log' ) ) {
                error_log( 'WP Clean Admin - Save Settings Error (' . $code . '): ' . $message );
            }
            
            // 发送错误响应
            if ( function_exists( 'wp_send_json_error' ) ) {
                wp_send_json_error( array( 
                    'code' => $code,
                    'message' => $message
                ), 400 );
            }
        }
        
        // 确保请求终止
        if ( function_exists( 'wp_die' ) ) {
            wp_die();
        }
    }
    
    /**
     * Get current plugin settings
     */
    public function get_settings() {
        try {
            if ( ! $this->validate_ajax_request( 'wpca_admin_nonce' ) ) {
                return;
            }
            
            // 获取当前设置 - 确保函数存在
            if ( ! function_exists( 'get_option' ) ) {
                throw new Exception('Required WordPress function not available', 'functions_missing');
            }
            
            $settings = get_option( 'wpca_settings', array() );
            
            // 确保 $settings 是数组
            if ( ! is_array( $settings ) ) {
                $settings = array();
            }
            
            if ( function_exists( 'wp_send_json_success' ) ) {
                wp_send_json_success( array( 'settings' => $settings ) );
            }
        } catch ( Exception $e ) {
            $code = method_exists( $e, 'getCode' ) && $e->getCode() ? $e->getCode() : 'get_settings_error';
            $message = $e->getMessage();
            
            // 记录错误
            if ( function_exists( 'error_log' ) ) {
                error_log( 'WP Clean Admin - Get Settings Error (' . $code . '): ' . $message );
            }
            
            // 发送错误响应
            if ( function_exists( 'wp_send_json_error' ) ) {
                wp_send_json_error( array( 
                    'code' => $code,
                    'message' => $message
                ), 400 );
            }
        }
        
        // 确保请求终止
        if ( function_exists( 'wp_die' ) ) {
            wp_die();
        }
    }
    
    /**
     * Update dashboard widgets visibility
     */
    public function update_dashboard_widgets() {
        try {
            if ( ! $this->validate_ajax_request( 'wpca_admin_nonce' ) ) {
                return;
            }
            
            // 验证必要函数存在
            if ( ! function_exists( 'update_option' ) || ! function_exists( 'get_option' ) ) {
                throw new Exception('Required WordPress functions not available', 'functions_missing');
            }
            
            if ( ! isset( $_POST['widgets'] ) ) {
                throw new Exception('Missing widgets data', 'missing_widgets');
            }
            
            // 获取和清理小部件数据
            $widgets = ( function_exists( 'json_decode' ) && function_exists( 'stripslashes' ) ) ? 
                json_decode( stripslashes( $_POST['widgets'] ), true ) : array();
            
            if ( ! is_array( $widgets ) ) {
                throw new Exception('Invalid widgets data format', 'invalid_format');
            }
            
            // 清理每个小部件 ID
            $sanitized_widgets = array();
            foreach ( $widgets as $widget_id => $is_visible ) {
                $sanitized_id = function_exists( 'sanitize_text_field' ) ? 
                    sanitize_text_field( $widget_id ) : 
                    filter_var( $widget_id, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES );
                $sanitized_widgets[ $sanitized_id ] = (bool) $is_visible;
            }
            
            // 获取当前设置
            $settings = get_option( 'wpca_settings', array() );
            
            // 确保 $settings 是数组
            if ( ! is_array( $settings ) ) {
                $settings = array();
            }
            
            // 更新仪表盘小部件设置
            $settings['dashboard_widgets'] = $sanitized_widgets;
            
            // 保存更新后的设置
            $success = update_option( 'wpca_settings', $settings );
            
            if ( $success ) {
                // 清除缓存
                if ( function_exists( 'wp_cache_delete' ) ) {
                    wp_cache_delete( 'wpca_settings', 'options' );
                }
                
                // 记录更新操作
                if ( defined( 'WP_DEBUG' ) && WP_DEBUG && function_exists( 'error_log' ) ) {
                    error_log( 'WP Clean Admin - Dashboard widgets visibility has been updated' );
                }
                
                if ( function_exists( 'wp_send_json_success' ) ) {
                    wp_send_json_success( array( 'message' => 'Dashboard widgets updated' ) );
                }
            } else {
                throw new Exception('Failed to update dashboard widgets', 'update_failed');
            }
        } catch ( Exception $e ) {
            $code = method_exists( $e, 'getCode' ) && $e->getCode() ? $e->getCode() : 'update_widgets_error';
            $message = $e->getMessage();
            
            // 记录错误
            if ( function_exists( 'error_log' ) ) {
                error_log( 'WP Clean Admin - Update Widgets Error (' . $code . '): ' . $message );
            }
            
            // 发送错误响应
            if ( function_exists( 'wp_send_json_error' ) ) {
                wp_send_json_error( array( 
                    'code' => $code,
                    'message' => $message
                ), 400 );
            }
        }
        
        // 确保请求终止
        if ( function_exists( 'wp_die' ) ) {
            wp_die();
        }
    }
    
    /**
     * Reset menu to default state
     */
    public function reset_menu() {
        if ( ! $this->validate_ajax_request( 'wpca_admin_nonce' ) ) {
            return;
        }
        
        try {
            // 验证必要函数存在
            if ( ! function_exists( 'update_option' ) || ! function_exists( 'get_option' ) ) {
                throw new Exception('Required WordPress functions not available', 'functions_missing');
            }
            
            // 获取当前设置
            $settings = get_option( 'wpca_settings', array() );
            
            // 移除菜单设置，恢复默认状态
            if ( isset( $settings['hidden_menus'] ) ) {
                unset( $settings['hidden_menus'] );
            }
            if ( isset( $settings['menu_toggles'] ) ) {
                unset( $settings['menu_toggles'] );
            }
            
            // 保存更新后的设置
            $success = update_option( 'wpca_settings', $settings );
            
            if ( $success ) {
                // 清除缓存
                if ( function_exists( 'wp_cache_delete' ) ) {
                    wp_cache_delete( 'wpca_settings', 'options' );
                }
                
                // 记录重置操作
                if ( defined( 'WP_DEBUG' ) && WP_DEBUG && function_exists( 'error_log' ) ) {
                    error_log( 'WP Clean Admin - Menu has been reset to default' );
                }
                
                if ( function_exists( 'wp_send_json_success' ) ) {
                    wp_send_json_success( array( 'message' => 'Menu has been reset to default' ) );
                }
            } else {
                throw new Exception('Failed to reset menu', 'reset_failed');
            }
        } catch ( Exception $e ) {
            $code = method_exists( $e, 'getCode' ) && $e->getCode() ? $e->getCode() : 'reset_menu_error';
            $message = $e->getMessage();
            
            // 记录错误
            if ( function_exists( 'error_log' ) ) {
                error_log( 'WP Clean Admin - Reset Menu Error (' . $code . '): ' . $message );
            }
            
            // 发送错误响应
            if ( function_exists( 'wp_send_json_error' ) ) {
                wp_send_json_error( array( 
                    'code' => $code,
                    'message' => $message
                ), 400 );
            }
        }
        
        // 确保请求终止
        if ( function_exists( 'wp_die' ) ) {
            wp_die();
        }
    }
    
    /**
     * Reset menu order to default
     */
    public function reset_menu_order() {
        if ( ! $this->validate_ajax_request( 'wpca_admin_nonce' ) ) {
            return;
        }
        
        try {
            // 验证必要函数存在
            if ( ! function_exists( 'update_option' ) || ! function_exists( 'get_option' ) ) {
                throw new Exception('Required WordPress functions not available', 'functions_missing');
            }
            
            // 获取当前设置
            $settings = get_option( 'wpca_settings', array() );
            
            // 移除自定义菜单顺序设置，恢复默认状态
            if ( isset( $settings['menu_order'] ) ) {
                unset( $settings['menu_order'] );
            }
            
            // 移除自定义子菜单顺序设置，恢复默认状态
            if ( isset( $settings['submenu_order'] ) ) {
                unset( $settings['submenu_order'] );
            }
            
            // 保存更新后的设置
            $success = update_option( 'wpca_settings', $settings );
            
            if ( $success ) {
                // 清除缓存
                if ( function_exists( 'wp_cache_delete' ) ) {
                    wp_cache_delete( 'wpca_settings', 'options' );
                }
                
                // 记录重置操作
                if ( defined( 'WP_DEBUG' ) && WP_DEBUG && function_exists( 'error_log' ) ) {
                    error_log( 'WP Clean Admin - Menu order has been reset to default' );
                }
                
                if ( function_exists( 'wp_send_json_success' ) ) {
                    wp_send_json_success( array( 'message' => 'Menu order has been reset to default' ) );
                }
            } else {
                throw new Exception('Failed to reset menu order', 'reset_failed');
            }
        } catch ( Exception $e ) {
            $code = method_exists( $e, 'getCode' ) && $e->getCode() ? $e->getCode() : 'reset_menu_order_error';
            $message = $e->getMessage();
            
            // 记录错误
            if ( function_exists( 'error_log' ) ) {
                error_log( 'WP Clean Admin - Reset Menu Order Error (' . $code . '): ' . $message );
            }
            
            // 发送错误响应
            if ( function_exists( 'wp_send_json_error' ) ) {
                wp_send_json_error( array( 
                    'code' => $code,
                    'message' => $message
                ), 400 );
            }
        }
        
        // 确保请求终止
        if ( function_exists( 'wp_die' ) ) {
            wp_die();
        }
    }
    
    /**
 * Get public data for unauthenticated users
 */
public function get_public_data() {
    try {
        // 验证是AJAX请求
        if (!defined('DOING_AJAX') || !DOING_AJAX) {
            if (function_exists('wp_die')) {
                wp_die();
            }
            return;
        }
        
        // 虽然这是公共数据，但我们仍然限制返回的数据范围
        $public_data = array(
            'version' => WPCA_VERSION,
            // 只返回登录样式，不返回其他敏感设置
            'login_style' => 'default' // 默认样式，不依赖选项表
        );
        
        // 即使是公共数据，也验证函数存在
        if (function_exists('wp_send_json_success')) {
            wp_send_json_success($public_data);
        }
    } catch (Exception $e) {
        // 改进的错误处理，但对于公共端点不暴露详细错误信息
        if (function_exists('error_log')) {
            error_log('WPCA Error in get_public_data: ' . $e->getMessage());
        }
    }
    
    // 确保请求终止
    if (function_exists('wp_die')) {
        wp_die();
    }
}
?>