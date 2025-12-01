/**
 * WP Clean Admin Main JavaScript
 *
 * @package WPCA
 */

// Global WPCA object
window.WPCleanAdmin = {
    /**
     * Initialize the plugin
     */
    init: function() {
        this.initTabs();
        this.initSettings();
        this.initAjax();
    },
    
    /**
     * Initialize tabs functionality
     */
    initTabs: function() {
        // Tab switching functionality
        jQuery(document).on('click', '.wpca-tabs-nav li a', function(e) {
            e.preventDefault();
            
            var tabId = jQuery(this).attr('href');
            var tabContainer = jQuery(this).closest('.wpca-tabs');
            
            // Remove active classes
            tabContainer.find('.wpca-tabs-nav li').removeClass('active');
            tabContainer.find('.wpca-tab-content').removeClass('active');
            
            // Add active classes
            jQuery(this).parent().addClass('active');
            tabContainer.find(tabId).addClass('active');
        });
    },
    
    /**
     * Initialize settings functionality
     */
    initSettings: function() {
        // Settings form submission
        jQuery(document).on('submit', '.wpca-settings-form', function(e) {
            // Add loading state
            jQuery(this).find('input[type="submit"]').prop('disabled', true).val(wpca_settings_vars.saving || 'Saving...');
        });
    },
    
    /**
     * Initialize AJAX functionality
     */
    initAjax: function() {
        // AJAX error handling
        jQuery(document).ajaxError(function(event, xhr, settings, error) {
            console.error('WPCA AJAX Error:', error);
            alert(wpca_settings_vars.ajax_error || 'An AJAX error occurred. Please try again.');
        });
    }
};

// Initialize when DOM is ready
jQuery(document).ready(function() {
    window.WPCleanAdmin.init();
});
