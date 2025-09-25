<?php
/**
 * WP Clean Admin - 数据库表检查脚本
 *
 * 此脚本用于检查和解决 WordPress 数据库错误：[Table 'opnr.opnrwp_openpnr_logs' doesn't exist]
 * 这个错误似乎与 WP Clean Admin 插件无关，可能来自其他插件或主题。
 */

// 加载 WordPress 核心文件
define('ABSPATH', dirname(dirname(dirname(dirname(__FILE__)))) . '/');
require_once(ABSPATH . 'wp-load.php');

/**
 * 检查并修复缺失的数据库表
 *
 * 这个函数会检查 opnrwp_openpnr_logs 表是否存在，如果不存在，会尝试创建它
 */
function check_and_fix_log_table() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'openpnr_logs';
    $charset_collate = $wpdb->get_charset_collate();
    
    echo "<h2>数据库表检查结果</h2>\n";
    echo "<p>检查的表名: <strong>{$table_name}</strong></p>\n";
    
    // 检查表是否存在
    if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
        echo "<p style='color: red;'>错误: 表 '{$table_name}' 不存在！</p>\n";
        echo "<p>这个表名不属于 WP Clean Admin 插件，可能来自其他插件或主题。</p>\n";
        
        // 尝试创建表
        echo "<h3>尝试创建缺失的表...</h3>\n";
        
        $sql = "CREATE TABLE {$table_name} (\n"
            . "id bigint(20) NOT NULL AUTO_INCREMENT,\n"
            . "created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,\n"
            . "user_id bigint(20) DEFAULT NULL,\n"
            . "action varchar(255) NOT NULL,\n"
            . "details longtext,\n"
            . "ip_address varchar(45) DEFAULT NULL,\n"
            . "PRIMARY KEY (id),\n"
            . "KEY idx_created_at (created_at),\n"
            . "KEY idx_user_id (user_id)\n"
            . ") {$charset_collate};";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $result = dbDelta($sql);
        
        if (empty($result)) {
            echo "<p style='color: green;'>成功: 表 '{$table_name}' 已创建！</p>\n";
            echo "<p>注意: 虽然我们已创建表，但这只是临时解决方案。建议您找出哪个插件或主题需要此表，并正确安装它。</p>\n";
        } else {
            echo "<p style='color: red;'>创建表失败，请手动创建表。错误信息:</p>\n";
            echo "<pre>" . print_r($result, true) . "</pre>\n";
        }
    } else {
        echo "<p style='color: green;'>表 '{$table_name}' 已存在。</p>\n";
        echo "<p>如果您仍然遇到错误，可能是其他原因导致的。</p>\n";
    }
    
    echo "<h3>可能的解决方案</h3>\n";
    echo "<ul>\n";
    echo "<li>检查是否有其他插件或主题需要此表，并确保它们正确安装。</li>\n";
    echo "<li>如果找不到来源，可以考虑禁用最近安装的插件来排查问题。</li>\n";
    echo "<li>如果您不需要日志功能，可以查找并移除尝试访问此表的代码。</li>\n";
    echo "</ul>\n";
}

// 执行检查
check_and_fix_log_table();

/**
 * 检查哪些插件可能使用了 openpnr 相关功能
 */
function check_plugins_for_openpnr() {
    echo "<h2>插件检查</h2>\n";
    echo "<p>检查已安装的插件是否包含 'openpnr' 相关代码...</p>\n";
    
    $plugins = get_plugins();
    $found = false;
    
    foreach ($plugins as $plugin_file => $plugin_data) {
        $plugin_path = WP_PLUGIN_DIR . '/' . $plugin_file;
        $plugin_content = file_get_contents($plugin_path);
        
        if (strpos($plugin_content, 'openpnr') !== false) {
            echo "<p style='color: orange;'>找到可能相关的插件: <strong>{$plugin_data['Name']}</strong> ({$plugin_file})</p>\n";
            $found = true;
        }
    }
    
    if (!$found) {
        echo "<p>未找到直接包含 'openpnr' 的插件。</p>\n";
    }
}

// 执行插件检查
check_plugins_for_openpnr();

?>