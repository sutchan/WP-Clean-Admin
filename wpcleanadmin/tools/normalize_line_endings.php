<?php
/**
 * WPCleanAdmin 工具 - 编码与换行规范化
 *
 * 递归扫描项目目录，将指定类型的文本文件统一为 UTF-8 无 BOM 编码，
 * 并将换行符统一为 Unix LF。默认执行写入，可通过命令行参数启用 dry-run。
 *
 * @package WPCleanAdmin
 * @version 1.7.11
 */

/**
 * 获取需要处理的文件扩展名列表
 *
 * @return array 扩展名列表
 */
function wpca_get_target_extensions() {
    return array('php', 'js', 'css', 'po', 'pot', 'md', 'txt');
}

/**
 * 判断文件是否为目标文本文件
 *
 * @param string $path 文件路径
 * @return bool 是否需要处理
 */
function wpca_is_target_text_file($path) {
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    if ($ext === 'mo') {
        return false;
    }
    return in_array($ext, wpca_get_target_extensions(), true);
}

/**
 * 将内容中的换行统一为 LF
 *
 * @param string $content 原始内容
 * @return string 规范化后的内容
 */
function wpca_normalize_line_endings($content) {
    $content = str_replace("\r\n", "\n", $content);
    $content = str_replace("\r", "\n", $content);
    return $content;
}

/**
 * 将文件写为 UTF-8 无 BOM 编码
 *
 * @param string $path 文件路径
 * @param string $content 文件内容（已规范化）
 * @return bool 是否写入成功
 */
function wpca_write_utf8_nobom($path, $content) {
    $fp = fopen($path, 'wb');
    if (!$fp) {
        return false;
    }
    // 不写入 BOM（EF BB BF），直接写入 UTF-8 文本
    $bytes = fwrite($fp, $content);
    fclose($fp);
    return $bytes !== false;
}

/**
 * 处理单个文件（按需写入）
 *
 * @param string $path 文件路径
 * @param bool   $dry_run 是否仅打印不写入
 * @return void
 */
function wpca_process_file($path, $dry_run) {
    $original = file_get_contents($path);
    if ($original === false) {
        echo "[SKIP] Read failed: {$path}\n";
        return;
    }
    $normalized = wpca_normalize_line_endings($original);
    // 若内容未变化也执行重写以去除 BOM（如果存在）
    if ($dry_run) {
        echo "[DRY] Normalize: {$path}\n";
        return;
    }
    $ok = wpca_write_utf8_nobom($path, $normalized);
    echo ($ok ? "[OK] " : "[ERR]") . " Normalize: {$path}\n";
}

/**
 * 主执行函数
 *
 * @param string $base_dir 基准目录
 * @param bool   $dry_run  是否仅打印不写入
 * @return void
 */
function wpca_run($base_dir, $dry_run) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($base_dir, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($iterator as $file) {
        $path = $file->getPathname();
        if (!is_file($path)) {
            continue;
        }
        if (!wpca_is_target_text_file($path)) {
            continue;
        }
        wpca_process_file($path, $dry_run);
    }
}

// 解析命令行参数
$dry_run = false;
foreach ($argv as $arg) {
    if ($arg === '--dry-run') {
        $dry_run = true;
    }
}

// 从当前工作目录运行
wpca_run(getcwd(), $dry_run);
?>