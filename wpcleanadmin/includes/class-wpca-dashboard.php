<?php
/**
 * WP Clean Admin Dashboard Class
 *
 * Handles all dashboard-related modifications and settings.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class WPCA_Dashboard {

    /**
     * Constructor.
     */
    public function __construct() {
        // Dashboard functionality is now handled in wpca-core-functions.php
        // This class is kept for future expansion if needed, but currently has no active hooks.
        // All dashboard widget removal logic has been moved to wpca_remove_dashboard_widgets in wpca-core-functions.php
    }
}
?>