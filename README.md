# Spacebring for WordPress

A modern WordPress plugin to integrate Spacebring with WordPress, enabling data sync, admin tooling, and frontend rendering using a modern JS stack.

## 🚀 Overview

**Spacebring for WordPress** is a plugin designed to connect a WordPress site with the Spacebring platform. The goal is to allow WordPress to act as a management and presentation layer for Spacebring data.

Planned features include:
- 🔐 Authenticate WordPress with the Spacebring API
- 🔄 Sync Spacebring resources into WordPress (as custom post types)
- 🧩 Admin dashboard for managing Spacebring settings and sync status
- 🌐 Frontend rendering of Spacebring data (blocks/shortcodes)
- ⚡ Modern frontend tooling (Vite + Tailwind + TypeScript)

> ⚠️ Status: This project currently provides the plugin foundation and architecture. The actual Spacebring API integration and sync features are still in progress.

## 🧱 Architecture

This plugin is built with a modern WordPress architecture:
- **PHP (OOP, namespaced)**
    - Modular service classes (Admin, Ajax, PostTypes, etc.)
    - Composer autoloading
- **Frontend Tooling**
    - Vite
    - Tailwind CSS
    - TypeScript

This structure is intended to support rich admin interfaces and frontend components.

## 📁 Project Structure

```
spacebring/
├── spacebring.php        # Main plugin bootstrap file
├── src/
│   ├── Plugin.php        # Core plugin loader
│   ├── Admin.php         # Admin UI (settings, pages)
│   ├── Ajax.php          # AJAX endpoints
│   └── PostTypes.php     # Custom post types for synced data
├── assets/               # Frontend source (TS, Tailwind, etc.)
├── vite.config.js        # Frontend build config
├── composer.json        # PHP dependencies & autoloading
└── package.json         # Frontend dependencies
```

## ⚙️ Requirements
- WordPress 6.x+
- PHP 8.0+
- Node.js 18+ (for frontend build)
- Composer

## 🛠️ Development Setup

1. **Install PHP dependencies**

```bash
composer install
```

2. **Install frontend dependencies**

```bash
npm install
```

3. **Build frontend assets**

```bash
npm run dev
# or
npm run build
```

4. **Activate the plugin**

- Zip the plugin folder or symlink it into your WordPress wp-content/plugins directory.
- Activate Spacebring for WordPress from the WP admin panel.

## 🧪 Current Status

Implemented:
- ✅ WordPress plugin bootstrapping
- ✅ Modular service architecture
- ✅ Modern frontend build system (Vite + Tailwind + TS)
- ✅ Basic CPT syncing with spacebring API
- ✅ Basic Cached Sync + Live Fetch for events
- ✅ Template overriding for frontend

Not yet implemented:
- ❌ Webhooks / scheduled sync
- ❌ Cached Sync + Live Fetch for feeds
- ❌ Support for multiple locations
- ❌ Template version control in settings

## Shortcodes

### Events

Use the [spacebring_events] shortcode to display upcoming Spacebring events anywhere on your site (posts, pages, or templates).

```
[spacebring_events]
```

#### Shortcode Attributes

You can customize the shortcode using the following attributes:

##### limit (number)
Controls how many upcoming events to display.

```
[spacebring_events]
```
Default: 5

##### assets (boolean)
Controls whether the plugin loads its JavaScript and CSS assets (Tailwind, calendar, view toggle, etc.).

```
[spacebring_events assets="true"]
```
Disable assets if you are manually enqueueing them elsewhere:
```
[spacebring_events assets="false"]
```
Default: true

## Template Override (Advanced)
You can override the default markup by copying the template into your theme:

```
your-theme/spacebring/shortcodes/events.php
```

This allows you to fully customize the layout and styling of the events list and calendar views.

### Notes
- Events are cached for performance and refreshed automatically.
- Dates are displayed using your WordPress site’s date format.
- Clicking an event opens the full Spacebring event page in a new tab.


## 🤝 Contributing

This project is in early development. Contributions and ideas are welcome.
If you’re extending this plugin, consider keeping the modular service structure intact.


## 📄 License

MIT
