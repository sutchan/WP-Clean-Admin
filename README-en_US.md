=== WP Clean Admin ===
 Contributors: SutChan
Tags: admin, dashboard, clean, minimal, ui, simplify, customization, optimization
Requires at least: 5.0
Tested up to: 6.5
Stable tag: 1.7.0
Requires PHP: 7.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wp-clean-admin
Domain Path: /languages

[简体中文版本](README.md)

Simplify and customize your WordPress admin dashboard with a flat, minimal, and fresh UI.

== Description ==

**WP Clean Admin** helps you declutter and personalize your WordPress admin area. It offers a range of options to hide unnecessary elements and apply a clean, modern visual style, making your dashboard more efficient and pleasant to use.

**Key Features:**

* **Interface Simplification:** Easily hide unused menus, dashboard widgets, and editor elements.
* **Visual Customization:** Choose from preset themes or tweak colors and layout for a personalized look.
* **Flat & Minimal Design:** Embrace a clean aesthetic that reduces visual noise and focuses on what's important.
* **User-Friendly Settings:** A dedicated settings page lets you configure the plugin without touching code.
* **Performance Optimization Module:** Comprehensive features to improve WordPress admin interface speed and efficiency
  - **Database Optimization:** Automatic and manual database table optimization to improve query performance
  - **Resource Management:** CSS/JS resource control and loading optimization
  - **Performance Monitoring:** Track page load times, query counts, and memory usage
  - **Database Cleanup:** Remove unnecessary data like revisions, auto-drafts, spam comments, etc.
* **Login Page Customization:** Multiple login page style options
* **Menu Management:** Hide/restore/sort capabilities
* **Role Management:** Set different admin interface experiences for different user roles
* **Security Improvements:** Added nonce verification and capability checks for enhanced security

Get a cleaner, faster, and more enjoyable WordPress admin experience with WP Clean Admin.

== Installation ==

1. Upload the `wp-clean-admin` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to `Settings > WP Clean Admin` to configure your preferences.

== Frequently Asked Questions ==

= Can I customize which menus or widgets are hidden? =

Yes, the settings page provides options to select which elements you want to hide. You can choose to hide any menu items or widgets based on your needs.

= Will this plugin affect my website's frontend? =

No, WP Clean Admin only modifies the appearance and functionality of the WordPress admin dashboard, with no impact on your website's frontend display.

= Can I restore default settings? =

Yes, there is a "Restore Default Settings" button on the settings page that will revert all settings to their initial state when clicked.

= Is this plugin compatible with other admin interface plugins? =

WP Clean Admin is carefully designed to be compatible with most WordPress plugins. However, if you use other plugins that modify the admin interface, some style conflicts may occur. If you encounter issues, try disabling other admin interface plugins first.

== Screenshots ==

1. Settings page showing options to hide dashboard widgets.
2. Settings page with theme style selection.
3. Custom color scheme settings interface.
4. Simplified WordPress admin dashboard showcase.

== Changelog ==

= 1.7.0 =
* **Menu Management Enhancement**: Added dedicated Menu Manager class for more efficient menu hiding functionality
* **Menu Protection**: Implemented menu protection mechanism to prevent accidental hiding of core functionality menus
* **Menu Statistics**: Added menu statistics functionality to display menu item count and hiding status

= 1.6.0 =
* **Database Optimization Module**: Added comprehensive database optimization and cleanup functionality
* **Architecture Enhancement**: Separated database logic from performance module for better code organization
* **Security Improvements**: Added nonce verification and input sanitization for all database operations

= 1.4.2 =
* Fixed missing PHP closing tags in multiple files
* Fixed duplicate closing curly brace in class-wpca-ajax.php
* Fixed version number inconsistency between files
* Ensured all PHP files follow proper syntax standards
* Fixed class loading order issue by changing direct performance class includes to autoloader loading
* Fixed missing $raw_stats private property definition in WPCA_Performance class
* Updated all documentation to version 1.4.2 to match plugin version

= 1.3.0 =
* **Performance Optimization Module**: Added comprehensive performance optimization features to improve WordPress admin interface speed and efficiency
  - **Database Optimization**: Implemented automatic and manual database table optimization to reduce overhead and improve query performance
  - **Resource Management**: Added CSS/JS resource control, loading optimization, and cleanup functionality for admin interface
  - **Performance Monitoring**: Built-in performance monitoring to track page load times, query counts, and memory usage
  - **Database Cleanup**: Added options to clean up unnecessary data including revisions, auto-drafts, spam comments, etc.
* Architecture Enhancement: Implemented singleton pattern for all new components to ensure consistent object instantiation
* Security Improvements: Added nonce verification and capability checks for all AJAX operations and admin actions

= 1.2.0 =
* Login page customization with multiple style options
* Menu management with hiding/restoring/sorting capabilities
* Dashboard widget removal and customization
* Admin interface styling improvements

= 1.1.0 =
* User permissions management
* Admin bar cleanup
* Page title optimization
* Translation support

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.1.1 =
After upgrading to version 1.1.1, please save your settings once to ensure new features work properly.

== Project Links ==

* [Project Home](https://github.com/sutchan/WP-Clean-Admin)
* [GitHub Repository](https://github.com/sutchan/WP-Clean-Admin)