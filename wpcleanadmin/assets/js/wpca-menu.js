/**
 * WP Clean Admin - Menu Management Module
 * Combines menu sorting and menu toggle functionality
 * 
 * @file       wpcleanadmin/assets/js/wpca-menu.js
 * @version    1.7.13
 * @updated    2025-06-18
 */

// Ensure WPCA namespace exists
window.WPCA = window.WPCA || {};

/**
 * Menu management module
 */
WPCA.menu = {
    config: null, // Will store configuration

    /**
     * Initialize menu management functionality
     * @returns {boolean} - True on success, false on failure.
     */
    init: function() {
        const $ = jQuery;

        // --- Configuration retrieval and validation ---
        if (typeof window.wpca_admin === 'undefined') {
            console.error('WPCA Error: The configuration object "wpca_admin" is missing.');
            if (window.WPCA?.core?.displayErrorNotice) {
                WPCA.core.displayErrorNotice('Menu management configuration failed to load');
            }
            return false;
        }
        this.config = window.wpca_admin;

        if (this.config.debug) {
            console.log('WPCA Menu Management: Initializing...');
        }

        // --- Dependency check ---
        if (typeof $.ui === 'undefined' || typeof $.ui.sortable === 'undefined') {
            console.error('WPCA Error: jQuery UI Sortable is not loaded.');
            if (window.WPCA?.core?.displayErrorNotice) {
                WPCA.core.displayErrorNotice('Menu management requires jQuery UI Sortable, please ensure it is loaded');
            }
            return false;
        }

        // --- Initialize menu sorting functionality ---
        this.initMenuSorting();

        // --- Initialize menu toggle functionality ---
        this.initMenuToggle();

        if (this.config.debug) {
            console.log('WPCA Menu Management: Initialized successfully.');
        }

        return true;
    },

    /**
     * Initialize menu sorting functionality
     */
    initMenuSorting: function() {
        const $ = jQuery;

        // Main menu sorting
        $('#wpca-menu-order').sortable({
            items: 'li.wpca-top-level-item', // More precise selector to prevent submenu items from being dragged to top level
            handle: '.wpca-drag-handle',
            placeholder: 'wpca-sortable-placeholder',
            axis: 'y',
            update: function() {
                const menuOrder = $(this).sortable('toArray', {
                    attribute: 'data-menu-slug'
                });
                // Store the sorted array (as JSON string) in a hidden input for form submission
                $('#wpca-menu-order-input').val(JSON.stringify(menuOrder));
            }
        });

        // Submenu sorting
        $('.wpca-submenu-sortable').sortable({
            items: 'li.wpca-submenu-item',
            handle: '.wpca-drag-handle',
            placeholder: 'wpca-sortable-placeholder',
            axis: 'y',
            update: function() {
                const parentSlug = $(this).data('parent-slug');
                const submenuOrder = $(this).sortable('toArray', {
                    attribute: 'data-menu-slug'
                });
                // Find the corresponding hidden input and update its value
                const inputName = `wpca_settings[submenu_order][${parentSlug}]`;
                $(`input[name="${inputName}"`).val(JSON.stringify(submenuOrder));
            }
        });

        if (this.config.debug) {
            console.log('WPCA Menu Sorting: Initialized successfully.');
        }
    },

    /**
     * Initialize menu toggle functionality
     */
    initMenuToggle: function() {
        const $ = jQuery;

        // Bind event listener to document to support dynamically added elements
        $(document).on('change', '.wpca-menu-toggle-checkbox', this.handleToggleChange.bind(this));

        if (this.config.debug) {
            console.log('WPCA Menu Toggle: Initialized successfully.');
        }
    },

    /**
     * Event callback for handling toggle state changes
     * @param {Event} e - The change event object.
     */
    handleToggleChange: function(e) {
        const $checkbox = jQuery(e.currentTarget);
        const $switch = $checkbox.closest('.wpca-switch');
        
        // Depends on backend providing slug on checkbox
        const slug = $checkbox.data('menu-slug');
        const isChecked = $checkbox.is(':checked');
        const state = isChecked ? 1 : 0;

        if (!slug) {
            console.error('WPCA Error: Menu slug is missing from the checkbox data attribute.');
            if (typeof WPCA !== 'undefined' && typeof WPCA.core !== 'undefined' && typeof WPCA.core.displayErrorNotice === 'function') {
                WPCA.core.displayErrorNotice('Menu slug is missing, please refresh the page and try again');
            }
            return;
        }

        if (this.config.debug) {
            console.log('Toggle changed:', { slug, state });
        }
        
        // Optimistic UI update: immediately update the left menu visibility
        this.updateAdminMenuVisibility(slug, isChecked);
        
        $switch.addClass('loading');

        // Use Promise-based AJAX call
        this.sendToggleRequest(slug, state)
            .done((response) => {
                if (response && response.success) {
                    if (typeof WPCA !== 'undefined' && typeof WPCA.core !== 'undefined' && typeof WPCA.core.displaySuccessNotice === 'function') {
                        WPCA.core.displaySuccessNotice('Settings saved');
                    }
                } else {
                    // If backend returns failure, rollback UI
                    const errorMessage = response?.data?.message || 'An unknown error occurred';
                    if (typeof WPCA !== 'undefined' && typeof WPCA.core !== 'undefined' && typeof WPCA.core.displayErrorNotice === 'function') {
                        WPCA.core.displayErrorNotice(errorMessage);
                    }
                    this.revertToggleState($checkbox, isChecked);
                }
            })
            .fail((xhr) => {
                // If request itself fails (network, server error), also rollback UI
                const message = this.getErrorMessageFromXHR(xhr);
                if (typeof WPCA !== 'undefined' && typeof WPCA.core !== 'undefined' && typeof WPCA.core.displayErrorNotice === 'function') {
                    WPCA.core.displayErrorNotice(message);
                }
                this.revertToggleState($checkbox, isChecked);
            })
            .always(() => {
                $switch.removeClass('loading');
            });
    },

    /**
     * Send AJAX request and return a Promise object
     * @param {string} slug - The menu slug.
     * @param {number} state - The new state (1 for visible, 0 for hidden).
     * @returns {jqXHR} - The jQuery AJAX promise.
     */
    sendToggleRequest: function(slug, state) {
        return jQuery.ajax({
            url: this.config.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'wpca_toggle_menu',
                nonce: this.config.nonce,
                slug: slug,
                state: state
            }
        });
    },

    /**
     * Rollback UI state for toggle and menu
     * @param {jQuery} $checkbox - The checkbox element.
     * @param {boolean} originalIsChecked - The state before the user's action.
     */
    revertToggleState: function($checkbox, originalIsChecked) {
        const slug = $checkbox.data('menu-slug');
        $checkbox.prop('checked', !originalIsChecked);
        this.updateAdminMenuVisibility(slug, !originalIsChecked);
    },

    /**
     * Generate user-friendly error message based on XHR object
     * @param {jqXHR} xhr - The jQuery XHR object from a failed request.
     * @returns {string} - The error message.
     */
    getErrorMessageFromXHR: function(xhr) {
        if (xhr.status === 403) {
            return 'Insufficient permissions or security verification failed. The page will refresh in 2 seconds.';
        }
        if (xhr.status === 0) {
            return 'Network connection error, please check your network and try again.';
        }
        return `Request failed, server returned error code: ${xhr.status}`;
    },

    /**
     * Update visibility of WordPress admin left menu
     * @param {string} slug - The menu slug to update.
     * @param {boolean} isVisible - Whether the menu should be visible.
     */
    updateAdminMenuVisibility: function(slug, isVisible) {
        if (!slug) return;
        
        // Finding logic remains the same as it's robust
        const $menuItem = this.findMenuItem(jQuery, slug);

        if ($menuItem.length) {
            if (this.config.debug) {
                console.log(`Updating visibility for "${slug}" to ${isVisible}`);
            }
            // Using CSS transitions is better performance than jQuery animations
            $menuItem.toggleClass('wpca-hidden-menu', !isVisible);
        } else if (this.config.debug) {
            console.warn(`Could not find admin menu item for slug: "${slug}"`);
        }
    },

    /**
     * Helper function to find menu items
     * @param {jQuery} $ - The jQuery object.
     * @param {string} slug - The menu slug to find.
     * @returns {jQuery} - The found menu item element.
     */
    findMenuItem: function($, slug) {
        // Try to find menu item through multiple selectors
        const selectors = [
            `#toplevel_page_${slug}`,
            `#menu-posts-${slug}`,
            `#menu-${slug.replace(/\.php.*/, '')}`, // Match edit.php, themes.php etc
            `li a[href$="page=${slug}"]`,
            `li a[href$="${slug}"]`,
        ];
        
        for (const selector of selectors) {
            const $item = $(selector).closest('li');
            if ($item.length) {
                return $item;
            }
        }
        return $(); // Return an empty jQuery object
    }
};

// Initialize after page load
jQuery(document).ready(function() {
    if (WPCA.menu?.init) {
        WPCA.menu.init();
    } else {
        console.error('WPCA Error: Menu management module failed to load.');
        if (typeof WPCA !== 'undefined' && typeof WPCA.core !== 'undefined' && typeof WPCA.core.displayErrorNotice === 'function') {
            WPCA.core.displayErrorNotice('Menu management initialization failed');
        }
    }
});