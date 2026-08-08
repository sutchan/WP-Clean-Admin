# WP Clean Admin

Make your WordPress admin cleaner, faster, and more secure! **Version 1.8.2**

✨ **Key Benefits**:
- ✨ One-click cleanup of cluttered admin area
- 🚀 Significantly faster admin loading
- 🔒 Enhanced website security
- 🎛️ Flexible menu customization
- 📦 Easy database management
- 🌐 Multi-language support (Chinese, English)

---

## Why Choose WP Clean Admin?

Is your WordPress admin area looking messy? Too many unnecessary menu items, widgets, and features? WP Clean Admin helps you:

- **Clean Up Clutter**: Hide unwanted menus, widgets, and redundant information
- **Speed Things Up**: Disable unnecessary features and make your admin fly
- **Boost Security**: Protect your site from common security threats
- **Simplify Management**: Make the admin interface work your way

---

## Core Features

### 🧹 Admin Cleanup
- **Menu Simplification**: Hide unnecessary admin menu items for a cleaner interface
- **Dashboard Optimization**: Remove unwanted dashboard widgets
- **Login Page Customization**: Customize login page appearance
- **Admin Bar Cleanup**: Hide unnecessary items from the top admin bar

### 🚀 Performance Optimization
- **Disable Emojis**: Remove WordPress emoji features for faster loading
- **Resource Optimization**: Minify and combine CSS/JS files
- **Resource Preloading**: Preload critical resources for better experience
- **Heartbeat Control**: Disable or adjust WordPress Heartbeat API to reduce server load

### 🔒 Security Enhancement
- **Hide Version**: Remove WordPress version info to prevent targeted attacks
- **Login Protection**: Add CAPTCHA to prevent spam logins
- **Two-Factor Authentication**: Support for 2FA to increase account security
- **Login Attempt Restriction**: Limit login attempts to prevent brute-force attacks
- **Access Control**: Restrict admin access by user role

### 🎛️ Menu Customization
- **Role-Based Restrictions**: Show/hide menus based on user roles
- **Menu Reordering**: Customize the order of admin menus
- **Create Groups**: Organize related menus into custom groups
- **Admin Bar Customization**: Customize what appears in the top admin bar

### 💾 Database Management
- **One-Click Optimization**: Automatically optimize database tables
- **Backup & Restore**: Easily create and download database backups, restore anytime
- **Smart Cleanup**: Clean transients, orphaned metadata, and expired cron events

---

## Quick Start

### Installation

1. Download the plugin zip file
2. In your WordPress admin, go to **Plugins > Add New**
3. Click **Upload Plugin**, select the zip file you downloaded
4. Click **Install Now**, then click **Activate**

Or you can upload via FTP to `wp-content/plugins/` directory, then activate in admin.

### Get Started in 3 Steps

1. **Access Settings**: After activation, find "WP Clean Admin" in the left menu
2. **Configure Features**: Use the tabbed interface to enable features as needed
3. **Save & Apply**: Click "Save Settings" and the plugin starts working immediately!

---

## Usage Guide

### Settings Page
- Navigate to **Settings > Clean Admin** to configure the plugin
- Use the top tabs to switch between different feature modules
- Remember to save after each change

### Database Management
- Go to **Settings > Clean Admin > Database**
- Click "Optimize Database" to auto-optimize tables
- Click "Backup Database" to create a backup
- Click "Restore Database" when you need to restore

### Cleanup Operations
- Go to **Settings > Clean Admin > Cleanup**
- Select the cleanup options you want to run
- Click "Run Cleanup" to start the process

### Performance Optimization
- Go to **Settings > Clean Admin > Performance**
- Toggle optimization options on/off
- Save settings to apply immediately

### Security Settings
- Go to **Settings > Clean Admin > Security**
- Enable security features as needed
- Save settings to apply immediately

---

## Requirements

- WordPress 5.0 or higher
- PHP 7.0 or higher
- MySQL 5.6 or higher

---

## Get Support

Have questions or suggestions? Visit our [GitHub repository](https://github.com/Tanox/WP-Clean-Admin) to submit an issue, we'll respond soon!

---

## License

WP Clean Admin is licensed under GPL-2.0+.

---

## Prototype & Design System

This project ships a high-fidelity interactive prototype and design specs for design review and acceptance:

- **Prototype** (`prototype/`): `ui/index.html` is the single entry (shadcn style) with realistic mock data;
  `php/` holds module skeletons (Module_Base + AJAX gateway), decoupled from the plugin runtime.
  Open `prototype/ui/index.html` in a browser to preview, no backend required.
- **Design system** (`prototype/ui/wpca-components.css`): the single source of shadcn/ui style tokens
  covering color/typography/spacing/radius/shadow/motion tokens, component library and responsive rules.
  The plugin's `wpcleanadmin/assets/css/wpca-admin.css` reuses the same token classes.
- **Design spec**: [`docs/设计规范_20260808.md`](docs/设计规范_20260808.md)
- **OpenSpec specs**: [`openspec/`](openspec/)

---

## Developer Docs

- Development guide: [`DEVELOPMENT.md`](DEVELOPMENT.md)
- Project instructions: [`AGENTS.md`](AGENTS.md)

---

## Changelog

See [CHANGELOG](CHANGELOG.md) for version history.
