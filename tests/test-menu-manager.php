<?php
/**
 * 菜单管理模块测试
 */

class WP_Clean_Admin_Menu_Test extends WP_UnitTestCase {
    protected $menu_manager;
    
    public function setUp() {
        parent::setUp();
        $this->menu_manager = WP_Clean_Admin_Menu::get_instance();
    }
    
    public function test_menu_hiding() {
        // 测试菜单隐藏功能
        $test_menu = 'tools.php';
        update_option('wpca_hidden_menus', [$test_menu]);
        
        // 触发菜单修改
        do_action('admin_menu');
        
        // 验证菜单是否被隐藏
        global $menu;
        $found = false;
        foreach ($menu as $item) {
            if ($item[2] === $test_menu) {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found, 'Menu should be hidden');
    }
    
    public function test_submenu_hiding() {
        // 测试子菜单隐藏功能
        $test_submenu = 'tools.php|tools.php';
        update_option('wpca_hidden_submenus', [$test_submenu]);
        
        // 触发菜单修改
        do_action('admin_menu');
        
        // 验证子菜单是否被隐藏
        global $submenu;
        $this->assertArrayNotHasKey('tools.php', $submenu, 'Submenu should be hidden');
    }
}