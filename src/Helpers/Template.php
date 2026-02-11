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
}