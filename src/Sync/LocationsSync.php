<?php

namespace RSA\Spacebring\Sync;

use RSA\Spacebring\PostTypes\Locations;

class LocationsSync extends AbstractSync
{
    protected function post_type(): string
    {
        return Locations::post_type();
    }

    protected function meta_map(): array
    {
        return [
            'networkRef' => 'network_ref',
            'address' => 'address',
            'email' => 'email',
            'currencyCode' => 'currency_code',
            'timezoneId' => 'timezone_id',
            'locale' => 'locale',
            'createDate' => 'create_date',
        ];
    }
}