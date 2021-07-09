<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Survey\Mode;
use ILIAS\Survey\InternalUIService;

/**
 * Interface for modes
 * @author Alexander Killing <killing@leifos.de>
 */
interface UIModifier
{
    /**
     * @return \ilFormPropertyGUI[]
     */
    public function getSurveySettingsGeneral(
        \ilObjSurvey $survey,
        InternalUIService $ui_service
    ) : array;

    /**
     * @return \ilFormPropertyGUI[]
     */
    public function getSurveySettingsReminderTargets(
        \ilObjSurvey $survey,
        InternalUIService $ui_service
    ) : array;

    /**
     * @return \ilFormPropertyGUI[]
     */
    public function getSurveySettingsResults(
        \ilObjSurvey $survey,
        InternalUIService $ui_service
    ) : array;

    /**
     * @inheritDoc
     */
    public function setValuesFromForm(
        \ilObjSurvey $survey,
        \ilPropertyFormGUI $form
    ) : void;

}