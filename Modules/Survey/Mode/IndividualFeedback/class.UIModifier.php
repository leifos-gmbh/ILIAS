<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Survey\Mode\IndividualFeedback;
use \ILIAS\Survey\Mode;
use ILIAS\Survey\InternalUIService;

/**
 * Interface for modes
 * @author Alexander Killing <killing@leifos.de>
 */
class UIModifier extends Mode\AbstractUIModifier
{
    /**
     * @inheritDoc
     */
    public function getSurveySettingsGeneral(
        \ilObjSurvey $survey,
        InternalUIService $ui_service
    ) : array {
        $items = [];
        $lng = $ui_service->lng();

        $self_rate = new \ilCheckboxInputGUI($lng->txt("survey_360_self_raters"), "self_rate");
        $self_rate->setInfo($lng->txt("survey_360_self_raters_info"));
        $self_rate->setChecked($survey->get360SelfRaters());
        $items[] = $self_rate;

        return $items;
    }

    /**
     * @inheritDoc
     */
    public function getSurveySettingsResults(
        \ilObjSurvey $survey,
        InternalUIService $ui_service
    ) : array {
        $items = [];
        $lng = $ui_service->lng();

        $ts_results = new \ilRadioGroupInputGUI($lng->txt("survey_360_results"), "ts_res");
        $ts_results->setValue($survey->get360Results());

        $option = new \ilRadioOption($lng->txt("survey_360_results_none"), \ilObjSurvey::RESULTS_360_NONE);
        $option->setInfo($lng->txt("survey_360_results_none_info"));
        $ts_results->addOption($option);

        $option = new \ilRadioOption($lng->txt("survey_360_results_own"), \ilObjSurvey::RESULTS_360_OWN);
        $option->setInfo($lng->txt("survey_360_results_own_info"));
        $ts_results->addOption($option);

        $items[] = $ts_results;

        return $items;
    }


    /**
     * @inheritDoc
     */
    public function setValuesFromForm(
        \ilObjSurvey $survey,
        \ilPropertyFormGUI $form
    ) : void
    {
        $survey->set360Results((int) $form->getInput("ts_res"));
        $survey->set360SelfRaters((bool) $form->getInput("self_rate"));
    }

}