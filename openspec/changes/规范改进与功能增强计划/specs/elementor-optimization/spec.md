<!-- OPENSPEC:START -->
## 设计参考

### 架构设计
- **详细设计**: [plugin-architecture/design.md](../plugin-architecture/design.md)

## ADDED Requirements

### Requirement: Elementor 缓存清理
系统SHALL提供清理 Elementor 缓存数据的功能

#### Scenario: 清理 Elementor 缓存
- **WHEN** 管理员在 WP Clean Admin 清理页面点击了清理 Elementor 缓存
- **THEN** 系统将清理 Elementor 的所有缓存数据
- **AND** 缓存包括：CSS 缓存、字体缓存、数据缓存
- **AND** 系统将显示清理结果

#### Scenario: 清理 Elementor 临时文件
- **WHEN** 管理员启用了自动清理 Elementor 临时文件
- **THEN** 系统将定期清理 Elementor 的临时文件
- **AND** 临时文件包括：未使用的模板、过期缓存、调试日志

### Requirement: Elementor 资源优化
系统SHALL提供优化 Elementor 资源加载的功能

#### Scenario: 优化 CSS 加载
- **WHEN** 管理员启用了 Elementor CSS 优化
- **THEN** 系统将合并和压缩 Elementor 的 CSS 文件
- **AND** 系统将移除未使用的 CSS 规则
- **AND** 系统将启用 CSS 缓存

#### Scenario: 优化 JavaScript 加载
- **WHEN** 管理员启用了 ElementScript 优化
- **THEN** 系统将延迟加载 Elementor 的 JavaScript 文件
- **AND** 系统将移除未使用的脚本
- **AND** 系统将启用脚本缓存

### Requirement: Elementor 小工具管理
系统SHALL提供管理 Elementor 小工具的功能

#### Scenario: 禁用不需要的小工具
- **WHEN** 管理员在设置页面禁用了特定的小工具
- **THEN** 这些小工具将不会在 Elementor 面板中显示
- **AND** 禁用的小工具不会在前端加载

#### Scenario: 优化小工具加载
- **WHEN** 管理员启用了小工具懒加载
- **THEN** 只有当前页面使用的小工具才会被加载
- **AND** 系统将追踪小工具使用情况

### Requirement: Elementor 数据清理
系统SHALL提供清理 Elementor 冗余数据的功能

#### Scenario: 清理未使用的模板
- **WHEN** 管理员点击了清理未使用的模板
- **THEN** 系统将识别未被任何页面使用的模板
- **AND** 系统将显示待清理的模板列表
- **AND** 管理员确认后，系统将删除这些模板

#### Scenario: 清理孤儿数据
- **WHEN** 管理员点击了清理孤儿数据
- **THEN** 系统将查找 Elementor 元数据中的孤儿记录
- **AND** 这些记录将被标记为待删除
- **AND** 管理员确认后，系统将删除这些数据

### Requirement: Elementor 安全增强
系统SHALL提供增强 Elementor 安全性的功能

#### Scenario: 限制模板导入
- **WHEN** 管理员启用了模板导入限制
- **THEN** 只有管理员可以导入 Elementor 模板
- **AND** 导入的模板将经过安全检查

#### Scenario: 清理调试数据
- **WHEN** 管理员启用了自动清理调试数据
- **THEN** 系统将定期清理 Elementor 的调试日志
- **AND** 调试信息将不会被显示在前端

## MODIFIED Requirements

### Requirement: Elementor 集成状态
系统SHALL允许配置 Elementor 集成的启用状态

#### Scenario: 成功启用 Elementor 集成
- **WHEN** 管理员在设置页面启用了 Elementor 集成
- **THEN** 系统将加载 Elementor 相关功能
- **AND** Elementor 优化选项将可用

## Design References

### 技术实现

#### Elementor 缓存检测

```php
class WPCA_Elementor_Cache_Manager {
    public function get_cache_info() {
        return array(
            'css_cache' => $this->get_css_cache_size(),
            'font_cache' => $this->get_font_cache_size(),
            'data_cache' => $this->get_data_cache_size(),
            'temp_files' => $this->get_temp_file_count(),
        );
    }
    
    public function clear_all_cache() {
        $this->clear_css_cache();
        $this->clear_font_cache();
        $this->clear_data_cache();
        $this->clear_temp_files();
        
        // 清除 Elementor 缓存
        \Elementor\Plugin::$instance->posts_css_manager->clear_cache();
        
        return true;
    }
    
    private function get_css_cache_size() {
        $upload_dir = wp_upload_dir();
        $css_dir = $upload_dir['basedir'] . '/elementor/css';
        
        if (!is_dir($css_dir)) {
            return 0;
        }
        
        return $this->get_dir_size($css_dir);
    }
    
    private function clear_css_cache() {
        $upload_dir = wp_upload_dir();
        $css_dir = $upload_dir['basedir'] . '/elementor/css';
        
        if (is_dir($css_dir)) {
            $this->delete_dir($css_dir);
        }
    }
}
```

#### Elementor 小工具管理

```php
class WPCA_Elementor_Widget_Manager {
    private $disabled_widgets = array();
    
    public function __construct() {
        $this->disabled_widgets = get_option('wpca_elementor_disabled_widgets', array());
        
        add_filter('elementor/widgets/black_list', array($this, 'filter_widgets'));
        add_action('elementor/frontend/after_enqueue_styles', array($this, 'optimize_styles'));
        add_action('elementor/frontend/after_enqueue_scripts', array($this, 'optimize_scripts'));
    }
    
    public function filter_widgets($widgets) {
        foreach ($this->disabled_widgets as $widget) {
            $widgets[] = $widget;
        }
        return $widgets;
    }
    
    public function disable_widget($widget_name) {
        if (!in_array($widget_name, $this->disabled_widgets)) {
            $this->disabled_widgets[] = $widget_name;
            update_option('wpca_elementor_disabled_widgets', $this->disabled_widgets);
        }
    }
    
    public function enable_widget($widget_name) {
        $key = array_search($widget_name, $this->disabled_widgets);
        if ($key !== false) {
            unset($this->disabled_widgets[$key]);
            update_option('wpca_elementor_disabled_widgets', $this->disabled_widgets);
        }
    }
}
```

#### Elementor 数据清理

```php
class WPCA_Elementor_Data_Cleaner {
    public function find_orphan_metadata() {
        global $wpdb;
        
        // 查找 Elementor 元数据中不存在对应的帖子记录
        $orphan_query = "
            SELECT pm.meta_id, pm.post_id, pm.meta_key
            FROM {$wpdb->postmeta} pm
            LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID
            WHERE p.ID IS NULL
            AND pm.meta_key LIKE '_elementor_%'
        ";
        
        return $wpdb->get_results($orphan_query);
    }
    
    public function find_unused_templates() {
        $templates = $this->get_all_templates();
        $used_template_ids = $this->get_used_template_ids();
        
        $unused = array();
        foreach ($templates as $template) {
            if (!in_array($template->ID, $used_template_ids)) {
                $unused[] = $template;
            }
        }
        
        return $unused;
    }
    
    public function clean_orphan_metadata() {
        $orphans = $this->find_orphan_metadata();
        $deleted = 0;
        
        foreach ($orphans as $orphan) {
            delete_post_meta($orphan->post_id, $orphan->meta_key);
            $deleted++;
        }
        
        return $deleted;
    }
    
    public function delete_templates($template_ids) {
        $deleted = 0;
        
        foreach ($template_ids as $id) {
            if (wp_delete_post($id, true)) {
                $deleted++;
            }
        }
        
        return $deleted;
    }
}
```

### 相关文件

- `includes/class-wpca-elementor.php` - Elementor 优化核心类
- `includes/class-wpca-cleanup.php` - 清理功能核心类

### 相关 API

```php
// 获取 Elementor 缓存信息
wpca_elementor_get_cache_info();

// 清理 Elementor 所有缓存
wpca_elementor_clear_cache();

// 清理 Elementor CSS 缓存
wpca_elementor_clear_css_cache();

// 获取禁用的小工具列表
wpca_elementor_get_disabled_widgets();

// 禁用小工具
wpca_elementor_disable_widget($widget_name);

// 启用小工具
wpca_elementor_enable_widget($widget_name);

// 查找孤儿数据
wpca_elementor_find_orphan_metadata();

// 清理孤儿数据
wpca_elementor_clean_orphan_metadata();

// 查找未使用的模板
wpca_elementor_find_unused_templates();

// 删除未使用的模板
wpca_elementor_delete_unused_templates($template_ids);
```

<!-- OPENSPEC:END -->
