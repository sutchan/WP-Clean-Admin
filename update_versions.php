<?php
/**
 * 批量更新 WP Clean Admin 插件文件版本号
 * 
 * 用法: php update_versions.php
 */

$plugin_dir = __DIR__ . '/wpcleanadmin';
$old_version = '1.8.0';
$new_version = '1.8.1';

// 更新所有 PHP 文件中的 @version 注释
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($plugin_dir),
    RecursiveIteratorIterator::SELF_FIRST
);

$updated_count = 0;

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $file_path = $file->getPathname();
        $content = file_get_contents($file_path);
        
        // 更新 @version 注释
        $new_content = str_replace(
            "@version {$old_version}",
            "@version {$new_version}",
            $content
        );
        
        // 如果有更新，写回文件
        if ($new_content !== $content) {
            file_put_contents($file_path, $new_content);
            echo "Updated: {$file_path}\n";
            $updated_count++;
        }
    }
}

// 更新语言文件中的版本号
$lang_files = [
    $plugin_dir . '/languages/wp-clean-admin.pot',
    $plugin_dir . '/languages/wp-clean-admin-zh_CN.po',
    $plugin_dir . '/languages/wp-clean-admin-en_US.po'
];

foreach ($lang_files as $lang_file) {
    if (file_exists($lang_file)) {
        $content = file_get_contents($lang_file);
        $new_content = str_replace(
            "Project-Id-Version: WP Clean Admin {$old_version}",
            "Project-Id-Version: WP Clean Admin {$new_version}",
            $content
        );
        if ($new_content !== $content) {
            file_put_contents($lang_file, $new_content);
            echo "Updated language file: {$lang_file}\n";
            $updated_count++;
        }
    }
}

echo "\nTotal files updated: {$updated_count}\n";
echo "Version updated from {$old_version} to {$new_version}\n";
