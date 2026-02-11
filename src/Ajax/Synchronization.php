<?php

namespace RSA\Spacebring\Ajax;

use RSA\Spacebring\Sync\SyncService;

class Synchronization
{

    public const LOCATIONS_ENDPOINT = '/locations/v1';

    public function register()
    {
        add_action(
            'wp_ajax_spacebring_synchronization_stream',
            [$this, 'stream']
        );
    }

    public function stream()
    {
        // Kill all output buffering
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        if (!check_ajax_referer('spacebring_synchronization', false, false)) {
            status_header(403);
            exit;
        }

        if (!current_user_can('manage_options')) {
            status_header(403);
            exit;
        }

        // SSE headers
        header('Content-Type: text/event-stream; charset=UTF-8');
        header('Cache-Control: no-cache, no-transform');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');

        @ob_flush();
        @flush();

        $service = new SyncService();

        $service->sync(function (array $payload) {
            echo 'data: ' . wp_json_encode($payload) . "\n\n";
            @flush();
            sleep(1);
        });

        echo 'data: ' . wp_json_encode(['done' => true]) . "\n\n";
        @flush();
        exit;
    }
}