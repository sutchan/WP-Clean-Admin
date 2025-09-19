/**
 * WP Clean Admin - 菜单排序模块
 */

// 确保WPCA命名空间存在
window.WPCA = window.WPCA || {};

// 菜单排序模块
WPCA.menuSorting = {
    init: function() {
        const $ = jQuery;
        
        // 主菜单排序
        $('#wpca-menu-order').sortable({
            items: 'li',
            handle: '.dashicons-menu',
            placeholder: 'wpca-sortable-placeholder',
            update: function() {
                const menuOrder = $(this).sortable('toArray', {
                    attribute: 'data-menu-slug'
                });
                $('#wpca_menu_order_input').val(JSON.stringify(menuOrder));
            }
        });

        // 子菜单排序
        $('.wpca-submenu-sortable').sortable({
            items: 'li',
            placeholder: 'wpca-sortable-placeholder',
            update: function() {
                const parentSlug = $(this).data('parent-slug');
                const submenuOrder = $(this).sortable('toArray', {
                    attribute: 'data-menu-slug'
                });
                const inputName = `wpca_settings[submenu_order][${parentSlug}]`;
                $(`input[name="${inputName}"]`).val(JSON.stringify(submenuOrder));
            }
        });
    }
};

// 页面加载完成后初始化
jQuery(document).ready(function() {
    WPCA.menuSorting.init();
});