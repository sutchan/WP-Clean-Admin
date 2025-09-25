/**
 * WP Clean Admin - Tab Navigation Module
 */

// Ensure WPCA namespace exists
window.WPCA = window.WPCA || {};

// Tab navigation module
WPCA.tabs = {
    init: function() {
        const $ = jQuery;
        const $tabs = $('.wpca-tab');
        const $tabContents = $('.wpca-tab-content');
        const $currentTabInput = $('#wpca-current-tab');

        // If there are no tabs, return directly
        if ($tabs.length === 0) {
            return;
        }

        // Get current active tab
        let activeTab = $currentTabInput.val() || $tabs.first().data('tab');
        if (!$("#" + activeTab).length) {
            activeTab = $tabs.first().data('tab');
        }

        // Set initial active tab
        $tabs.removeClass('active');
        $tabContents.removeClass('active');
        $(`.wpca-tab[data-tab="${activeTab}"]`).addClass('active');
        $(`#${activeTab}`).addClass('active');

        // Unbind previous click events to prevent duplicate binding
        $tabs.off('click.wpca');
        
        // Bind click event
        $tabs.on('click.wpca', function(e) {
            e.preventDefault();
            const tabId = $(this).data('tab');
            
            // Update active status
            $tabs.removeClass('active');
            $tabContents.removeClass('active');

            $(this).addClass('active');
            $(`#${tabId}`).addClass('active');
            $currentTabInput.val(tabId);
            
            // Trigger custom event so other scripts can respond to tab switching
            $(document).trigger('wpca.tab.changed', [tabId]);
        });
    },
    
    // Reinitialize tabs
    reinit: function() {
        this.init();
    }
};

// Initialize after page load
jQuery(document).ready(function($) {
    // Use a slight delay to ensure DOM is fully loaded
    setTimeout(function() {
        if (typeof WPCA !== 'undefined' && typeof WPCA.tabs !== 'undefined') {
            WPCA.tabs.init();
        }
    }, 100);
});

// Also listen for custom initialization event
jQuery(document).on('wpca.init.tabs', function() {
    if (typeof WPCA !== 'undefined' && typeof WPCA.tabs !== 'undefined') {
        WPCA.tabs.init();
    }
});