<?php

namespace RSA\Spacebring\Ajax;

use RSA\Spacebring\Api\Client;

class TestApiConnection
{
    public function register()
    {
        add_action('wp_ajax_spacebring_test_api_connection', [$this, 'handle']);
    }

    public function handle()
    {
        check_ajax_referer('spacebring_test_api_connection');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized', 403);
        }

        try {
            $client = new Client();

            $response = $client->request('/locations/v1', 'GET');

            wp_send_json_success($response);

        } catch (\Exception $e) {
            wp_send_json_error($e->getMessage(), 500);
        }
    }
}