<?php

namespace RSA\Spacebring\Helpers;

class Template
{
    public static function admin(
        string $view,
        array $data = [],
        string $capability = 'manage_options'
    ): void {
        if (!defined('ABSPATH')) {
            exit;
        }

        if (!current_user_can($capability)) {
            wp_die(__('You do not have permission to access this page.', 'spacebring'));
        }

        $view = trim($view, '/');
        $file = SPACEBRING_PATH . "templates/admin/{$view}.php";

        if (!file_exists($file)) {
            $file = SPACEBRING_PATH . "templates/admin/{$view}/index.php";

            if (!file_exists($file)) {
                wp_die(__('Template not found.', 'spacebring'));
            }
        }

        extract($data, EXTR_SKIP);
        include $file;
    }

    public static function frontend(
        string $view,
        array $data = [],
        string $version = '1.0.0'
    ): void {
        if (!defined('ABSPATH')) {
            exit;
        }

        $view = trim($view, '/');

        /**
         * 1. Theme override path
         *    your-theme/spacebring/shortcodes/events.php
         */
        $themeFile = get_stylesheet_directory() . "/spacebring/{$view}.php";

        /**
         * 2. Plugin default path
         *    plugin/templates/frontend/shortcodes/events.php
         */
        $pluginFile = SPACEBRING_PATH . "templates/frontend/{$view}.php";

        if (file_exists($themeFile)) {
            $file = $themeFile;
        } elseif (file_exists($pluginFile)) {
            $file = $pluginFile;
        } else {
            wp_die(__('Frontend template not found.', 'spacebring'));
        }

        /**
         * Provide $template_version inside the template
         */
        $template_version = $version;

        extract($data, EXTR_SKIP);
        include $file;
    }
}