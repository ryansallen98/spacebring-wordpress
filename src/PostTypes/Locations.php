<?php

namespace RSA\Spacebring\PostTypes;

class Locations extends AbstractPostType
{
    public static function post_type(): string
    {
        return 'locations';
    }

    protected static function labels(): array
    {
        return [
            'name' => 'Locations',
            'singular_name' => 'Location',
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

    protected static function supports(): array
    {
        return ['title', 'editor'];
    }

    protected static function public_visibility(): bool
    {
        $options = get_option('spacebring_sync_options', []);
        return empty($options['single_location']);
    }

    protected static function meta_fields(): array
    {
        return [
            'external_id' => ['label' => 'External ID'],
            'network_ref' => ['label' => 'Network Ref'],
            'address' => ['label' => 'Address'],
            'email' => ['label' => 'Email'],
            'currency_code' => ['label' => 'Currency Code'],
            'timezone_id' => ['label' => 'Timezone'],
            'locale' => ['label' => 'Locale'],
            'create_date' => ['label' => 'Created Date'],
        ];
    }
}