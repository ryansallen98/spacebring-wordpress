<?php

namespace RSA\Spacebring\Helpers;

class Credentials
{
    public static function exists(): bool
    {
        $options = get_option('spacebring_api_options', []);

        return !empty($options['api_username'])
            && !empty($options['api_password']);
    }

    public static function username(): string
    {
        return get_option('spacebring_api_options', [])['api_username'] ?? '';
    }

    public static function password(): string
    {
        return get_option('spacebring_api_options', [])['api_password'] ?? '';
    }
}