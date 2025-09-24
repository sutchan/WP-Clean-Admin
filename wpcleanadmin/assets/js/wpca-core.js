/**
 * WP Clean Admin - Core Module
 * 包含全局变量、通知功能和基础工具
 */

// 确保jQuery可用
if (typeof jQuery === 'undefined') {
    console.error('WP Clean Admin 错误: jQuery 未加载');
    throw new Error('jQuery is required for WP Clean Admin');
}

// 创建全局命名空间
window.WPCA = window.WPCA || {};

// 核心模块
WPCA.core = {
    // 配置对象
    config: {
        ajaxurl: '',
        nonce: '',
        debug: false,
        version: '1.0.0',
        loadedModules: []
    },
    
    // 状态标志
    isInitialized: false,
    hasErrors: false,
    
    // 获取全局配置，从多个来源合并
    getGlobalConfig: function() {
        // 从多个可能的来源合并配置
        const sources = [
            window.wpca_admin || {},
            window.wpca_settings || {},
            window.wpcaLoginVars || {}
        ];
        
        const mergedConfig = {};
        sources.forEach(source => {
            if (typeof source === 'object' && source !== null) {
                Object.keys(source).forEach(key => {
                    mergedConfig[key] = source[key];
                });
            }
        });
        
        // 确保ajaxurl始终可用
        if (!mergedConfig.ajaxurl) {
            mergedConfig.ajaxurl = (typeof ajaxurl !== 'undefined') ? ajaxurl : '/wp-admin/admin-ajax.php';
        }
        
        return mergedConfig;
    },

    // 初始化检查
    initCheck: function() {
        // 获取合并的配置
        const config = this.getGlobalConfig();
        this.config = {...this.config, ...config};
        
        // 验证必要的配置
        if (!this.config.ajaxurl) {
            this.logError('缺少 AJAX URL 设置');
            this.showNotice('error', '无法加载必要的 JavaScript 设置。请刷新页面重试。');
            this.hasErrors = true;
            return false;
        }
        
        // 提供默认的nonce（在AJAX请求中仍然会验证）
        if (!this.config.nonce) {
            this.config.nonce = typeof wpApiSettings !== 'undefined' ? wpApiSettings.nonce : '';
            this.logWarning('使用备用 nonce 值');
        }
        
        this.isInitialized = true;
        return true;
    },
    
    // 基础 AJAX 请求封装
    ajaxRequest: function(action, data = {}, options = {}) {
        // 确保核心已初始化
        if (!this.isInitialized && !this.initCheck()) {
            return Promise.reject(new Error('WPCA 核心未初始化'));
        }
        
        // 默认选项
        const defaultOptions = {
            type: 'POST',
            url: this.config.ajaxurl,
            dataType: 'json',
            beforeSend: (xhr) => {
                // 添加 nonce 到请求头
                if (this.config.nonce) {
                    xhr.setRequestHeader('X-WP-Nonce', this.config.nonce);
                }
            },
            error: (xhr, status, error) => {
                this.handleAjaxError(xhr, status, error);
            }
        };
        
        // 合并选项和数据
        const mergedOptions = {...defaultOptions, ...options};
        mergedOptions.data = {...mergedOptions.data, ...data, action: action};
        
        // 添加调试信息
        this.logDebug('AJAX 请求:', mergedOptions);
        
        return jQuery.ajax(mergedOptions);
    },
    
    // AJAX 错误处理
    handleAjaxError: function(xhr, status, error) {
        let errorMessage = '请求处理失败';
        
        if (xhr.status === 403) {
            errorMessage = '您没有执行此操作的权限';
        } else if (xhr.status === 400) {
            errorMessage = '请求参数错误';
        } else if (xhr.status === 401) {
            errorMessage = '请先登录';
        } else if (xhr.status === 500) {
            errorMessage = '服务器内部错误';
        }
        
        this.logError('AJAX 错误:', error, '状态:', xhr.status);
        this.showNotice('error', errorMessage);
    },
    
    // 显示通知
    showNotice: function(type, message, duration = 3000) {
        // 确保在管理界面中才显示通知
        if (jQuery('.wrap').length) {
            const noticeClass = type === 'error' ? 'notice-error' : 
                              type === 'success' ? 'notice-success' : 
                              type === 'warning' ? 'notice-warning' : 'notice-info';
            
            const notice = jQuery(`<div class="notice ${noticeClass} is-dismissible"><p><strong>WP Clean Admin:</strong> ${message}</p></div>`);
            notice.prependTo('.wrap').hide().fadeIn();
            
            // 自动关闭（除了错误通知）
            if (type !== 'error') {
                setTimeout(() => {
                    notice.fadeOut('slow', () => notice.remove());
                }, duration);
            }
            
            // 添加关闭功能
            notice.on('click', '.notice-dismiss', function() {
                notice.fadeOut('slow', () => notice.remove());
            });
        } else {
            // 如果无法显示通知，则使用alert
            alert(message);
        }
    },
    
    // 调试日志函数
    logDebug: function(...args) {
        if (this.config.debug && window.console && console.log) {
            console.log('WPCA [DEBUG]:', ...args);
        }
    },
    
    logWarning: function(...args) {
        if (window.console && console.warn) {
            console.warn('WPCA [WARNING]:', ...args);
        }
    },
    
    logError: function(...args) {
        if (window.console && console.error) {
            console.error('WPCA [ERROR]:', ...args);
        }
    },

    /**
     * 初始化核心功能
     */
    init: function() {
        // 防止重复初始化
        if (this.isInitialized) {
            this.logWarning('核心已经初始化');
            return;
        }
        
        // 执行初始化检查
        const success = this.initCheck();
        
        if (success) {
            this.logDebug('核心初始化成功');
        } else {
            this.logError('核心初始化失败');
        }
        
        // 注册全局事件
        this.registerGlobalEvents();
    },
    
    // 注册全局事件
    registerGlobalEvents: function() {
        // 监听AJAX完成事件，用于调试
        if (this.config.debug) {
            jQuery(document).ajaxComplete((event, xhr, settings) => {
                if (settings.url === this.config.ajaxurl && settings.data && settings.data.includes('action=')) {
                    this.logDebug('AJAX 完成:', settings.data.substring(0, 100) + (settings.data.length > 100 ? '...' : ''));
                }
            });
        }
    },
    
    // 模块注册功能
    registerModule: function(moduleName, module) {
        if (!WPCA[moduleName]) {
            WPCA[moduleName] = module;
            this.config.loadedModules.push(moduleName);
            this.logDebug('模块已注册:', moduleName);
            return true;
        } else {
            this.logWarning('模块名称已存在:', moduleName);
            return false;
        }
    }
};

// 为了向后兼容，保留一些全局变量
window.wpca = {
    ajaxurl: WPCA.core.config.ajaxurl,
    nonce: WPCA.core.config.nonce,
    debug: WPCA.core.config.debug,
    // 提供向后兼容的AJAX方法
    ajax: function(action, data, options) {
        return WPCA.core.ajaxRequest(action, data, options);
    }
};

// 页面加载完成后初始化核心功能
jQuery(document).ready(function() {
    try {
        WPCA.core.init();
    } catch (error) {
        console.error('WPCA 初始化异常:', error);
        // 显示基本错误通知
        jQuery('<div class="notice notice-error"><p><strong>WP Clean Admin 初始化失败:</strong> ' + error.message + '</p></div>')
            .prependTo('.wrap');
    }
});