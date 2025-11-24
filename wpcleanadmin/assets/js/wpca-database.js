/**
 * WPCleanAdmin Database Optimization Script
 * 
 * Handles frontend interactions for database optimization and cleanup features.
 * Manages AJAX requests, button states, and result display.
 * 
 * @file       wpcleanadmin/assets/js/wpca-database.js
 * @version    1.7.13
 * @updated    2025-06-18
 * @package WP_Clean_Admin
 * @since 1.6.0
 */

( function( $ ) {
    
    'use strict';
    
    /**
     * Main database handler object
     */
    var WPCleanAdminDatabase = {
        
        /**
         * Initialize the database scripts
         */
        init: function() {
            
            // Initialize event listeners
            this.initEventListeners();
        },
        
        /**
         * Initialize event listeners
         */
        initEventListeners: function() {
            
            // Table optimization button
            $( '#wpca-optimize-tables' ).on( 'click', this.handleOptimizeTablesClick.bind( this ) );
            
            // Database cleanup button
            $( '#wpca-cleanup-database' ).on( 'click', this.handleCleanupDatabaseClick.bind( this ) );
            
            // Auto enable/disable controls
            $( '#wpca_auto_optimize_tables' ).on( 'change', function() {
                $( '#wpca_optimize_interval' ).prop( 'disabled', ! $( this ).is( ':checked' ) );
            });
            
            $( '#wpca_enable_auto_cleanup' ).on( 'change', function() {
                $( '#wpca_cleanup_interval' ).prop( 'disabled', ! $( this ).is( ':checked' ) );
            });
        },
        
        /**
         * Handle optimize tables button click
         */
        handleOptimizeTablesClick: function( e ) {
            e.preventDefault();
            
            var $button = $( '#wpca-optimize-tables' );
            var $results = $( '#wpca-optimization-results' );
            
            // Disable button and show loading state
            $button.prop( 'disabled', true );
            $button.html( '<span class="dashicons dashicons-update"></span> ' + wpca_database.loading );
            $results.empty();
            
            // Get selected tables
            var tables = [];
            $( 'input[name="wpca_tables_to_optimize[]"]:checked' ).each( function() {
                tables.push( $( this ).val() );
            });
            
            // Call AJAX to optimize tables
            this.optimizeTables( tables, function( response ) {
                
                // Enable button again
                $button.prop( 'disabled', false );
                $button.html( '<span class="dashicons dashicons-database"></span> ' + wpca_database.optimizeTables );
                
                if ( response.success ) {
                    // Show success message
                    var message = wpca_database.optimizeSuccess.replace( '%d', response.data.successful_tables ).replace( '%d', response.data.total_tables );
                    $results.html( '<div class="notice notice-success inline"><p>' + message + '</p></div>' );
                } else {
                    // Show error message
                    $results.html( '<div class="notice notice-error inline"><p>' + wpca_database.optimizeFailed + '</p></div>' );
                }
            });
        },
        
        /**
         * Handle cleanup database button click
         */
        handleCleanupDatabaseClick: function( e ) {
            e.preventDefault();
            
            var $button = $( '#wpca-cleanup-database' );
            var $results = $( '#wpca-cleanup-results' );
            
            // Get selected cleanup items
            var cleanupItems = {};
            var hasSelectedItems = false;
            
            // Check for each cleanup option
            cleanupItems.revisions = $( '#wpca_cleanup_revisions' ).is( ':checked' ) ? parseInt( $( '#wpca_revision_days' ).val(), 10 ) : 0;
            cleanupItems.auto_drafts = $( '#wpca_cleanup_auto_drafts' ).is( ':checked' ) ? 1 : 0;
            cleanupItems.trashed_posts = $( '#wpca_cleanup_trashed_posts' ).is( ':checked' ) ? 1 : 0;
            cleanupItems.spam_comments = $( '#wpca_cleanup_spam_comments' ).is( ':checked' ) ? 1 : 0;
            cleanupItems.trashed_comments = $( '#wpca_cleanup_trashed_comments' ).is( ':checked' ) ? 1 : 0;
            cleanupItems.pingbacks_trackbacks = $( '#wpca_cleanup_pingbacks_trackbacks' ).is( ':checked' ) ? 1 : 0;
            cleanupItems.orphaned_postmeta = $( '#wpca_cleanup_orphaned_postmeta' ).is( ':checked' ) ? 1 : 0;
            cleanupItems.orphaned_commentmeta = $( '#wpca_cleanup_orphaned_commentmeta' ).is( ':checked' ) ? 1 : 0;
            cleanupItems.orphaned_relationships = $( '#wpca_cleanup_orphaned_relationships' ).is( ':checked' ) ? 1 : 0;
            cleanupItems.orphaned_usermeta = $( '#wpca_cleanup_orphaned_usermeta' ).is( ':checked' ) ? 1 : 0;
            cleanupItems.expired_transients = $( '#wpca_cleanup_expired_transients' ).is( ':checked' ) ? 1 : 0;
            cleanupItems.all_transients = $( '#wpca_cleanup_all_transients' ).is( ':checked' ) ? 1 : 0;
            cleanupItems.oembed_caches = $( '#wpca_cleanup_oembed_caches' ).is( ':checked' ) ? 1 : 0;
            
            // Check if any items are selected
            $.each( cleanupItems, function( key, value ) {
                if ( value > 0 ) {
                    hasSelectedItems = true;
                }
            });
            
            // If no items selected, show error
            if ( ! hasSelectedItems ) {
                $results.html( '<div class="notice notice-error inline"><p>' + wpca_database.selectCleanupItemsFirst + '</p></div>' );
                return;
            }
            
            // Confirm with user
            if ( ! confirm( wpca_database.confirmCleanup ) ) {
                return;
            }
            
            // Disable button and show loading state
            $button.prop( 'disabled', true );
            $button.html( '<span class="dashicons dashicons-update"></span> ' + wpca_database.loading );
            $results.empty();
            
            // Call AJAX to clean up database
            this.cleanupDatabase( cleanupItems, function( response ) {
                
                // Enable button again
                $button.prop( 'disabled', false );
                $button.html( '<span class="dashicons dashicons-trash"></span> ' + wpca_database.cleanupDatabase );
                
                if ( response.success ) {
                    // Show success message
                    var message = wpca_database.cleanupSuccess.replace( '%d', response.data.total_items_removed );
                    $results.html( '<div class="notice notice-success inline"><p>' + message + '</p></div>' );
                    
                    // If we have detailed results, show them
                    if ( response.data.detailed_results && typeof response.data.detailed_results === 'object' ) {
                        var detailedHTML = '<div class="wpca-detailed-results"><h4>' + wpca_database.detailedResults + '</h4><ul>';
                        
                        $.each( response.data.detailed_results, function( item, count ) {
                            if ( count > 0 ) {
                                var itemName = WPCleanAdminDatabase.getItemName( item );
                                detailedHTML += '<li>' + itemName + ': ' + count + '</li>';
                            }
                        });
                        
                        detailedHTML += '</ul></div>';
                        $results.append( detailedHTML );
                    }
                } else {
                    // Show error message
                    $results.html( '<div class="notice notice-error inline"><p>' + wpca_database.cleanupFailed + '</p></div>' );
                }
            });
        },
        
        /**
         * Optimize database tables via AJAX
         */
        optimizeTables: function( tables, callback ) {
            $.ajax( {
                url: wpca_database.ajax_url,
                type: 'POST',
                data: {
                    action: 'wpca_optimize_tables',
                    nonce: wpca_database.nonce,
                    tables: tables
                },
                dataType: 'json',
                success: function( response ) {
                    if ( typeof callback === 'function' ) {
                        callback( response );
                    }
                },
                error: function() {
                    if ( typeof callback === 'function' ) {
                        callback( { success: false, data: {} } );
                    }
                }
            });
        },
        
        /**
         * Clean up database via AJAX
         */
        cleanupDatabase: function( cleanupItems, callback ) {
            $.ajax( {
                url: wpca_database.ajax_url,
                type: 'POST',
                data: {
                    action: 'wpca_cleanup_database',
                    nonce: wpca_database.nonce,
                    cleanup_items: cleanupItems
                },
                dataType: 'json',
                success: function( response ) {
                    if ( typeof callback === 'function' ) {
                        callback( response );
                    }
                },
                error: function() {
                    if ( typeof callback === 'function' ) {
                        callback( { success: false, data: {} } );
                    }
                }
            });
        },
        
        /**
         * Get human-readable name for a cleanup item
         */
        getItemName: function( itemKey ) {
            var names = {
                'revisions': wpca_database.postRevisions,
                'auto_drafts': wpca_database.autoDrafts,
                'trashed_posts': wpca_database.trashedPosts,
                'spam_comments': wpca_database.spamComments,
                'trashed_comments': wpca_database.trashedComments,
                'pingbacks_trackbacks': wpca_database.pingbacksTrackbacks,
                'orphaned_postmeta': wpca_database.orphanedPostmeta,
                'orphaned_commentmeta': wpca_database.orphanedCommentmeta,
                'orphaned_relationships': wpca_database.orphanedRelationships,
                'orphaned_usermeta': wpca_database.orphanedUsermeta,
                'expired_transients': wpca_database.expiredTransients,
                'all_transients': wpca_database.allTransients,
                'oembed_caches': wpca_database.oembedCaches
            };
            
            return names[itemKey] || itemKey;
        }
    };
    
    /**
     * Initialize the script when the DOM is ready
     */
    $( document ).ready( function() {
        WPCleanAdminDatabase.init();
    });
    
} )( jQuery );
