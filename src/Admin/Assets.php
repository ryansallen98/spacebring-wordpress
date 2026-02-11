<?php

namespace RSA\Spacebring\Admin;

use RSA\Spacebring\Helpers\Vite;

class Assets
{
    public function register()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    public function enqueue_admin_assets($hook)
    {
        // Only load on Spacebring admin pages
        if (!$this->is_spacebring_page($hook)) {
            return;
        }

        // Enqueue Vite assets
        Vite::enqueue('tailwind');
        Vite::enqueue('admin');

        // Enqueue nonce scripts
        $this->enqueue_nonce_scripts($hook);
    }

    protected function is_spacebring_page(string $hook): bool
    {
        return str_starts_with($hook, 'toplevel_page_spacebring')
            || str_starts_with($hook, 'spacebring_page_');
    }

    protected function enqueue_nonce_scripts($hook)
    {
        wp_localize_script('spacebring-admin', 'SpacebringAjax', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonces' => [
                'testApiConnection' => wp_create_nonce('spacebring_test_api_connection'),
                'synchronization' => wp_create_nonce('spacebring_synchronization'),
            ],
        ]);
    }
}