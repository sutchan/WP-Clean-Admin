<?php
/**
 * WP Clean Admin Login Class
 *
 * Handles all login page-related modifications and settings.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class WPCA_Login {

    /**
     * Constructor.
     */
    public function __construct() {
        // Login page functionality is now handled in WPCA_Settings and wpca-login.js
        // This class is kept for future expansion if needed, but currently has no active hooks.
    }
}