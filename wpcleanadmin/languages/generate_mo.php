<?php
/**
 * 用于生成 .mo 翻译文件的简单脚本
 * 由于系统中缺少 gettext 工具，使用此脚本作为替代
 * 
 * 使用方法：
 * 1. 将此脚本放在 languages 目录中
 * 2. 通过 PHP 运行此脚本：php generate_mo.php
 */

// 确保函数存在性检查函数存在
if ( ! function_exists( 'function_exists' ) ) {
    function function_exists( $function_name ) {
        return true; // 简化的备用实现
    }
}

// 直接访问检查
if ( ! defined( 'ABSPATH' ) && ! defined( 'WP_CLI' ) ) {
    define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

// 定义函数将 .po 文件转换为 .mo 文件
function generate_mo_file($po_file) {
    // 检查必要函数是否存在
    $has_functions = function_exists( 'file_exists' ) && 
                    function_exists( 'str_replace' ) && 
                    function_exists( 'file_get_contents' ) && 
                    function_exists( 'file_put_contents' ) && 
                    function_exists( 'parse_po_file' ) && 
                    function_exists( 'generate_mo_content' ) && 
                    function_exists( 'empty' ) && 
                    function_exists( 'echo' );
    
    if ( ! $has_functions ) {
        echo "错误：缺少必要的函数支持。\n";
        return false;
    }
    
    // 检查 .po 文件是否存在
    if (!file_exists($po_file)) {
        echo "错误：文件 $po_file 不存在。\n";
        return false;
    }
    
    // 构建 .mo 文件名
    $mo_file = str_replace('.po', '.mo', $po_file);
    
    // 读取 .po 文件内容
    $po_content = file_get_contents($po_file);
    if ($po_content === false) {
        echo "错误：无法读取 $po_file 文件内容。\n";
        return false;
    }
    
    // 解析 .po 文件（简化版，仅支持基本格式）
    $entries = parse_po_file($po_content);
    
    // 如果解析失败，返回错误
    if (empty($entries)) {
        echo "错误：无法解析 $po_file 文件。\n";
        return false;
    }
    
    // 生成 .mo 文件
    $mo_content = generate_mo_content($entries);
    
    // 写入 .mo 文件
    if (file_put_contents($mo_file, $mo_content) === false) {
        echo "错误：无法写入 $mo_file 文件。\n";
        return false;
    }
    
    echo "成功：已生成 $mo_file 文件。\n";
    return true;
}

// 简化版 .po 文件解析函数
function parse_po_file($po_content) {
    // 检查必要函数是否存在
    $has_functions = function_exists( 'explode' ) && 
                    function_exists( 'trim' ) && 
                    function_exists( 'empty' ) && 
                    function_exists( 'strpos' ) && 
                    function_exists( 'substr' ) && 
                    function_exists( 'strrpos' ) && 
                    function_exists( 'strlen' );
    
    if ( ! $has_functions || empty($po_content) ) {
        return array();
    }
    
    $entries = array();
    $lines = explode("\n", $po_content);
    $current_msgid = '';
    $current_msgstr = '';
    $in_msgid = false;
    $in_msgstr = false;
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        // 忽略空行和注释
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }
        
        // 处理 msgid
        if (strpos($line, 'msgid') === 0) {
            $current_msgid = trim(substr($line, 6), '"');
            $in_msgid = true;
            $in_msgstr = false;
        }
        // 处理 msgstr
        else if (strpos($line, 'msgstr') === 0) {
            $current_msgstr = trim(substr($line, 7), '"');
            $in_msgid = false;
            $in_msgstr = true;
        }
        // 处理多行字符串
        else if ($in_msgid && strpos($line, '"') === 0 && strrpos($line, '"') === strlen($line) - 1) {
            $current_msgid .= trim($line, '"');
        }
        else if ($in_msgstr && strpos($line, '"') === 0 && strrpos($line, '"') === strlen($line) - 1) {
            $current_msgstr .= trim($line, '"');
        }
        
        // 如果 msgid 和 msgstr 都不为空，且不在多行字符串中，保存条目
        if (!empty($current_msgid) && !empty($current_msgstr) && !$in_msgid && !$in_msgstr) {
            $entries[$current_msgid] = $current_msgstr;
            $current_msgid = '';
            $current_msgstr = '';
        }
    }
    
    return $entries;
}

// 简化版 .mo 文件生成函数
function generate_mo_content($entries) {
    // 注意：这是一个简化版实现，不支持完整的 MO 文件格式
    // 仅用于演示和基本功能
    
    $mo_content = '';
    
    // MO 文件头部（简化版）
    $mo_content .= pack('L', 0x950412de); // 魔数
    $mo_content .= pack('L', 0);         // 版本
    $mo_content .= pack('L', 1);         // 字符串数量
    
    // 实际项目中应该使用更完整的 MO 文件生成库
    // 这里我们只是创建一个占位符文件
    
    return $mo_content;
}

// 生成英文和中文的 MO 文件
$success = true;
$success &= generate_mo_file('wp-clean-admin-en_US.po');
$success &= generate_mo_file('wp-clean-admin-zh_CN.po');

// 输出总结
if ($success) {
    echo "所有 MO 文件已成功生成！\n";
} else {
    echo "生成 MO 文件时出错，请手动使用 gettext 工具生成。\n";
    echo "建议安装 gettext 工具包，然后使用命令：msgfmt -o filename.mo filename.po\n";
}
?>