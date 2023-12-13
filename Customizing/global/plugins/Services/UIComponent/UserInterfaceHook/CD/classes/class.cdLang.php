<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * CD languages
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 */
class cdLang
{
    public static $langs = array("de", "en", "fr", "it", "es");
    public static $lang_levels = array("A1", "A2", "B1", "B2", "C1", "C2");
    public static $lang_levels_ext = array("A 1", "A 2.1", "A 2.2", "B 1.1", "B 1.2", "B 2.1", "B 2.2", "B 2.3", "C 1.1", "C 1.2", "C 2");
    
    /**
     * Get languages
     */
    public static function getLanguages()
    {
        return self::$langs;
    }

    /**
     * Get languages
     */
    public static function getLangLevels()
    {
        return self::$lang_levels;
    }

    /**
     * Get languages
     */
    public static function getLangLevelsExt()
    {
        return self::$lang_levels_ext;
    }

    /**
     * Get languages as options
     */
    public static function getLanguagesAsOptions($a_mode = "property", $a_omit_lead_option = false)
    {
        global $lng;

        $lng->loadLanguageModule("meta");
        $options = array();
        if ($a_mode == "property" && !$a_omit_lead_option) {
            $options = array("" => $lng->txt("please_select"));
        }
        if ($a_mode == "filter" && !$a_omit_lead_option) {
            $options = array("" => $lng->txt("all"));
        }
        foreach (self::$langs as $l) {
            $options[$l] = $lng->txt("meta_l_" . $l);
        }
        return $options;
    }
}
