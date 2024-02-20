<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Language exam licenses
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup
 */
class ilCDLangExamLicense
{
    /**
     * Get licenses
     *
     * @param
     * @return
     */
    public static function getLicenses()
    {
        include_once("./Services/CD/classes/class.cdUtil.php");
        if (cdUtil::isDAF()) {	// a17k540
            return  array(
                "telc" => "telc",
                "widaf" => "WiDaF",
                "testdaf" => "TestDaF",
                "pwd" => "PWD",
                "dsh" => "DSH"
            );
        }


        return  array(
            "telc" => "telc",
            "toeic" => "Toeic",
            "ielts" => "IELTS",
            "widaf" => "WiDaF",
            "testdaf" => "TestDaF",
            "bec" => "BEC",
            "toefl" => "TOEFL",
            "delf" => "DELF",
            "celi" => "CELI",
            "dele" => "DELE",
            "tfi" => "TFI",
            "ket" => "KET",
            "pet" => "PET",
            "fce" => "FCE",
            "cae" => "CAE",
            "cpe" => "CPE",
            "ssat" => "SSAT",
            "slep" => "SLEP",
            "pwd" => "PWD",
            "dsh" => "DSH"
            );
    }
}
