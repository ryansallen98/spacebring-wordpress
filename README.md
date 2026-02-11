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