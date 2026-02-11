<?php

namespace RSA\Spacebring\Admin;

class Admin
{
    public static function init(): void
    {
        (new Menu())->register();
        (new Settings())->register();
        (new Assets())->register();
    }
}