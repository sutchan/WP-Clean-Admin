/**
 * WP Clean Admin 管理界面脚本
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // 初始化选项卡
    if ($.fn.tabs) {
        $('.wpca-tabs').tabs();
    }
    
    // 初始化菜单排序
    if ($.fn.sortable) {
        $('#menu-sortable').sortable({
            placeholder: 'ui-state-highlight',
            update: function(event, ui) {
                // 更新隐藏字段的顺序
                var newOrder = [];
                $('#menu-sortable .menu-item').each(function() {
                    newOrder.push($(this).data('slug'));
                });
                
                // 如果有隐藏字段，更新它的值
                if ($('#wpca_menu_order_data').length) {
                    $('#wpca_menu_order_data').val(JSON.stringify(newOrder));
                }
            }
        }).disableSelection();
    }
    
    // 切换菜单项可见性
    $('.wpca-menu-visibility input[type="checkbox"], .wpca-submenu-visibility input[type="checkbox"]').on('change', function() {
        var $this = $(this);
        var isChecked = $this.is(':checked');
        
        if (isChecked) {
            $this.closest('label').addClass('menu-hidden');
        } else {
            $this.closest('label').removeClass('menu-hidden');
        }
    });
    
    // 初始化已隐藏的菜单项样式
    $('.wpca-menu-visibility input[type="checkbox"]:checked, .wpca-submenu-visibility input[type="checkbox"]:checked').each(function() {
        $(this).closest('label').addClass('menu-hidden');
    });
    
    // 主题预览
    $('#wpca_theme').on('change', function() {
        var theme = $(this).val();
        $('.theme-preview').removeClass('active');
        $('#theme-preview-' + theme).addClass('active');
    });
    
    // 布局密度预览
    $('#wpca_layout_density').on('change', function() {
        var density = $(this).val();
        $('.density-preview').removeClass('active');
        $('#density-preview-' + density).addClass('active');
    });
});