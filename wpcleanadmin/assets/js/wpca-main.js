/**
 * WP Clean Admin - Core Module
 * Contains global variables, notification functions and basic utilities
 */

// Ensure jQuery is available
if (typeof jQuery === 'undefined') {
    console.error('WP Clean Admin Error: jQuery is not loaded');
    throw new Error('jQuery is required for WP Clean Admin');
}

// Initialize WPCA namespace
window.WPCA = window.WPCA || {};

/**
 * Core module
 */
WPCA.core = {
    // Configuration object
    config: {
        ajaxurl: '',
        nonce: '',
        debug: false,
        version: '1.1.1',
        loadedModules: []
    },
    
    // Status flags
    isInitialized: false,
    hasErrors: false,

    /**
     * Initialize core functionality
     */
    init: function() {
        // Prevent reinitialization
        if (this.isInitialized) {
            this.logWarning('核心已经初始化');
            return;
        }
        
        // Configuration retrieval and validation
        if (typeof window.wpca_admin === 'undefined') {
            console.error('WPCA Error: The configuration object "wpca_admin" is missing.');
            this.hasErrors = true;
            return false;
        }
        
        // Execute initialization check
        const success = this.initCheck();
        
        if (success) {
            // Initialize debug mode
            this.initDebugMode();
            
            if (this.config.debug) {
                console.log('WPCA Core: Initialized successfully.');
            }
            
            // Register global events
            this.registerGlobalEvents();
        } else {
            this.logError('核心初始化失败');
        }
        
        return success;
    },

    /**
     * Initialize debug mode
     */
    initDebugMode: function() {
        if (this.config.debug) {
            window.WPCA_DEBUG = true;
        }
    },

    /**
     * Get global configuration, merged from multiple sources
     */
    getGlobalConfig: function() {
        // Merge configuration from multiple possible sources
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
        
        // Ensure ajaxurl is always available
        if (!mergedConfig.ajaxurl) {
            mergedConfig.ajaxurl = (typeof ajaxurl !== 'undefined') ? ajaxurl : '/wp-admin/admin-ajax.php';
        }
        
        return mergedConfig;
    },

    // Initialization check
    initCheck: function() {
        // Get merged configuration
        const config = this.getGlobalConfig();
        this.config = {...this.config, ...config};
        
        // Validate necessary configuration
        if (!this.config.ajaxurl) {
            this.logError('缺少 AJAX URL 设置');
            this.showNotice('error', '无法加载必要的 JavaScript 设置。请刷新页面重试。');
            this.hasErrors = true;
            return false;
        }
        
        // Provide default nonce (still verified in AJAX request)
        if (!this.config.nonce) {
            this.config.nonce = typeof wpApiSettings !== 'undefined' ? wpApiSettings.nonce : '';
            this.logWarning('使用备用 nonce 值');
        }
        
        this.isInitialized = true;
        return true;
    },
    
    // Basic AJAX request encapsulation
    ajaxRequest: function(action, data = {}, options = {}) {
        // Ensure core is initialized
        if (!this.isInitialized && !this.initCheck()) {
            return Promise.reject(new Error('WPCA 核心未初始化'));
        }
        
        // Default options
        const defaultOptions = {
            type: 'POST',
            url: this.config.ajaxurl,
            dataType: 'json',
            beforeSend: (xhr) => {
                // Add nonce to request header
                if (this.config.nonce) {
                    xhr.setRequestHeader('X-WP-Nonce', this.config.nonce);
                }
            },
            error: (xhr, status, error) => {
                this.handleAjaxError(xhr, status, error);
            }
        };
        
        // Merge options and data
        const mergedOptions = {...defaultOptions, ...options};
        mergedOptions.data = {...mergedOptions.data, ...data, action: action};
        
        // Add debugging information
        this.logDebug('AJAX 请求:', mergedOptions);
        
        return jQuery.ajax(mergedOptions);
    },
    
    // AJAX error handling
    handleAjaxError: function(xhr, status, error) {
        let errorMessage = wpca_admin.error_request_processing_failed || '请求处理失败';
        
        if (xhr.status === 403) {
            errorMessage = wpca_admin.error_insufficient_permissions || '您没有执行此操作的权限';
        } else if (xhr.status === 400) {
            errorMessage = wpca_admin.error_invalid_parameters || '请求参数错误';
        } else if (xhr.status === 401) {
            errorMessage = wpca_admin.error_not_logged_in || '请先登录';
        } else if (xhr.status === 500) {
            errorMessage = wpca_admin.error_server_error || '服务器内部错误';
        }
        
        this.logError('AJAX 错误:', error, '状态:', xhr.status);
        this.showNotice('error', errorMessage);
    },

    /**
     * Log debug information if debug mode is enabled
     * @param {*} message - The message to log
     */
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
     * Display notification
     */
    showNotice: function(type, message, duration = 3000) {
        // Ensure notification is displayed only in management interface
        if (jQuery('.wrap').length) {
            // Use custom WPCA notice classes that match our CSS
            const noticeClass = type === 'error' ? 'error' : 
                              type === 'success' ? 'success' : 
                              type === 'warning' ? 'warning' : 'info';
            
            const notice = jQuery(`<div class="wpca-notice ${noticeClass}"><p><strong>WP Clean Admin:</strong> ${message}</p></div>`);
            notice.prependTo('.wrap').hide().fadeIn();
            
            // Automatically close (except error notifications)
            if (type !== 'error') {
                setTimeout(() => {
                    notice.fadeOut('slow', () => notice.remove());
                }, duration);
            }
        } else {
            // If unable to display notification, use alert
            alert(message);
        }
    },

    /**
     * Display an error notice
     * @param {string} message - The error message
     */
    displayErrorNotice: function(message) {
        // Keep for backward compatibility
        this.showNotice('error', message, 5000);
    },

    /**
     * Display a success notice
     * @param {string} message - The success message
     */
    displaySuccessNotice: function(message) {
        // Keep for backward compatibility
        this.showNotice('success', message, 3000);
    },
    
    // Register global events
    registerGlobalEvents: function() {
        // Listen to AJAX complete event, for debugging
        if (this.config.debug) {
            jQuery(document).ajaxComplete((event, xhr, settings) => {
                if (settings.url === this.config.ajaxurl && settings.data && settings.data.includes('action=')) {
                    this.logDebug('AJAX 完成:', settings.data.substring(0, 100) + (settings.data.length > 100 ? '...' : ''));
                }
            });
        }
    },
    
    // Module registration functionality
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