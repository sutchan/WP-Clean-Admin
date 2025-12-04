/**
 * WP Clean Admin Database JavaScript
 *
 * @package WPCA
 * @version 1.7.15
 * @author Sut
 * @author URI: https://github.com/sutchan
 * @since 1.7.15
 */
// Use IIFE to encapsulate the database functionality
(function($) {
    'use strict';
    
    /**
     * Database functionality for WPCA
     */
    const WPCADatabase = {
        /**
         * Initialize database functionality
         */
        init: function() {
            this.bindEvents();
            this.initDatabaseTables();
        },
        
        /**
         * Bind events for database
         */
        bindEvents: function() {
            // Handle database optimization buttons
            $(document).on('click', '.wpca-db-optimize-btn', this.handleDatabaseOptimize.bind(this));
            
            // Handle database cleanup buttons
            $(document).on('click', '.wpca-db-cleanup-btn', this.handleDatabaseCleanup.bind(this));
            
            // Handle database backup buttons
            $(document).on('click', '.wpca-db-backup-btn', this.handleDatabaseBackup.bind(this));
        },
        
        /**
         * Initialize database tables display
         */
        initDatabaseTables: function() {
            // Add visual indicators for database table states
            $('.wpca-db-table').each(function() {
                const $table = $(this);
                WPCADatabase.updateTableVisuals($table);
            });
        },
        
        /**
         * Handle database optimization buttons
         * @param {Event} e - The click event
         */
        handleDatabaseOptimize: function(e) {
            e.preventDefault();
            
            const $btn = $(e.currentTarget);
            const tableName = $btn.data('table') || 'selected tables';
            const confirmText = $btn.data('confirm') || `Are you sure you want to optimize ${tableName}? This may take a few moments.`;
            
            if (confirm(confirmText)) {
                this.performDatabaseAction($btn, 'optimize');
            }
        },
        
        /**
         * Handle database cleanup buttons
         * @param {Event} e - The click event
         */
        handleDatabaseCleanup: function(e) {
            e.preventDefault();
            
            const $btn = $(e.currentTarget);
            const cleanupType = $btn.data('cleanup-type') || 'general';
            const confirmText = $btn.data('confirm') || `Are you sure you want to perform ${cleanupType} cleanup? This action cannot be undone.`;
            
            if (confirm(confirmText)) {
                this.performDatabaseAction($btn, 'cleanup');
            }
        },
        
        /**
         * Handle database backup buttons
         * @param {Event} e - The click event
         */
        handleDatabaseBackup: function(e) {
            e.preventDefault();
            
            const $btn = $(e.currentTarget);
            const backupType = $btn.data('backup-type') || 'full';
            const confirmText = $btn.data('confirm') || `Are you sure you want to create a ${backupType} database backup? This may take a few moments.`;
            
            if (confirm(confirmText)) {
                this.performDatabaseAction($btn, 'backup');
            }
        },
        
        /**
         * Perform database actions
         * @param {jQuery} $btn - The action button
         * @param {string} action - The action type
         */
        performDatabaseAction: function($btn, action) {
            const originalText = $btn.text();
            const processingText = $btn.data('processing-text') || `${action.charAt(0).toUpperCase() + action.slice(1)}ing...`;
            
            // Add loading state
            $btn.prop('disabled', true).text(processingText);
            
            // Trigger custom event for database action
            $(document).trigger('wpca_database_action_started', { 
                action: action, 
                button: $btn 
            });
        },
        
        /**
         * Update visual feedback for database tables
         * @param {jQuery} $table - The table element
         */
        updateTableVisuals: function($table) {
            // Update table visual indicators based on size and status
            const size = parseInt($table.data('size') || 0, 10);
            const status = $table.data('status') || 'normal';
            
            $table.removeClass('wpca-db-table-small wpca-db-table-medium wpca-db-table-large wpca-db-table-status-normal wpca-db-table-status-warning wpca-db-table-status-error');
            
            // Add size class
            if (size < 1024) {
                $table.addClass('wpca-db-table-small');
            } else if (size < 10240) {
                $table.addClass('wpca-db-table-medium');
            } else {
                $table.addClass('wpca-db-table-large');
            }
            
            // Add status class
            $table.addClass(`wpca-db-table-status-${status}`);
        }
    };
    
    // Initialize database functionality when DOM is ready
    $(document).ready(function() {
        WPCADatabase.init();
    });
    
    // Expose to global scope for potential external use
    window.WPCADatabase = WPCADatabase;
})(jQuery);
