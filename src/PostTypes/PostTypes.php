<?php

namespace RSA\Spacebring\PostTypes;

use RSA\Spacebring\Helpers\Credentials;

class PostTypes
{
    public static function init(): void
    {
        if (!Credentials::exists()) {
            return;
        }

        $sync = get_option('spacebring_sync_options', []);

        if (!empty($sync['sync_locations'])) {
            (new Locations())->register();
        }

        if (!empty($sync['sync_resources']['hot_desks'])) {
            (new HotDesks())->register();
        }

        if (!empty($sync['sync_resources']['dedicated_desks'])) {
            (new DedicatedDesks())->register();
        }

        if (!empty($sync['sync_resources']['offices'])) {
            (new Offices())->register();
        }

        if (!empty($sync['sync_resources']['parking_lots'])) {
            (new ParkingLots())->register();
        }

        if (!empty($sync['sync_resources']['rooms'])) {
            (new Rooms())->register();
        }
    }
}