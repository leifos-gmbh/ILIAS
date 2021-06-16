<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Technical languages
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup
 */
class ilCDTechnicalLanguage
{
    // see also cdNeedsAnalysis
    public static $tech_lang = array(
        "10" => "law",
        "20" => "medicin",
        "30" => "it",
        "40" => "technology",
        "50" => "finance",
        "60" => "human_resources",
        "70" => "marketing_and_sales",
        "80" => "insurances");

    /**
     * Get all
     *
     * @param
     * @return
     */
    public static function getAll()
    {
        return self::$tech_lang;
    }
}
