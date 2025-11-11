<?php
/**
 * 改进版用于生成和更新 .po/.mo 翻译文件的脚本
 * 无需外部 Gettext 工具，可独立运行
 * 
 * 使用方法：
 * 1. 将此脚本放在 languages 目录中
 * 2. 通过 PHP 运行此脚本：php generate_mo_improved.php
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

// 设置错误报告（安全地设置）
if ( function_exists( 'error_reporting' ) && function_exists( 'ini_set' ) ) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// 定义文件路径
$pot_file = 'wp-clean-admin.pot';
$po_files = array(
    'wp-clean-admin-en_US.po',
    'wp-clean-admin-zh_CN.po'
);

/**
 * 从 POT 文件更新 PO 文件
 * 
 * @param string $pot_file POT 文件路径
 * @param string $po_file PO 文件路径
 * @return bool 更新是否成功
 */
function update_po_file($pot_file, $po_file) {
    // 检查必要函数是否存在
    $has_functions = function_exists( 'echo' ) && 
                    function_exists( 'file_exists' ) && 
                    function_exists( 'copy' ) && 
                    function_exists( 'file_get_contents' ) && 
                    function_exists( 'parse_po_file' ) && 
                    function_exists( 'empty' ) && 
                    function_exists( 'array_merge' ) && 
                    function_exists( 'extract_po_header' ) && 
                    function_exists( 'generate_po_content' ) && 
                    function_exists( 'file_put_contents' );
    
    if ( ! $has_functions ) {
        if (function_exists('echo')) echo "错误：缺少必要的函数支持。\n";
        return false;
    }
    
    echo "正在更新 $po_file 文件...\n";
    
    // 检查 POT 文件是否存在
    if (!file_exists($pot_file)) {
        echo "错误：POT 文件 $pot_file 不存在。\n";
        return false;
    }
    
    // 如果 PO 文件不存在，则创建新的
    if (!file_exists($po_file)) {
        echo "PO 文件 $po_file 不存在，创建新文件...\n";
        if (!copy($pot_file, $po_file)) {
            echo "错误：无法创建 $po_file 文件。\n";
            return false;
        }
        echo "PO 文件 $po_file 创建成功！\n";
        return true;
    }
    
    // 读取 POT 和 PO 文件内容
    $pot_content = file_get_contents($pot_file);
    $po_content = file_get_contents($po_file);
    
    if ($pot_content === false || $po_content === false) {
        echo "错误：无法读取文件内容。\n";
        return false;
    }
    
    // 解析 POT 和 PO 文件
    $pot_entries = parse_po_file($pot_content);
    $po_entries = parse_po_file($po_content);
    
    if (empty($pot_entries)) {
        echo "错误：无法解析 $pot_file 文件。\n";
        return false;
    }
    
    if (empty($po_entries)) {
        echo "错误：无法解析 $po_file 文件。\n";
        return false;
    }
    
    // 合并新的翻译条目，保留现有翻译
    $merged_entries = array_merge($pot_entries, $po_entries);
    
    // 生成更新后的 PO 文件内容
    $header = extract_po_header($po_content);
    $new_po_content = generate_po_content($merged_entries, $header);
    
    // 写入更新后的 PO 文件
    if (file_put_contents($po_file, $new_po_content) === false) {
        echo "错误：无法写入 $po_file 文件。\n";
        return false;
    }
    
    echo "PO 文件 $po_file 更新成功！\n";
    return true;
}

/**
 * 提取 PO 文件的头部信息
 * 
 * @param string $po_content PO 文件内容
 * @return string 头部信息
 */
function extract_po_header($po_content) {
    // 检查必要函数是否存在
    $has_functions = function_exists( 'explode' ) && 
                    function_exists( 'trim' ) && 
                    function_exists( 'strpos' ) && 
                    function_exists( 'array_push' ) && 
                    function_exists( 'implode' );
    
    if ( ! $has_functions || empty($po_content) ) {
        return '';
    }
    
    $lines = explode("\n", $po_content);
    $header_lines = array();
    $in_header = false;
    $msgid_count = 0;
    
    foreach ($lines as $line) {
        if (strpos(trim($line), 'msgid') === 0) {
            $msgid_count++;
            
            if ($msgid_count > 1) {
                break;
            }
        }
        
        if ($msgid_count <= 1) {
            $header_lines[] = $line;
        }
    }
    
    return implode("\n", $header_lines);
}

/**
 * 解析 PO 文件
 * 
 * @param string $po_content PO 文件内容
 * @return array 解析后的翻译条目
 */
function parse_po_file($po_content) {
    // 检查必要函数是否存在
    $has_functions = function_exists( 'explode' ) && 
                    function_exists( 'trim' ) && 
                    function_exists( 'strpos' ) && 
                    function_exists( 'strrpos' ) && 
                    function_exists( 'substr' ) && 
                    function_exists( 'strlen' ) && 
                    function_exists( 'empty' ) && 
                    function_exists( 'is_array' ) && 
                    function_exists( 'isset' );
    
    if ( ! $has_functions || empty($po_content) || !is_string($po_content) ) {
        return array();
    }
    
    $entries = array();
    $lines = explode("\n", $po_content);
    $current_msgid = '';
    $current_msgstr = '';
    $in_msgid = false;
    $in_msgstr = false;
    $comments = '';
    
    foreach ($lines as $line) {
        $trimmed_line = trim($line);
        
        // 处理注释
        if (strpos($trimmed_line, '#') === 0) {
            $comments .= $line . "\n";
            continue;
        }
        
        // 处理 msgid
        if (strpos($trimmed_line, 'msgid') === 0) {
            // 保存之前的条目
            if (!empty($current_msgid)) {
                $entries[$current_msgid] = array(
                    'msgstr' => $current_msgstr,
                    'comments' => $comments
                );
            }
            
            $current_msgid = trim(substr($trimmed_line, 6), '"');
            $current_msgstr = '';
            $in_msgid = true;
            $in_msgstr = false;
            $comments = '';
        }
        // 处理 msgstr
        else if (strpos($trimmed_line, 'msgstr') === 0) {
            $current_msgstr = trim(substr($trimmed_line, 7), '"');
            $in_msgid = false;
            $in_msgstr = true;
        }
        // 处理多行字符串
        else if ($in_msgid && strpos($trimmed_line, '"') === 0 && strrpos($trimmed_line, '"') === strlen($trimmed_line) - 1) {
            $current_msgid .= trim($trimmed_line, '"');
        }
        else if ($in_msgstr && strpos($trimmed_line, '"') === 0 && strrpos($trimmed_line, '"') === strlen($trimmed_line) - 1) {
            $current_msgstr .= trim($trimmed_line, '"');
        }
        // 处理空行
        else if (empty($trimmed_line)) {
            // 保存之前的条目（如果有的话）
            if (!empty($current_msgid)) {
                $entries[$current_msgid] = array(
                    'msgstr' => $current_msgstr,
                    'comments' => $comments
                );
                
                $current_msgid = '';
                $current_msgstr = '';
                $in_msgid = false;
                $in_msgstr = false;
                $comments = '';
            }
        }
    }
    
    // 保存最后一个条目
    if (!empty($current_msgid)) {
        $entries[$current_msgid] = array(
            'msgstr' => $current_msgstr,
            'comments' => $comments
        );
    }
    
    return $entries;
}

/**
 * 生成 PO 文件内容
 * 
 * @param array $entries 翻译条目
 * @param string $header 头部信息
 * @return string PO 文件内容
 */
function generate_po_content($entries, $header) {
    // 检查必要函数是否存在
    $has_functions = function_exists( 'empty' ) && 
                    function_exists( 'is_array' ) && 
                    function_exists( 'is_string' ) && 
                    function_exists( 'isset' ) && 
                    function_exists( 'strlen' );
    
    if ( ! $has_functions || !is_array($entries) ) {
        return is_string($header) ? $header : '';
    }
    
    $content = (is_string($header) ? $header : '') . "\n\n";
    
    foreach ($entries as $msgid => $entry) {
        if ($msgid === '' || !is_array($entry)) continue; // 跳过空的 msgid（通常是头部）
        
        // 添加注释
        if (isset($entry['comments']) && !empty($entry['comments'])) {
            $content .= $entry['comments'];
        }
        
        // 添加 msgid 和 msgstr
        $msgstr = isset($entry['msgstr']) ? $entry['msgstr'] : '';
        $content .= "msgid \"$msgid\"\n";
        $content .= "msgstr \"$msgstr\"\n\n";
    }
    
    return $content;
}

/**
 * 生成 MO 文件
 * 注意：这是一个简化版实现，仅用于基本功能
 * 
 * @param string $po_file PO 文件路径
 * @return bool 生成是否成功
 */
function generate_mo_file($po_file) {
    // 检查必要函数是否存在
    $has_functions = function_exists( 'echo' ) && 
                    function_exists( 'file_exists' ) && 
                    function_exists( 'str_replace' ) && 
                    function_exists( 'file_get_contents' ) && 
                    function_exists( 'parse_po_file' ) && 
                    function_exists( 'empty' ) && 
                    function_exists( 'is_array' ) && 
                    function_exists( 'isset' ) && 
                    function_exists( 'generate_mo_content' ) && 
                    function_exists( 'file_put_contents' ) &&
                    function_exists( 'is_string' );
    
    if ( ! $has_functions ) {
        if (function_exists('echo')) echo "错误：缺少必要的函数支持。\n";
        return false;
    }
    
    // 检查参数类型
    if (!is_string($po_file)) {
        echo "错误：参数类型错误。\n";
        return false;
    }
    
    // 检查 PO 文件是否存在
    if (!file_exists($po_file)) {
        echo "错误：PO 文件 $po_file 不存在。\n";
        return false;
    }
    
    // 构建 MO 文件名
    $mo_file = str_replace('.po', '.mo', $po_file);
    
    // 读取 PO 文件内容
    $po_content = file_get_contents($po_file);
    
    if ($po_content === false) {
        echo "错误：无法读取 $po_file 文件。\n";
        return false;
    }
    
    // 解析 PO 文件
    $entries = parse_po_file($po_content);
    
    if (empty($entries) || !is_array($entries)) {
        echo "错误：无法解析 $po_file 文件。\n";
        return false;
    }
    
    // 过滤掉空的 msgid（通常是头部）
    $filtered_entries = array();
    foreach ($entries as $msgid => $entry) {
        if (!empty($msgid) && is_array($entry) && isset($entry['msgstr'])) {
            $filtered_entries[$msgid] = $entry['msgstr'];
        }
    }
    
    // 生成 MO 文件
    $mo_content = generate_mo_content($filtered_entries);
    
    // 写入 MO 文件
    if (file_put_contents($mo_file, $mo_content) === false) {
        echo "错误：无法写入 $mo_file 文件。\n";
        return false;
    }
    
    echo "MO 文件 $mo_file 生成成功！\n";
    return true;
}

/**
 * 生成 MO 文件内容
 * 注意：这是一个简化版实现，支持基本的 MO 文件格式
 * 
 * @param array $entries 翻译条目
 * @return string MO 文件内容
 */
function generate_mo_content($entries) {
    // 检查必要函数是否存在
    $has_functions = function_exists( 'pack' ) && 
                    function_exists( 'count' ) && 
                    function_exists( 'is_array' ) && 
                    function_exists( 'empty' ) && 
                    function_exists( 'strlen' );
    
    if ( ! $has_functions || !is_array($entries) ) {
        return '';
    }
    
    $magic = 0x950412de;
    $version = 0;
    $num_strings = count($entries);
    $offset_orig = 28; // 头部大小
    
    // 收集原始字符串和翻译后的字符串
    $orig_strings = array();
    $trans_strings = array();
    
    foreach ($entries as $msgid => $msgstr) {
        if (!empty($msgid) && is_string($msgid) && is_string($msgstr)) {
            $orig_strings[] = $msgid;
            $trans_strings[] = $msgstr;
        }
    }
    
    // 计算哈希表的偏移量
    $offset_trans = $offset_orig + $num_strings * 8;
    
    // 计算字符串表的偏移量
    $hash_table_offset = $offset_trans + $num_strings * 8;
    
    // 构建头部
    $mo_content = function_exists('pack') ? pack('L', $magic) : '';
    $mo_content .= function_exists('pack') ? pack('L', $version) : '';
    $mo_content .= function_exists('pack') ? pack('L', $num_strings) : '';
    $mo_content .= function_exists('pack') ? pack('L', $offset_orig) : '';
    $mo_content .= function_exists('pack') ? pack('L', $offset_trans) : '';
    $mo_content .= function_exists('pack') ? pack('L', 0) : ''; // 哈希表大小（简化实现）
    $mo_content .= function_exists('pack') ? pack('L', $hash_table_offset) : '';
    
    // 构建原始字符串索引表
    $current_offset = $hash_table_offset + $num_strings * 4; // 简化的哈希表大小
    
    foreach ($orig_strings as $string) {
        if (function_exists('pack') && function_exists('strlen')) {
            $mo_content .= pack('L', strlen($string));
            $mo_content .= pack('L', $current_offset);
            $current_offset += strlen($string) + 1; // +1 for null terminator
        }
    }
    
    // 构建翻译后的字符串索引表
    foreach ($trans_strings as $string) {
        if (function_exists('pack') && function_exists('strlen')) {
            $mo_content .= pack('L', strlen($string));
            $mo_content .= pack('L', $current_offset);
            $current_offset += strlen($string) + 1; // +1 for null terminator
        }
    }
    
    // 构建原始字符串表
    foreach ($orig_strings as $string) {
        $mo_content .= $string . "\0";
    }
    
    // 构建翻译后的字符串表
    foreach ($trans_strings as $string) {
        $mo_content .= $string . "\0";
    }
    
    // 添加简化的哈希表（对于基本功能不是必需的）
    for ($i = 0; $i < $num_strings; $i++) {
        if (function_exists('pack')) {
            $mo_content .= pack('L', 0);
        }
    }
    
    return $mo_content;
}

// 显示脚本信息
function display_script_info() {
    echo "==========================================================\n";
    echo "WPCleanAdmin 翻译文件生成工具（改进版）\n";
    echo "==========================================================\n";
}

// 执行更新和生成操作
display_script_info();

$success = true;

// 更新所有 PO 文件
foreach ($po_files as $po_file) {
    $success &= update_po_file($pot_file, $po_file);
    echo "\n";
}

// 生成所有 MO 文件
foreach ($po_files as $po_file) {
    $success &= generate_mo_file($po_file);
    echo "\n";
}

// 显示操作结果
echo "==========================================================\n";
if ($success) {
    echo "所有翻译文件已成功更新和生成！\n";
    echo "系统将使用新的翻译字符串。\n";
} else {
    echo "更新或生成翻译文件时出错。\n";
    echo "请检查错误信息并手动修复问题。\n";
}
echo "==========================================================\n";
?>