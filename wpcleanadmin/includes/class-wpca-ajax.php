-<?php
/**
 * AJAX 处理类
 *
 * @package WPCleanAdmin
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WPCA_Ajax 类
 *
 * 处理所有 AJAX 请求
 */
class WPCA_Ajax {

    /**
     * 构造函数
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * 初始化钩子
     */
    public function init_hooks() {
        add_action('wp_ajax_wpca_save_settings', array($this, 'save_settings'));
        add_action('wp_ajax_wpca_reset_settings', array($this, 'reset_settings'));
        add_action('wp_ajax_wpca_get_settings', array($this, 'get_settings'));
    }

    /**
     * 验证 AJAX 请求
     *
     * @param string $action AJAX 操作名称。
     * @return void
     */
    private function validate_ajax_request($action) {
        // 检查是否为 AJAX 请求
        if (!wp_doing_ajax()) {
            wp_send_json_error(array(
                'message' => __('非法请求', 'wp-clean-admin')
            ), 400);
        }

        // 验证 nonce
        if (!check_ajax_referer('wpca_nonce_' . $action, 'nonce', false)) {
            wp_send_json_error(array(
                'message' => __('安全验证失败', 'wp-clean-admin')
            ), 403);
        }

        // 检查权限
        $permissions = new WPCA_Permissions();
        if (!$permissions->current_user_can('wpca_manage_menus') && 
            !current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => __('权限不足', 'wp-clean-admin')
            ), 403);
        }
    }

    /**
     * 保存设置
     */
    public function save_settings() {
        try {
            $this->validate_ajax_request('save_settings');
            
            // 获取并验证设置数据
            $settings = isset($_POST['settings']) ? wp_unslash($_POST['settings']) : array();
            
            parse_str($settings, $settings_array);
            
            if (!is_array($settings_array) || !isset($settings_array['wpca_settings'])) {
                wp_send_json_error(array(
                    'message' => __('无效的设置数据', 'wp-clean-admin')
                ), 400);
            }
            
            // 更新设置
            $updated = update_option('wpca_settings', $settings_array['wpca_settings']);
            
            // 清除缓存
            WPCA_Settings::$cached_options = null;
            
            if ($updated) {
                wp_send_json_success(array(
                    'message' => __('设置已保存', 'wp-clean-admin'),
                    'data' => get_option('wpca_settings')
                ));
            } else {
                wp_send_json_error(array(
                    'message' => __('设置保存失败', 'wp-clean-admin')
                ), 500);
            }
        } catch (Exception $e) {
            error_log('WPCA Save Settings Error: ' . $e->getMessage());
            wp_send_json_error(array(
                'message' => __('保存设置时发生错误', 'wp-clean-admin')
            ), 500);
        }
    }

    /**
     * 重置设置
     */
    public function reset_settings() {
        try {
            $this->validate_ajax_request('reset_settings');
            
            // 重置为默认设置
            $defaults = WPCA_Settings::get_default_settings();
            $reset = update_option('wpca_settings', $defaults);
            
            // 清除缓存
            WPCA_Settings::$cached_options = null;
            
            if ($reset) {
                wp_send_json_success(array(
                    'message' => __('设置已重置为默认值', 'wp-clean-admin'),
                    'data' => get_option('wpca_settings')
                ));
            } else {
                wp_send_json_error(array(
                    'message' => __('重置设置失败', 'wp-clean-admin')
                ), 500);
            }
        } catch (Exception $e) {
            error_log('WPCA Reset Settings Error: ' . $e->getMessage());
            wp_send_json_error(array(
                'message' => __('重置设置时发生错误', 'wp-clean-admin')
            ), 500);
        }
    }

    /**
     * 获取当前设置
     */
    public function get_settings() {
        try {
            $this->validate_ajax_request('get_settings');
            
            $settings = get_option('wpca_settings');
            
            wp_send_json_success(array(
                'data' => $settings
            ));
        } catch (Exception $e) {
            error_log('WPCA Get Settings Error: ' . $e->getMessage());
            wp_send_json_error(array(
                'message' => __('获取设置时发生错误', 'wp-clean-admin')
            ), 500);
        }
    }
}