/**
 * WP Clean Admin Settings JavaScript (Refactored)
 *
 * This version removes localStorage to prevent data conflicts, simplifies tab handling,
 * and relies on a single source of truth (the database via PHP-rendered state).
 */
jQuery(document).ready(function($) {
    'use strict';

    // ==============================================
    // Pre-flight Checks & Global Variables
    // ==============================================

    // Ensure the localized object from PHP is available.
    if (typeof wpca_admin === 'undefined' || !wpca_admin.ajaxurl || !wpca_admin.nonce) {
        console.error('WPCA Script Error: The "wpca_admin" localization object is missing or incomplete.');
        // Display a persistent error on the page for the user.
        $('<div class="notice notice-error"><p><strong>WP Clean Admin Error:</strong> The required JavaScript settings could not be loaded. Some features may not work. Please try refreshing the page or contact support.</p></div>').prependTo('.wrap');
        return; // Halt execution.
    }

    var ajaxurl = wpca_admin.ajaxurl;
    var nonce = wpca_admin.nonce;

    // ==============================================
    // Notification Functions
    // ==============================================

    /**
     * Shows a dismissible notice.
     * @param {string} message - The message to display.
     * @param {string} type - 'success' or 'error'.
     */
    function showNotice(message, type = 'success') {
        $('.wpca-notice').remove();
        const noticeClass = `wpca-notice notice notice-${type} is-dismissible`;
        const $notice = $(`<div class="${noticeClass}"><p>${message}</p></div>`);
        $notice.insertAfter($('.wrap h1').first());

        // Auto-fade after a delay.
        setTimeout(() => $notice.fadeOut(500, () => $notice.remove()), type === 'success' ? 3000 : 5000);

        // Allow manual dismissal.
        $notice.on('click', '.notice-dismiss', function() {
            $notice.remove();
        });
    }

    // ======================================================
    // Settings Page Tab Navigation (Simplified)
    // ======================================================

    function initializeTabs() {
        const $tabs = $('.wpca-tab');
        const $tabContents = $('.wpca-tab-content');
        const $currentTabInput = $('#wpca-current-tab');

        // Set active tab based on hidden input value on page load.
        let currentTab = $currentTabInput.val() || $tabs.first().data('tab');
        if (!$("#" + currentTab).length) {
            currentTab = $tabs.first().data('tab');
        }

        $tabs.removeClass('active');
        $tabContents.removeClass('active');
        $(`.wpca-tab[data-tab="${currentTab}"]`).addClass('active');
        $(`#${currentTab}`).addClass('active');

        // Handle tab clicks.
        $tabs.on('click', function(e) {
            e.preventDefault();
            const tabId = $(this).data('tab');
            $tabs.removeClass('active');
            $tabContents.removeClass('active');

            $(this).addClass('active');
            $(`#${tabId}`).addClass('active');

            // Update hidden input. This value will be saved when the main form is submitted.
            // No AJAX call is needed here.
            $currentTabInput.val(tabId);
        });
    }
    initializeTabs();


    // ======================================================
    // Menu Sorting Functionality
    // ======================================================

    // Main menu sorting.
    $('#wpca-menu-order').sortable({
        items: 'li',
        handle: '.dashicons-menu',
        placeholder: 'wpca-sortable-placeholder',
        update: function() {
            const menuOrder = $(this).sortable('toArray', {
                attribute: 'data-menu-slug'
            });
            // Assumes a single hidden input named "wpca_settings[menu_order]"
            $('#wpca_menu_order_input').val(JSON.stringify(menuOrder));
        }
    });

    // Submenu sorting.
    $('.wpca-submenu-sortable').sortable({
        items: 'li',
        placeholder: 'wpca-sortable-placeholder',
        update: function() {
            const parentSlug = $(this).data('parent-slug');
            const submenuOrder = $(this).sortable('toArray', {
                attribute: 'data-menu-slug'
            });
            // Assumes a hidden input like name="wpca_settings[submenu_order][parent-slug]"
            const inputName = `wpca_settings[submenu_order][${parentSlug}]`;
            $(`input[name="${inputName}"]`).val(JSON.stringify(submenuOrder));
        }
    });


    // ======================================================
    // Menu Toggle Switch Functionality (No localStorage)
    // ======================================================

    $(document).on('change', '.wpca-menu-toggle-switch input[type="checkbox"]', function() {
        const $checkbox = $(this);
        const $switch = $checkbox.closest('.wpca-menu-toggle-switch');
        const slug = $checkbox.data('menu-slug'); // Assumes data-menu-slug is directly on the input.
        const isChecked = $checkbox.is(':checked');
        const state = isChecked ? 1 : 0;

        if (!slug) {
            console.error('WPCA Error: Menu slug not found on checkbox.');
            return;
        }

        // Optimistic UI update.
        updateMenuVisibility(slug, state);

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wpca_toggle_menu',
                slug: slug,
                state: state,
                nonce: nonce
            },
            beforeSend: () => $switch.addClass('loading'),
            complete: () => $switch.removeClass('loading'),
            dataType: 'json'
        }).done(function(response) {
            if (response && response.success) {
                showNotice('Setting saved.', 'success');
            } else {
                // On failure, revert the UI and show an error.
                const errorMessage = response.data?.message || 'An unknown error occurred.';
                showNotice(errorMessage, 'error');
                
                $checkbox.prop('checked', !isChecked);
                updateMenuVisibility(slug, !state);
            }
        }).fail(function() {
            // On catastrophic failure (e.g., server error), also revert and show an error.
            showNotice('Request failed. Please check your connection and try again.', 'error');
            
            $checkbox.prop('checked', !isChecked);
            updateMenuVisibility(slug, !state);
        });
    });

    /**
     * Instantly updates the visibility of the corresponding admin menu item in the sidebar.
     * This function only affects the *actual* admin menu, not the list on the settings page.
     * 
     * @param {string} slug - The menu slug to target
     * @param {boolean|number} isVisible - Whether the menu should be visible (true/1) or hidden (false/0)
     * @return {boolean} - Whether the update was successful
     */
    function updateMenuVisibility(slug, isVisible) {
        if (!slug) {
            console.warn('WPCA: Cannot update menu visibility - empty slug provided');
            return false;
        }
        
        // Convert to boolean to ensure consistent behavior
        isVisible = !!isVisible;
        
        // Try multiple selector strategies for better compatibility
        let $menuItem = null;
        
        // Strategy 1: Try exact ID match first (most reliable)
        const possibleIDs = [
            `#toplevel_page_${slug}`,
            `#menu-${slug}`,
            `#menu-posts-${slug}`
        ];
        
        for (const id of possibleIDs) {
            const $item = $(id);
            if ($item.length) {
                $menuItem = $item;
                break;
            }
        }
        
        // Strategy 2: If no direct ID match, try href matching (fallback)
        if (!$menuItem || !$menuItem.length) {
            // More precise href matching with URL path ending
            $menuItem = $(`#adminmenu a[href$="${slug}"], #adminmenu a[href*="page=${slug}"]`).closest('li');
        }
        
        // Apply visibility change if we found a matching menu item
        if ($menuItem && $menuItem.length) {
            // Add visual transition for smoother UX
            $menuItem.css('transition', 'opacity 0.3s');
            
            if (isVisible) {
                $menuItem.show().css('opacity', 1);
            } else {
                $menuItem.css('opacity', 0).delay(300).hide(0);
            }
            
            // Also update any related CSS classes
            $menuItem.toggleClass('wpca-hidden-menu', !isVisible);
            
            // Log success for debugging
            if (wpca_admin.debug) {
                console.log(`WPCA: Menu "${slug}" visibility set to ${isVisible ? 'visible' : 'hidden'}`);
            }
            
            return true;
        } else {
            // Log failure for debugging
            console.warn(`WPCA: Could not find menu item with slug "${slug}" to update visibility`);
            return false;
        }
    }
});