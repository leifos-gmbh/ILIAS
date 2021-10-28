<?php declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

class ApacheCustom
{
    /**
     * @return string
     */
    public static function getUsername() : string
    {
        /*
         * enter your custom login-name resolve function here
         *
         * if you are using the "auto create account" feature
         * be sure to return a valid username IN ANY CASE
         */
        $long_name = (string) $_SERVER['REMOTE_USER'];
        $short_names = explode('@',$long_name);
        return $short_names[0];
    }
}
