<?php

namespace RSA\Spacebring\PostTypes;

class DedicatedDesks extends AbstractPostType
{
    public static function post_type(): string
    {
        return 'dedicated-desks';
    }

    protected static function labels(): array
    {
        return [
            'name' => 'Dedicated Desks',
            'singular_name' => 'Dedicated Desk',
        ];
    }

    protected static function menu_parent(): ?string
    {
        return 'spacebring';
    }

    protected static function classic_editor_only(): bool
    {
        return true;
    }

    protected static function api_controlled(): bool
    {
        return true;
    }

    protected static function disable_add_new(): bool
    {
        return true;
    }

    protected static function revisions(): bool
    {
        return false;
    }

    protected static function meta_fields(): array
    {
        return [
            'external_id' => ['label' => 'External ID'],
            'network_ref' => ['label' => 'Network Ref'],
            'location_ref' => ['label' => 'Location Ref'],
            'capacity' => ['label' => 'Capacity'],
            'booking_url' => ['label' => 'Booking URL'],
            'locale' => ['label' => 'Locale'],
            'create_date' => ['label' => 'Created Date'],
            'money' => [
                'type' => 'object',
                'single' => true,
                'show_in_rest' => [
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'enabled' => ['type' => 'boolean'],
                            'tiers' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'flatAmount' => ['type' => 'number'],
                                        'from' => ['type' => 'integer'],
                                        'unitAmount' => ['type' => 'number'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'plans' => [
                'type' => 'object',
                'single' => true,
                'show_in_rest' => [
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'enabled' => ['type' => 'boolean'],
                            'tiers' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'entire' => ['type' => 'boolean'],
                                        'plan' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'commitmentCycles' => ['type' => 'integer'],
                                                'credits' => ['type' => 'integer'],
                                                'dayPasses' => ['type' => 'integer'],
                                                'id' => ['type' => 'string'],
                                                'period' => ['type' => 'string'],
                                                'price' => ['type' => 'number'],
                                                'title' => ['type' => 'string'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}