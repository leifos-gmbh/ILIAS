<?php
declare(strict_types = 1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Survey\Mode;
use ILIAS\Survey\InternalUIService;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
abstract class AbstractUIModifier implements UIModifier
{
    public function __construct()
    {

    }

    /**
     * @inheritDoc
     */
    public function getSurveySettingsGeneral(
        \ilObjSurvey $survey,
        InternalUIService $ui_service
    ) : array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getSurveySettingsReminderTargets(
        \ilObjSurvey $survey,
        InternalUIService $ui_service
    ) : array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getSurveySettingsResults(
        \ilObjSurvey $survey,
        InternalUIService $ui_service
    ) : array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function setValuesFromForm(
        \ilObjSurvey $survey,
        \ilPropertyFormGUI $form
    ) : void
    {
    }

}