<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id\$
 * @ingroup
 */
class cdNeedsAnalysisFactory
{
    /**
     * Get app instance
     *
     * @param ilPlugin $a_pl plugin object
     * @return cdNeedsAnalysisGUI needs analysis gui object
     */
    public static function getGUIInstance($a_pl)
    {
        $a_pl->includeClass("class.cdNeedsAnalysisGUI.php");
        if (cdUtil::isDAF()) {
            $a_pl->includeClass("class.cdNeedsAnalysisDAFGUI.php");
            $na_gui = new cdNeedsAnalysisDAFGUI($a_pl);
        } else {
            $na_gui = new cdNeedsAnalysisGUI($a_pl);
        }
        return $na_gui;
    }

    /**
     * Get app instance
     *
     * @param ilPlugin $a_pl plugin object
     * @return cdNeedsAnalysis needs analysis gui object
     */
    public static function getInstance($a_pl, $a_id = 0)
    {
        $a_pl->includeClass("class.cdNeedsAnalysis.php");
        if (cdUtil::isDAF()) {
            $a_pl->includeClass("class.cdNeedsAnalysisDAF.php");
            $na = new cdNeedsAnalysisDAF($a_id);
        } else {
            $na = new cdNeedsAnalysis($a_id);
        }
        return $na;
    }
}
