<?php

namespace RSA\Spacebring\Helpers;

use \RSA\Spacebring\PostTypes\Locations as LocationsPostType;

class Locations
{
    public static function getSingleExternalId(): ?string
    {
        $locationIds = get_posts([
            'post_type' => LocationsPostType::post_type(),
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'fields' => 'ids',
        ]);

        if (empty($locationIds)) {
            return null;
        }

        $externalId = get_post_meta($locationIds[0], "external_id", true);

        return $externalId ?: null;
    }
}