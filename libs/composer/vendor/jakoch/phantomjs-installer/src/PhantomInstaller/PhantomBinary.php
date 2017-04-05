<?php

namespace PhantomInstaller;

class PhantomBinary
{
    const BIN = '/Users/leifos/Sites/ILIAS/libs/composer/bin/phantomjs';
    const DIR = '/Users/leifos/Sites/ILIAS/libs/composer/bin';

    public static function getBin() {
        return self::BIN;
    }

    public static function getDir() {
        return self::DIR;
    }
}
