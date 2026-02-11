<?php

namespace RSA\Spacebring\Sync;

use RSA\Spacebring\PostTypes\DedicatedDesks;

class DedicatedDesksSync extends AbstractSync
{
    protected function post_type(): string
    {
        return DedicatedDesks::post_type();
    }

    protected function meta_map(): array
    {
        return [
            'networkRef' => 'network_ref',
            'locationRef' => 'location_ref', 
            'capacity' => 'capacity',
            'locale' => 'locale',
            'createDate' => 'create_date',
            'plans' => 'plans',
            'money' => 'money',
        ];
    }
}