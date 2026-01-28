# WP Clean Admin

A comprehensive WordPress plugin for cleaning and optimizing your WordPress admin area and database.

## Features

### Admin Cleanup
- **Dashboard Cleanup**: Remove unnecessary dashboard widgets
- **Admin Menu Simplification**: Remove unused admin menu items
- **Admin Bar Cleanup**: Remove unnecessary admin bar items
- **Login Page Customization**: Customize login page appearance

### Database Management
- **Database Optimization**: Automatically optimize database tables
- **Database Backup**: Create and download database backups
- **Database Restore**: Restore database from backups
- **Cleanup Options**: Clean transients, orphaned metadata, and expired cron events

### Performance Optimization
- **Disable Emojis**: Improve performance by disabling WordPress emojis
- **Disable XML-RPC**: Enhance security and performance
- **Disable REST API**: Restrict REST API access for non-authenticated users
- **Disable Heartbeat**: Reduce server load by disabling heartbeat
- **Resource Optimization**: Minify and combine CSS/JS files
- **Resource Preloading**: Preload critical resources

### Security Enhancement
- **Hide WordPress Version**: Remove WordPress version information
- **Login CAPTCHA**: Add CAPTCHA to login form
- **Two-Factor Authentication**: Implement two-factor authentication
- **Login Attempt Restriction**: Limit login attempts to prevent brute force attacks
- **Admin Access Restriction**: Restrict admin area access

### Menu Customization
- **Role-Based Menu Restrictions**: Customize menu visibility based on user roles
- **Dashboard Widget Management**: Control which dashboard widgets are displayed
- **Admin Bar Customization**: Customize admin bar items

### API Integration
- **REST API**: Access plugin functionality via REST API endpoints
- **Settings Management**: Get and update settings via API
- **Cleanup Operations**: Run cleanup operations via API
- **Database Operations**: Run database operations via API
- **Performance Statistics**: Get performance statistics via API

## Installation

1. Download the plugin zip file
2. Upload the zip file to your WordPress site via Plugins > Add New > Upload Plugin
3. Activate the plugin
4. Configure settings via Settings > Clean Admin

## Usage

### Settings
- Navigate to **Settings > Clean Admin** to configure plugin settings
- Use the tabbed interface to navigate between different settings sections
- Save changes to apply settings

### Database Management
- Navigate to **Settings > Clean Admin > Database** to manage database operations
- Click "Optimize Database" to optimize database tables
- Click "Backup Database" to create a database backup
- Click "Restore Database" to restore from a backup

### Cleanup Operations
- Navigate to **Settings > Clean Admin > Cleanup** to run cleanup operations
- Select the cleanup options you want to run
- Click "Run Cleanup" to start the cleanup process

### Performance Optimization
- Navigate to **Settings > Clean Admin > Performance** to configure performance settings
- Enable/disable performance optimization options
- Save changes to apply settings

### Security Settings
- Navigate to **Settings > Clean Admin > Security** to configure security settings
- Enable/disable security features
- Save changes to apply settings

## API Usage

### Endpoints

#### GET /wp-json/wpca/v1/settings
Get plugin settings

#### POST /wp-json/wpca/v1/settings
Update plugin settings

#### POST /wp-json/wpca/v1/cleanup
Run cleanup operations

#### POST /wp-json/wpca/v1/database
Run database operations

#### GET /wp-json/wpca/v1/performance
Get performance statistics

## Requirements

- WordPress 5.0+
- PHP 7.0+

## License

MIT License

## Changelog

### 1.8.0
- Added tabbed interface for settings page
- Added database backup and restore functionality
- Added two-factor authentication
- Added login CAPTCHA
- Added performance optimization features
- Added security enhancement features
- Added REST API endpoints
- Added role-based menu restrictions
- Added resource preloading
- Improved database cleanup functionality
- Improved media cleanup functionality
- Improved comments cleanup functionality
- Improved content cleanup functionality

### 1.7.15
- Initial release

## Support

For support, please visit the [GitHub repository](https://github.com/sutchan/WP-Clean-Admin) or contact the developer.
