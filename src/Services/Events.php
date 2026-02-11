<?php

namespace RSA\Spacebring\Services;

use RSA\Spacebring\Api\Client;
use RSA\Spacebring\Helpers\Template;
use RSA\Spacebring\Helpers\Vite;
use RuntimeException;
use RSA\Spacebring\Helpers\Locations;

class Events
{
    private const CACHE_KEY = 'rsa_spacebring_latest_events';
    private const CACHE_TTL = 5 * MINUTE_IN_SECONDS;

    private const ENDPOINT = '/events/v1';

    private const SPACEBRING_DOMAIN = "https://book.casamarianas.com/suite/organizations/"; // TODO: Make this dynamic based on settings

    public function register()
    {
        add_action('admin_post_spacebring_clear_events_cache', function () {
            $this->clearAllCaches();
            wp_redirect(admin_url('options-general.php?page=spacebring&cache_cleared=1'));
            exit;
        });

        add_shortcode('spacebring_events', [$this, 'shortcode']);
    }

    public function latest(int $limit = 5): array
    {
        $cacheKey = self::CACHE_KEY . '_' . $limit;
        $cached = get_transient($cacheKey);

        if ($cached !== false) {
            return $cached;
        }

        $client = new Client();

        $queryParams = [
            'limit' => $limit,
            'sort' => 'start_date:asc',
        ];

        $options = get_option('spacebring_sync_options', []);

        if (!empty($options['single_location'])) {
            $queryParams['locationRef'] = Locations::getSingleExternalId();
        }

        /*
         * TODO:
         * - Add multiple location support (e.g. location_id[]=1&location_id[]=2)
         */

        try {
            $data = $client->request(self::ENDPOINT, 'GET', $queryParams);

            $events = $data['events'] ?? [];

            usort(
                $events,
                fn($a, $b) =>
                strtotime($b['startDate']) <=> strtotime($a['startDate'])
            );

            set_transient($cacheKey, $events, self::CACHE_TTL);

            return $events;

        } catch (RuntimeException $e) {
            error_log('[Spacebring Events] ' . $e->getMessage());
            return [];
        }
    }

    public function clearCache(int $limit = 5): void
    {
        delete_transient(self::CACHE_KEY . '_' . $limit);
    }

    public function clearAllCaches(): void
    {
        global $wpdb;

        $like = esc_sql('_transient_' . self::CACHE_KEY . '_%');
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '%{$like}%'");
    }

    public function shortcode($atts): string
    {
        $atts = shortcode_atts([
            'limit' => 5,
            'assets' => 'true',
        ], $atts);

        // Normalize boolean-ish string
        $renderAssets = filter_var($atts['assets'], FILTER_VALIDATE_BOOLEAN);

        $events = $this->latest((int) $atts['limit']);

        if ($renderAssets) {
            $this->enqueueAssets();
            $this->localizeEvents($events);
        }

        ob_start();

        Template::frontend('shortcodes/events', [
            'events' => $events,
            'domain' => self::SPACEBRING_DOMAIN,
        ], '1.0.0');

        return ob_get_clean();
    }

    protected function enqueueAssets(): void
    {
        Vite::enqueue('tailwind');
        Vite::enqueue('events');
    }

    protected function localizeEvents(array $events): void
    {
        $handle = 'spacebring-events';

        wp_localize_script($handle, 'SpacebringData', [
            'events' => array_map(function ($event) {
                return [
                    'id' => $event['id'] ?? null,
                    'title' => $event['title'] ?? 'Untitled Event',
                    'start' => $event['startDate'] ?? null,
                    'end' => $event['endDate'] ?? null,
                    'url' => self::SPACEBRING_DOMAIN
                        . ($event['locationRef'] ?? '')
                        . '/events/'
                        . ($event['id'] ?? ''),
                ];
            }, $events),
        ]);
    }
}