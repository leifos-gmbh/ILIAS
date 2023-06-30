<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Teach types
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup
 */
class ilCDTeachType
{
    public static $teach_types = array(
        "10" => "group_work",
        "20" => "individual_teach",
        "30" => "teach_top_management",
        "40" => "edu_consultance",
        "50" => "blended_learning_products");

    /**
     * Get all
     *
     * @param
     * @return
     */
    public static function getAll()
    {
        return self::$teach_types;
    }
}
