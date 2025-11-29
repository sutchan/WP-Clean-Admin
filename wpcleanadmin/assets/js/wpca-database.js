/**
 * Database optimization functionality for WP Clean Admin
 * 
 * @file wpcleanadmin/assets/js/wpca-database.js
 * @version 1.7.15
 * @updated 2025-11-29
 */

jQuery(document).ready(function($) {
    'use strict';
    
    /**
     * Database optimization class
     */
    var WPCADatabase = {
        /**
         * Initialize database optimization functionality
         */
        init: function() {
            this.bindEvents();
            this.loadDatabaseInfo();
            this.loadCleanupStatistics();
        },
        
        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // Run cleanup button
            $('#wpca-run-cleanup').on('click', this.runDatabaseCleanup.bind(this));
            
            // Optimize tables button
            $('#wpca-optimize-tables').on('click', this.optimizeTables.bind(this));
        },
        
        /**
         * Load database information
         */
        loadDatabaseInfo: function() {
            var container = $('#wpca-database-info-container');
            container.html('<p>' + wpca_admin.loading + '</p>');
            
            $.ajax({
                url: wpca_admin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpca_get_database_info',
                    nonce: wpca_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        WPCADatabase.renderDatabaseInfo(response.data);
                    } else {
                        container.html('<p class="error">' + (response.data.message || wpca_admin.error_request_processing_failed) + '</p>');
                    }
                },
                error: function() {
                    container.html('<p class="error">' + wpca_admin.error_server_error + '</p>');
                }
            });
        },
        
        /**
         * Render database information
         * 
         * @param {Object} data Database information
         */
        renderDatabaseInfo: function(data) {
            var container = $('#wpca-database-info-container');
            var html = '<div class="wpca-database-info-grid">';
            
            html += '<div class="wpca-info-card">';
            html += '<h3>' + wpca_admin.total_tables + '</h3>';
            html += '<p>' + wpca_admin.tables + '</p>';
            html += '</div>';
            
            html += '<div class="wpca-info-card">';
            html += '<h3>' + this.formatSize(data.total_size) + '</h3>';
            html += '<p>' + wpca_admin.total_size + '</p>';
            html += '</div>';
            
            html += '<div class="wpca-info-card">';
            html += '<h3>' + this.formatSize(data.overhead) + '</h3>';
            html += '<p>' + wpca_admin.overhead + '</p>';
            html += '</div>';
            
            html += '</div>';
            
            html += '<h3>' + wpca_admin.table_details + '</h3>';
            html += '<table class="wp-list-table widefat fixed striped">';
            html += '<thead>';
            html += '<tr>';
            html += '<th>' + wpca_admin.table_name + '</th>';
            html += '<th>' + wpca_admin.size + '</th>';
            html += '<th>' + wpca_admin.overhead + '</th>';
            html += '<th>' + wpca_admin.rows + '</th>';
            html += '<th>' + wpca_admin.engine + '</th>';
            html += '</tr>';
            html += '</thead>';
            html += '<tbody>';
            
            $.each(data.tables, function(index, table) {
                html += '<tr>';
                html += '<td>' + table.name + '</td>';
                html += '<td>' + WPCADatabase.formatSize(table.size) + '</td>';
                html += '<td>' + WPCADatabase.formatSize(table.overhead) + '</td>';
                html += '<td>' + table.rows + '</td>';
                html += '<td>' + table.engine + '</td>';
                html += '</tr>';
            });
            
            html += '</tbody>';
            html += '</table>';
            
            container.html(html);
        },
        
        /**
         * Load cleanup statistics
         */
        loadCleanupStatistics: function() {
            var container = $('#wpca-cleanup-stats-container');
            container.html('<p>' + wpca_admin.loading + '</p>');
            
            $.ajax({
                url: wpca_admin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpca_get_cleanup_statistics',
                    nonce: wpca_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        WPCADatabase.renderCleanupStatistics(response.data);
                    } else {
                        container.html('<p class="error">' + (response.data.message || wpca_admin.error_request_processing_failed) + '</p>');
                    }
                },
                error: function() {
                    container.html('<p class="error">' + wpca_admin.error_server_error + '</p>');
                }
            });
        },
        
        /**
         * Render cleanup statistics
         * 
         * @param {Object} data Cleanup statistics
         */
        renderCleanupStatistics: function(data) {
            var container = $('#wpca-cleanup-stats-container');
            var html = '<div class="wpca-cleanup-stats-grid">';
            
            html += '<div class="wpca-info-card">';
            html += '<h3>' + data.total_cleanups + '</h3>';
            html += '<p>' + wpca_admin.total_cleanups + '</p>';
            html += '</div>';
            
            html += '<div class="wpca-info-card">';
            html += '<h3>' + data.total_rows_cleaned.toLocaleString() + '</h3>';
            html += '<p>' + wpca_admin.total_rows_cleaned + '</p>';
            html += '</div>';
            
            html += '<div class="wpca-info-card">';
            html += '<h3>' + this.formatSize(data.total_space_saved) + '</h3>';
            html += '<p>' + wpca_admin.total_space_saved + '</p>';
            html += '</div>';
            
            html += '<div class="wpca-info-card">';
            html += '<h3>' + (data.last_cleanup ? new Date(data.last_cleanup).toLocaleString() : wpca_admin.never) + '</h3>';
            html += '<p>' + wpca_admin.last_cleanup + '</p>';
            html += '</div>';
            
            html += '</div>';
            
            container.html(html);
        },
        
        /**
         * Run database cleanup
         */
        runDatabaseCleanup: function() {
            var button = $('#wpca-run-cleanup');
            var resultsContainer = $('#wpca-action-results');
            
            // Disable button and show loading
            button.prop('disabled', true).html('<span class="spinner is-active"></span> ' + wpca_admin.running_cleanup);
            resultsContainer.html('');
            
            // Get cleanup settings from form
            var cleanupItems = this.getCleanupItemsFromSettings();
            
            $.ajax({
                url: wpca_admin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpca_run_database_cleanup',
                    nonce: wpca_admin.nonce,
                    cleanup_items: JSON.stringify(cleanupItems)
                },
                success: function(response) {
                    WPCADatabase.handleCleanupResponse(response);
                },
                error: function() {
                    resultsContainer.html('<div class="error notice"><p>' + wpca_admin.error_server_error + '</p></div>');
                    button.prop('disabled', false).html(wpca_admin.run_cleanup);
                }
            });
        },
        
        /**
         * Get cleanup items from settings
         * 
         * @return {Object} Cleanup items configuration
         */
        getCleanupItemsFromSettings: function() {
            var settings = $('#wpca-database-settings-form').serializeArray();
            var cleanupItems = {};
            
            // Default cleanup items
            var defaultItems = {
                revision_posts: { enabled: true, days: 30 },
                auto_drafts: { enabled: true, days: 7 },
                trashed_posts: { enabled: true, days: 30 },
                spam_comments: { enabled: true, days: 7 },
                trash_comments: { enabled: true, days: 30 },
                orphan_postmeta: { enabled: true, days: 0 },
                orphan_commentmeta: { enabled: true, days: 0 },
                orphan_term_relationships: { enabled: true, days: 0 },
                orphan_usermeta: { enabled: true, days: 0 },
                expired_transients: { enabled: true, days: 0 },
                oembed_cache: { enabled: true, days: 30 }
            };
            
            // Override with settings from form
            $.each(settings, function(index, field) {
                if (field.name === 'wpca_database_settings[cleanup_revisions]') {
                    defaultItems.revision_posts.enabled = field.value === '1';
                } else if (field.name === 'wpca_database_settings[cleanup_auto_drafts]') {
                    defaultItems.auto_drafts.enabled = field.value === '1';
                } else if (field.name === 'wpca_database_settings[cleanup_trashed_posts]') {
                    defaultItems.trashed_posts.enabled = field.value === '1';
                } else if (field.name === 'wpca_database_settings[cleanup_spam_comments]') {
                    defaultItems.spam_comments.enabled = field.value === '1';
                } else if (field.name === 'wpca_database_settings[cleanup_trash_comments]') {
                    defaultItems.trash_comments.enabled = field.value === '1';
                } else if (field.name === 'wpca_database_settings[cleanup_orphans]') {
                    var enabled = field.value === '1';
                    defaultItems.orphan_postmeta.enabled = enabled;
                    defaultItems.orphan_commentmeta.enabled = enabled;
                    defaultItems.orphan_term_relationships.enabled = enabled;
                    defaultItems.orphan_usermeta.enabled = enabled;
                } else if (field.name === 'wpca_database_settings[cleanup_expired_transients]') {
                    defaultItems.expired_transients.enabled = field.value === '1';
                } else if (field.name === 'wpca_database_settings[cleanup_oembed_cache]') {
                    defaultItems.oembed_cache.enabled = field.value === '1';
                }
            });
            
            return defaultItems;
        },
        
        /**
         * Handle cleanup response
         * 
         * @param {Object} response AJAX response
         */
        handleCleanupResponse: function(response) {
            var button = $('#wpca-run-cleanup');
            var resultsContainer = $('#wpca-action-results');
            
            button.prop('disabled', false).html(wpca_admin.run_cleanup);
            
            if (response.success) {
                var html = '<div class="success notice"><p>' + response.data.message + '</p></div>';
                html += '<div class="wpca-cleanup-results">';
                html += '<h3>' + wpca_admin.cleanup_results + '</h3>';
                html += '<p>' + wpca_admin.total_rows_deleted + ': ' + response.data.total_rows.toLocaleString() + '</p>';
                html += '<p>' + wpca_admin.space_saved + ': ' + response.data.space_saved + '</p>';
                
                if (response.data.details && response.data.details.length) {
                    html += '<h4>' + wpca_admin.cleanup_details + '</h4>';
                    html += '<table class="wp-list-table widefat fixed striped">';
                    html += '<thead><tr><th>' + wpca_admin.item + '</th><th>' + wpca_admin.rows_deleted + '</th><th>' + wpca_admin.status + '</th></tr></thead>';
                    html += '<tbody>';
                    
                    $.each(response.data.details, function(index, item) {
                        html += '<tr>';
                        html += '<td>' + item.item + '</td>';
                        html += '<td>' + item.rows_deleted.toLocaleString() + '</td>';
                        html += '<td>' + item.status + '</td>';
                        html += '</tr>';
                    });
                    
                    html += '</tbody></table>';
                }
                
                html += '</div>';
                
                resultsContainer.html(html);
                
                // Reload database info and statistics
                WPCADatabase.loadDatabaseInfo();
                WPCADatabase.loadCleanupStatistics();
            } else {
                resultsContainer.html('<div class="error notice"><p>' + (response.data.message || wpca_admin.error_request_processing_failed) + '</p></div>');
            }
        },
        
        /**
         * Optimize database tables
         */
        optimizeTables: function() {
            var button = $('#wpca-optimize-tables');
            var resultsContainer = $('#wpca-action-results');
            
            // Disable button and show loading
            button.prop('disabled', true).html('<span class="spinner is-active"></span> ' + wpca_admin.optimizing_tables);
            resultsContainer.html('');
            
            $.ajax({
                url: wpca_admin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpca_optimize_tables',
                    nonce: wpca_admin.nonce
                },
                success: function(response) {
                    WPCADatabase.handleOptimizeResponse(response);
                },
                error: function() {
                    resultsContainer.html('<div class="error notice"><p>' + wpca_admin.error_server_error + '</p></div>');
                    button.prop('disabled', false).html(wpca_admin.optimize_tables);
                }
            });
        },
        
        /**
         * Handle optimize response
         * 
         * @param {Object} response AJAX response
         */
        handleOptimizeResponse: function(response) {
            var button = $('#wpca-optimize-tables');
            var resultsContainer = $('#wpca-action-results');
            
            button.prop('disabled', false).html(wpca_admin.optimize_tables);
            
            if (response.success) {
                var html = '<div class="success notice"><p>' + response.data.message + '</p></div>';
                html += '<div class="wpca-optimize-results">';
                html += '<h3>' + wpca_admin.optimization_results + '</h3>';
                html += '<p>' + wpca_admin.optimized_tables + ': ' + response.data.optimized_tables + ' / ' + response.data.total_tables + '</p>';
                html += '<p>' + wpca_admin.space_saved + ': ' + response.data.space_saved + '</p>';
                
                resultsContainer.html(html);
                
                // Reload database info
                WPCADatabase.loadDatabaseInfo();
            } else {
                resultsContainer.html('<div class="error notice"><p>' + (response.data.message || wpca_admin.error_request_processing_failed) + '</p></div>');
            }
        },
        
        /**
         * Format size in bytes to human readable format
         * 
         * @param {number} bytes Size in bytes
         * @return {string} Human readable size
         */
        formatSize: function(bytes) {
            if (bytes === 0) return '0 B';
            var k = 1024;
            var sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
            var i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    };
    
    // Initialize when DOM is ready
    WPCADatabase.init();
});
