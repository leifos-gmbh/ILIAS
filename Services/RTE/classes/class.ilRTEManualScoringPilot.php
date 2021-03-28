<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilRTEManualScoringPilot
 * @author      Björn Heyser <info@bjoernheyser.de>
 */
class ilRTEManualScoringPilot extends ilRTE
{
    /**
     * @return string
     */
    public static function _getRTEClassname()
    {
        require_once 'Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php';
        switch (ilObjAdvancedEditing::_getRichTextEditor()) {
            case 'tinymce':
                require_once 'Services/RTE/classes/class.ilTinyMCEManualScoringPilot.php';
                return 'ilTinyMCEManualScoringPilot';
                break;

            default:
                return 'ilRTE';
                break;
        }
    }
}
