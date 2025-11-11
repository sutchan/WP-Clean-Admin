<?php
/**
 * 数据库优化功能测试
 * 
 * 测试数据库优化模块的核心功能
 * 
 * @package WPCleanAdmin
 * @since 1.6.0
 */

/**
 * 数据库优化功能测试类
 * 
 * @since 1.6.0
 */
class WPCA_Database_Optimization_Test extends WP_UnitTestCase {

    /**
     * 测试设置
     * 
     * @since 1.6.0
     */
    public function setUp() {
        parent::setUp();
        
        // 确保数据库类已加载
        if (!class_exists('WPCA_Database')) {
            require_once WPCA_DIR . 'includes/class-wpca-database.php';
        }
        
        if (!class_exists('WPCA_Database_Settings')) {
            require_once WPCA_DIR . 'includes/class-wpca-database-settings.php';
        }
    }
    
    /**
     * 测试数据库类实例化
     * 
     * @since 1.6.0
     */
    public function test_database_class_instantiation() {
        $database = new WPCA_Database();
        $this->assertInstanceOf('WPCA_Database', $database);
    }
    
    /**
     * 测试数据库设置类实例化
     * 
     * @since 1.6.0
     */
    public function test_database_settings_class_instantiation() {
        $settings = new WPCA_Database_Settings();
        $this->assertInstanceOf('WPCA_Database_Settings', $settings);
    }
    
    /**
     * 测试获取数据库表信息
     * 
     * @since 1.6.0
     */
    public function test_get_tables() {
        $database = new WPCA_Database();
        $tables = $database->get_tables();
        
        $this->assertIsArray($tables);
        // 至少应该有一个表
        $this->assertGreaterThan(0, count($tables));
    }
    
    /**
     * 测试获取数据库信息
     * 
     * @since 1.6.0
     */
    public function test_get_database_info() {
        $database = new WPCA_Database();
        $info = $database->get_database_info();
        
        $this->assertIsArray($info);
        $this->assertArrayHasKey('size', $info);
        $this->assertArrayHasKey('tables', $info);
        $this->assertArrayHasKey('version', $info);
        $this->assertArrayHasKey('type', $info);
    }
    
    /**
     * 测试计划任务功能
     * 
     * @since 1.6.0
     */
    public function test_scheduled_tasks() {
        // 设置计划任务
        WPCA_Database_Settings::set_scheduled_cleanup(true, 'weekly');
        
        // 检查计划任务是否已设置
        $this->assertTrue(WPCA_Database_Settings::is_scheduled_cleanup_enabled());
        $this->assertEquals('weekly', WPCA_Database_Settings::get_cleanup_frequency());
        
        // 禁用计划任务
        WPCA_Database_Settings::set_scheduled_cleanup(false);
        $this->assertFalse(WPCA_Database_Settings::is_scheduled_cleanup_enabled());
    }
    
    /**
     * 测试数据清理功能
     * 
     * @since 1.6.0
     */
    public function test_cleanup_functionality() {
        $database = new WPCA_Database();
        
        // 获取清理统计，不实际执行清理
        $stats = $database->get_cleanup_stats();
        
        $this->assertIsArray($stats);
        // 检查统计数据包含所需的键
        $expected_keys = array('revisions', 'auto_drafts', 'trashed_posts', 'spam_comments', 'trashed_comments', 'pingbacks', 'orphaned_postmeta', 'orphaned_commentmeta', 'orphaned_termmeta', 'orphaned_usermeta', 'expired_transients', 'all_transients', 'oembed_cache');
        
        foreach ($expected_keys as $key) {
            $this->assertArrayHasKey($key, $stats);
        }
    }
    
    /**
     * 测试设置保存
     * 
     * @since 1.6.0
     */
    public function test_settings_save() {
        $settings = new WPCA_Database_Settings();
        
        // 保存测试设置
        $test_settings = array(
            'enable_scheduled_cleanup' => true,
            'cleanup_frequency' => 'daily',
            'keep_days' => 30
        );
        
        $settings->save_settings($test_settings);
        
        // 获取设置并验证
        $saved_settings = $settings->get_settings();
        
        $this->assertEquals($test_settings['enable_scheduled_cleanup'], $saved_settings['enable_scheduled_cleanup']);
        $this->assertEquals($test_settings['cleanup_frequency'], $saved_settings['cleanup_frequency']);
        $this->assertEquals($test_settings['keep_days'], $saved_settings['keep_days']);
    }
    
    /**
     * 测试清理选项
     * 
     * @since 1.6.0
     */
    public function test_cleanup_options() {
        $database = new WPCA_Database();
        $options = $database->get_cleanup_options();
        
        $this->assertIsArray($options);
        $this->assertGreaterThan(0, count($options));
        
        // 检查每个选项是否包含必要的键
        foreach ($options as $option) {
            $this->assertArrayHasKey('id', $option);
            $this->assertArrayHasKey('name', $option);
            $this->assertArrayHasKey('description', $option);
        }
    }
}
?>
