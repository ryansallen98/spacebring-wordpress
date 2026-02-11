<?php

namespace RSA\Spacebring\Ajax;

class Ajax
{
    public static function init(): void
    {
        (new TestApiConnection())->register();
        (new Synchronization())->register();
    }
}