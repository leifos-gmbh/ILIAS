<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * CD helper
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class cdUtil
{
    /**
     * isDAF
     *
     * @return bool
     */
    public static function isDAF()
    {
        global $DIC;

        if (isset($DIC['ilClientIniFile'])) {
            $ilClientIniFile = $DIC['ilClientIniFile'];
            return ($ilClientIniFile->readVariable("system", "DAF") == "1");
        }

        return false;
    }
}
