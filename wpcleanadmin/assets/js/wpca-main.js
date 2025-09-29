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
        version: '1.2.0',
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
            this.logWarning('Core has already been initialized');
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
            this.logError('Core initialization failed');
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
            this.logError('Missing AJAX URL setting');
            // 使用翻译后的消息
            this.showNotice('error', wpca_admin.error_js_settings_missing || 'Unable to load necessary JavaScript settings. Please refresh the page and try again.');
            this.hasErrors = true;
            return false;
        }
        
        // Provide default nonce (still verified in AJAX request)
        if (!this.config.nonce) {
            this.config.nonce = typeof wpApiSettings !== 'undefined' ? wpApiSettings.nonce : '';
            this.logWarning('Using fallback nonce value');
        }
        
        this.isInitialized = true;
        return true;
    },
    
    // Basic AJAX request encapsulation
    ajaxRequest: function(action, data = {}, options = {}) {
        // Ensure core is initialized
        if (!this.isInitialized && !this.initCheck()) {
            return Promise.reject(new Error('WPCA core not initialized'));
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
        this.logDebug('AJAX request:', mergedOptions);
        
        return jQuery.ajax(mergedOptions);
    },
    
    // AJAX error handling
    handleAjaxError: function(xhr, status, error) {
        let errorMessage = wpca_admin.error_request_processing_failed || 'Request processing failed';
        
        if (xhr.status === 403) {
            errorMessage = wpca_admin.error_insufficient_permissions || 'You do not have permission to perform this action';
        } else if (xhr.status === 400) {
            errorMessage = wpca_admin.error_invalid_parameters || 'Invalid request parameters';
        } else if (xhr.status === 401) {
            errorMessage = wpca_admin.error_not_logged_in || 'Please log in first';
        } else if (xhr.status === 500) {
            errorMessage = wpca_admin.error_server_error || 'Internal server error';
        }
        
        this.logError('AJAX error:', error, 'Status:', xhr.status);
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
            
            // 获取翻译后的插件名称
            let pluginName = wpca_admin.plugin_name || 'WP Clean Admin';
            
            const notice = jQuery(`<div class="wpca-notice ${noticeClass}"><p><strong>${pluginName}:</strong> ${message}</p></div>`);
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
                    this.logDebug('AJAX completed:', settings.data.substring(0, 100) + (settings.data.length > 100 ? '...' : ''));
                }
            });
        }
    },
    
    // Module registration functionality
    registerModule: function(moduleName, module) {
        if (!WPCA[moduleName]) {
            WPCA[moduleName] = module;
            this.config.loadedModules.push(moduleName);
            this.logDebug('Module registered:', moduleName);
            return true;
        } else {
            this.logWarning('Module name already exists:', moduleName);
            return false;
        }
    }
};

// Keep some global variables for backward compatibility
window.wpca = {
    ajaxurl: WPCA.core.config.ajaxurl,
    nonce: WPCA.core.config.nonce,
    debug: WPCA.core.config.debug,
    // Provide backward compatible AJAX method
    ajax: function(action, data, options) {
        return WPCA.core.ajaxRequest(action, data, options);
    }
};

// Initialize core functionality after page loads
jQuery(document).ready(function() {
    try {
        WPCA.core.init();
    } catch (error) {
        console.error('WPCA initialization exception:', error);
        // Display basic error notification
        jQuery('<div class="notice notice-error"><p><strong>' + (wpca_admin.error_initialization_failed || 'WP Clean Admin initialization failed:') + '</strong> ' + error.message + '</p></div>')
            .prependTo('.wrap');
    }
});