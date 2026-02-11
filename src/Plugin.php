<?php

namespace RSA\Spacebring;

use RSA\Spacebring\Admin\Admin;
use RSA\Spacebring\Ajax\Ajax;
use RSA\Spacebring\PostTypes\PostTypes;


class Plugin
{
    public static function init(): void
    {
        (new Admin())->init();
        (new Ajax())->init();
        (new PostTypes())->init();
    }
}