<?php

namespace RSA\Spacebring\Sync;

use RSA\Spacebring\Api\Client;

class SyncService
{
    public const LOCATIONS_ENDPOINT = '/locations/v1';
    public const RESOURCES_ENDPOINT = '/resources/v1';
    public const HOT_DESK_PARAM = 'hotDesk';
    public const DEDICATED_DESK_PARAM = 'dedicatedDesk';
    public const OFFICE_PARAM = 'office';
    public const PARKING_LOT_PARAM = 'parkingLot';
    public const ROOM_PARAM = 'room';

    protected Client $client;

    public function __construct(?Client $client = null)
    {
        $this->client = $client ?? new Client();
    }

    public function sync(?callable $callback = null): void
    {
        $locations = [];

        // First, fetch locations if needed
        foreach ($this->endpoints() as $endpoint) {
            try {
                if ($endpoint['endpoint'] === self::LOCATIONS_ENDPOINT) {
                    $response = $this->fetch($endpoint);

                    $locations = $response[$endpoint['items_key']] ?? [];

                    if (!isset($endpoint['handler'])) {
                        throw new \RuntimeException('No sync handler for endpoint: ' . $endpoint['endpoint']);
                    }

                    $results = (new $endpoint['handler']())->sync($locations);
                    $summary = AbstractSync::summarize($results);

                    $log = [
                        "api" => $response,
                        "sync" => $summary,
                    ];

                    $status = $summary['failed'] === 0 ? 'success' : 'error';

                    $this->notify($callback, [
                        'endpoint' => $endpoint['endpoint'],
                        'status' => $status,
                        'data' => $log,
                        'count' => count($locations),
                    ]);

                    continue;
                }

                // Then, fetch other endpoints using locations
                foreach ($locations as $location) {
                    try {
                        $query = $endpoint['params'] ?? [];
                        $query['locationRef'] = $location['id'];

                        $response = $this->client->request(
                            $endpoint['endpoint'],
                            'GET',
                            $query
                        );

                        $items = $response[$endpoint['items_key']] ?? [];

                        if (!isset($endpoint['handler'])) {
                            throw new \RuntimeException('No sync handler for endpoint: ' . $endpoint['endpoint']);
                        }

                        $results = (new $endpoint['handler']())->sync($items);
                        $summary = AbstractSync::summarize($results);

                        $log = [
                            "api" => $response,
                            "sync" => $summary,
                        ];

                        $status = $summary['failed'] === 0 ? 'success' : 'error';

                        $this->notify($callback, [
                            'endpoint' => $endpoint['endpoint'],
                            'status' => $status,
                            'query' => $query,
                            'data' => $log,
                        ]);
                    } catch (\Throwable $e) {
                        $this->notify($callback, [
                            'endpoint' => $endpoint['endpoint'],
                            'status' => 'error',
                            'data' => $e->getMessage(),
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                $this->notify($callback, [
                    'endpoint' => $endpoint['endpoint'],
                    'query' => $endpoint['params'] ?? [],
                    'status' => 'error',
                    'data' => $e->getMessage(),
                ]);
            }
        }
    }

    protected function fetch(array $endpoint): array
    {
        return $this->client->request(
            $endpoint['endpoint'],
            'GET',
            $endpoint['params'] ?? []
        );
    }

    protected function notify(?callable $callback, array $payload): void
    {
        if ($callback) {
            $callback($payload);
        }
    }

    protected function endpoints(): array
    {
        $sync = get_option('spacebring_sync_options', []);
        $endpoints = [];

        if (!empty($sync['sync_locations'])) {
            $endpoints[] = [
                'endpoint' => self::LOCATIONS_ENDPOINT,
                'handler' => LocationsSync::class,
                'items_key' => 'locations',
            ];
        }

        if (!empty($sync['sync_resources']['hot_desks'])) {
            $endpoints[] = [
                'endpoint' => self::RESOURCES_ENDPOINT,
                'params' => [
                    'types' => self::HOT_DESK_PARAM,
                ],
                'handler' => HotDesksSync::class,
                'items_key' => 'resources',
            ];
        }

        if (!empty($sync['sync_resources']['dedicated_desks'])) {
            $endpoints[] = [
                'endpoint' => self::RESOURCES_ENDPOINT,
                'params' => [
                    'types' => self::DEDICATED_DESK_PARAM,
                ],
                'handler' => DedicatedDesksSync::class,
                'items_key' => 'resources',
            ];
        }

        if (!empty($sync['sync_resources']['offices'])) {
            $endpoints[] = [
                'endpoint' => self::RESOURCES_ENDPOINT,
                'params' => [
                    'types' => self::OFFICE_PARAM,
                ],
                'handler' => OfficesSync::class,
                'items_key' => 'resources',
            ];
        }

        if (!empty($sync['sync_resources']['parking_lots'])) {
            $endpoints[] = [
                'endpoint' => self::RESOURCES_ENDPOINT,
                'params' => [
                    'types' => self::PARKING_LOT_PARAM,
                ],
                'handler' => ParkingLotsSync::class,
                'items_key' => 'resources',
            ];
        }

        if (!empty($sync['sync_resources']['rooms'])) {
            $endpoints[] = [
                'endpoint' => self::RESOURCES_ENDPOINT,
                'params' => [
                    'types' => self::ROOM_PARAM,
                ],
                'handler' => RoomsSync::class,
                'items_key' => 'resources',
            ];
        }

        return $endpoints;
    }
}