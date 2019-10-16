<?php

namespace PhantomInstaller;

class PhantomBinary
{
    const BIN = '/srv/www/hal/web/ilias54_leifost/libs/composer/bin/phantomjs';
    const DIR = '/srv/www/hal/web/ilias54_leifost/libs/composer/bin';

    public static function getBin() {
        return self::BIN;
    }

    public static function getDir() {
        return self::DIR;
    }
}
