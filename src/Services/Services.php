<?php

namespace RSA\Spacebring\Services;

class Services
{
    public static function init(): void
    {
        (new Events())->register();
    }
}