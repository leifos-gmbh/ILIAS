<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Exercise\Assignment;

use ILIAS\Exercise\InternalDomainService;
use ILIAS\Exercise\InternalGUIService;
use ILIAS\Exercise\Assignment\Mandatory\MandatoryAssignmentsManager;
use ILIAS\UI\Component\Button\Shy as ButtonShy;
use ILIAS\UI\Component\Link\Standard as LinkStandard;
use ILIAS\UI\Component\Button\Primary as ButtonPrimary;
use ILIAS\UI\Component\Component;


class PropertyAndActionBuilderUI
{
    public const PROP_DEADLINE = "deadline";
    public const PROP_REQUIREMENT = "requirement";
    public const SEC_INSTRUCTIONS = "instructions";
    public const SEC_FILES = "files";
    public const SEC_SUBMISSION = "submission";
    public const SEC_SCHEDULE = "schedule";
    protected \ilExSubmission $submission;
    protected \ilExAssignmentTypesGUI $types_gui;
    protected \ilObjExercise $exc;
    protected \ilExAssignment $ex_ass;
    protected \ILIAS\MediaObjects\MediaType\MediaTypeManager $media_type;
    protected Assignment $assignment;
    protected \ilExAssignmentInfo $info;

    protected int $user_id;
    protected string $lead_text = "";
    protected array $head_properties = [];
    protected array $properties = [];
    protected array $actions = [];
    protected array $main_action = [];
    protected array $additional_head_properties = [];

    public function __construct(
        \ilObjExercise $exc,
        MandatoryAssignmentsManager $mandatory_manager,
        InternalDomainService $domain_service,
        InternalGUIService $gui_service
    ) {
        global $DIC;

        $this->exc = $exc;
        $this->media_type = $DIC->mediaObjects()->internal()->domain()->mediaType();
        $this->domain = $domain_service;
        $this->gui = $gui_service;
        $this->lng = $domain_service->lng();
        $this->mandatory_manager = $mandatory_manager;
        $this->lng->loadLanguageModule("exc");
        $this->ctrl = $gui_service->ctrl();
        $this->types_gui = $gui_service->assignment()->types();
    }

    public function build(
        Assignment $ass,
        int $user_id
    ) : void
    {
        $this->assignment = $ass;
        $this->user_id = $user_id;
        $this->state = \ilExcAssMemberState::getInstanceByIds($ass->getId(), $user_id);
        $this->info = new \ilExAssignmentInfo($ass->getId(), $user_id);
        $this->ex_ass = new \ilExAssignment($this->assignment->getId());
        $this->submission = new \ilExSubmission($this->ex_ass, $user_id);
        $this->lead_text = "";
        $this->head_properties = [];
        $this->additional_head_properties = [];
        $this->buildHead();
        $this->buildBody();
    }

    public function getLeadText() : string
    {
        return $this->lead_text;
    }

    public function getHeadProperty(string $type) : ?array
    {
        return $this->head_properties[$type] ?? null;
    }

    public function getAdditionalHeadProperties() : array
    {
        return $this->additional_head_properties;
    }

    protected function setLeadText(string $text) : void
    {
        $this->lead_text = $text;
    }


    protected function setHeadProperty(string $type, string $prop, string $val) : void
    {
        $this->head_properties[$type] = [
            "prop" => $prop,
            "val" => $val
        ];
    }

    protected function addAdditionalHeadProperty(string $prop, string $val) : void
    {
        $this->additional_head_properties[] = [
            "prop" => $prop,
            "val" => $val
        ];
    }

    public function addProperty(string $section, string $prop, string $val) : void
    {
        $this->properties[$section][] = [
            "prop" => $prop,
            "val" => $val
        ];
    }

    public function getProperties(string $section) : array
    {
        return $this->properties[$section] ?? [];
    }

    public function addAction(string $section, Component $button_or_link) : void
    {
        $this->actions[$section][] = $button_or_link;
    }

    public function getActions(string $section) : array
    {
        return $this->actions[$section] ?? [];
    }

    public function setMainAction(string $section, ButtonPrimary $button) : void
    {
        $this->main_action[$section] = $button;
    }

    public function getMainAction(string $section):?ButtonPrimary
    {
        return $this->main_action[$section] ?? null;
    }

    protected function buildHead() : void
    {
        $state = $this->state;
        $lng = $this->lng;

        if ($state->exceededOfficialDeadline()) {
            $this->setLeadText (
                $lng->txt("exc_ended_on") . ": " . $state->getCommonDeadlinePresentation()
            );

            $this->setHeadProperty(
                self::PROP_DEADLINE,
                $lng->txt("exc_ended_on"),
                $state->getCommonDeadlinePresentation()
            );

            // #14077 // this currently shows the feedback deadline during grace period
            if ($state->getPeerReviewDeadline()) {
                $this->addAdditionalHeadProperty(
                    $lng->txt("exc_peer_review_deadline"),
                    $state->getPeerReviewDeadlinePresentation()
                );
            }
        } elseif (!$state->hasGenerallyStarted()) {
            if ($state->getRelativeDeadline()) {
                $prop = $lng->txt("exc_earliest_start_time");
            } else {
                $prop = $lng->txt("exc_starting_on");
            }
            $this->setLeadText (
                $prop . ": " . $state->getGeneralStartPresentation()
            );
            $this->setHeadProperty(
                self::PROP_DEADLINE,
                $prop,
                $state->getGeneralStartPresentation()
            );
        } else {
            if ($state->getCommonDeadline() > 0) {
                $this->setLeadText (
                    $lng->txt("exc_time_to_send") . ": " . $state->getRemainingTimePresentation()
                );
                $this->setHeadProperty(
                    self::PROP_DEADLINE,
                    $lng->txt("exc_edit_until"),
                    $state->getCommonDeadlinePresentation()
                );
            } elseif ($state->getRelativeDeadline()) {		// if we only have a relative deadline (not started yet)
                $this->setHeadProperty(
                    self::PROP_DEADLINE,
                    $lng->txt("exc_rem_time_after_start"),
                    $state->getRelativeDeadlinePresentation()
                );

                if ($state->getLastSubmissionOfRelativeDeadline()) {		// if we only have a relative deadline (not started yet)
                    $this->addAdditionalHeadProperty(
                        $lng->txt("exc_rel_last_submission"),
                        $state->getLastSubmissionOfRelativeDeadlinePresentation()
                    );
                }
            }

            if ($state->getIndividualDeadline() > 0) {
                $this->addAdditionalHeadProperty(
                    $lng->txt("exc_individual_deadline"),
                    $state->getIndividualDeadlinePresentation()
                );
            }
        }

        if ($this->mandatory_manager->isMandatoryForUser($this->assignment->getId(), $this->user_id)) {
            $this->setHeadProperty(
                self::PROP_REQUIREMENT,
                $lng->txt("exc_requirement"),
                $lng->txt("exc_mandatory")
            );
        } else {
            $this->setHeadProperty(
                self::PROP_REQUIREMENT,
                $lng->txt("exc_requirement"),
                $lng->txt("exc_optional")
            );
        }

        // status icon
        /*
        $tpl->setVariable(
            "ICON_STATUS",
            $this->getIconForStatus(
                $a_ass->getMemberStatus()->getStatus(),
                ilLPStatusIcons::ICON_VARIANT_SHORT
            )
        );*/
    }

    protected function buildBody() : void
    {
        if ($this->state->areInstructionsVisible()) {
            $this->buildInstructions();
            $this->buildFiles();
        }

        //$this->buildSchedule();

        if ($this->state->hasSubmissionStarted()) {
            $this->buildSubmission();
        }
    }


    protected function buildInstructions(): void {
        $inst = $this->info->getInstructionInfo();
        if (count($inst) > 0) {
            $this->addProperty(
                self::SEC_INSTRUCTIONS,
                $inst["instruction"]["txt"],
                $inst["instruction"]["value"]
            );
        }
    }

    /**
     * @throws ilDateTimeException|ilExcUnknownAssignmentTypeException
     */
    protected function addSchedule(
        ilInfoScreenGUI $a_info,
        ilExAssignment $a_ass
    ): void {
        $lng = $this->lng;
        $ilUser = $this->user;
        $ilCtrl = $this->ctrl;

        $info = new ilExAssignmentInfo($a_ass->getId(), $ilUser->getId());
        $schedule = $info->getScheduleInfo();

        $state = ilExcAssMemberState::getInstanceByIds($a_ass->getId(), $ilUser->getId());

        $a_info->addSection($lng->txt("exc_schedule"));
        if ($state->getGeneralStart() > 0) {
            $a_info->addProperty($schedule["start_time"]["txt"], $schedule["start_time"]["value"]);
        }


        if ($state->getCommonDeadline()) {		// if we have a common deadline (target timestamp)
            $a_info->addProperty($schedule["until"]["txt"], $schedule["until"]["value"]);
        } elseif ($state->getRelativeDeadline()) {		// if we only have a relative deadline (not started yet)
            $but = "";
            if ($state->hasGenerallyStarted()) {
                $ilCtrl->setParameterByClass("ilobjexercisegui", "ass_id", $a_ass->getId());
                $but = $this->ui->factory()->button()->primary($lng->txt("exc_start_assignment"), $ilCtrl->getLinkTargetByClass("ilobjexercisegui", "startAssignment"));
                $ilCtrl->setParameterByClass("ilobjexercisegui", "ass_id", $this->requested_ass_id);
                $but = $this->ui->renderer()->render($but);
            }

            $a_info->addProperty($schedule["time_after_start"]["txt"], $schedule["time_after_start"]["value"] . " " . $but);
            if ($state->getLastSubmissionOfRelativeDeadline()) {		// if we only have a relative deadline (not started yet)
                $a_info->addProperty(
                    $lng->txt("exc_rel_last_submission"),
                    $state->getLastSubmissionOfRelativeDeadlinePresentation()
                );
            }
        }

        if ($state->getOfficialDeadline() > $state->getCommonDeadline()) {
            $a_info->addProperty($schedule["individual_deadline"]["txt"], $schedule["individual_deadline"]["value"]);
        }

        if ($state->hasSubmissionStarted()) {
            $a_info->addProperty($schedule["time_to_send"]["txt"], $schedule["time_to_send"]["value"]);
        }
    }

    protected function addPublicSubmissions(
        ilInfoScreenGUI $a_info,
        ilExAssignment $a_ass
    ): void {
        $lng = $this->lng;
        $ilUser = $this->user;

        $state = ilExcAssMemberState::getInstanceByIds($a_ass->getId(), $ilUser->getId());

        // submissions are visible, even if other users may still have a larger individual deadline
        if ($state->hasSubmissionEnded()) {
            $button = ilLinkButton::getInstance();
            $button->setCaption("exc_list_submission");
            $button->setUrl($this->getSubmissionLink("listPublicSubmissions"));

            $a_info->addProperty($lng->txt("exc_public_submission"), $button->render());
        } else {
            $a_info->addProperty(
                $lng->txt("exc_public_submission"),
                $lng->txt("exc_msg_public_submission")
            );
        }
    }

    protected function buildFiles(): void {
        $lng = $this->lng;
        $ui_factory = $this->gui->ui()->factory();
        $ui_renderer = $this->gui->ui()->renderer();

        $ass = $this->ex_ass;
        $files = $ass->getFiles();
        if (count($files) > 0) {
            $cnt = 0;
            foreach ($files as $file) {
                $cnt++;
                // get mime type
                $mime = \ilObjMediaObject::getMimeType($file['fullpath']);
                $output_filename = htmlspecialchars($file['name']);

                if ($this->media_type->isImage($mime)) {
                    $item_id = "il-ex-modal-img-" . $ass->getId() . "-" . $cnt;


                    $image = $ui_renderer->render($ui_factory->image()->responsive($file['fullpath'], $output_filename));
                    $image_lens = \ilUtil::getImagePath("enlarge.svg");

                    $modal = \ilModalGUI::getInstance();
                    $modal->setId($item_id);
                    $modal->setType(\ilModalGUI::TYPE_LARGE);
                    $modal->setBody($image);
                    $modal->setHeading($output_filename);
                    $modal = $modal->getHTML();

                    $img_tpl = new \ilTemplate("tpl.image_file.html", true, true, "Modules/Exercise");
                    $img_tpl->setCurrentBlock("image_content");
                    $img_tpl->setVariable("MODAL", $modal);
                    $img_tpl->setVariable("ITEM_ID", $item_id);
                    $img_tpl->setVariable("IMAGE", $image);
                    $img_tpl->setvariable("IMAGE_LENS", $image_lens);
                    $img_tpl->setvariable("ALT_LENS", $lng->txt("exc_fullscreen"));
                    $img_tpl->parseCurrentBlock();

                    $this->addProperty(
                        self::SEC_FILES,
                        $output_filename,
                        $img_tpl->get()
                    );

                } elseif ($this->media_type->isAudio($mime) || $this->media_type->isVideo($mime)) {
                    $media_tpl = new \ilTemplate("tpl.media_file.html", true, true, "Modules/Exercise");

                    if ($this->media_type->isAudio($mime)) {
                        $p = $ui_factory->player()->audio($file['fullpath']);
                    } else {
                        $p = $ui_factory->player()->video($file['fullpath']);
                    }
                    $media_tpl->setVariable("MEDIA", $ui_renderer->render($p));

                    $but = $ui_factory->button()->shy(
                        $lng->txt("download"),
                        $this->getSubmissionLink("downloadFile", array("file" => urlencode($file["name"])))
                    );
                    $media_tpl->setVariable("DOWNLOAD_BUTTON", $ui_renderer->render($but));
                    $this->addProperty(
                        self::SEC_FILES,
                        $output_filename,
                        $media_tpl->get()
                    );
                } else {
                    $l = $ui_factory->link()->standard(
                        $lng->txt("download"),
                        $this->getSubmissionLink("downloadFile", array("file" => urlencode($file["name"])))
                    );
                    $this->addProperty(
                        self::SEC_FILES,
                        $output_filename,
                        $ui_renderer->render($l)
                    );
                }
            }
        }
    }

    /**
     * @throws ilCtrlException
     * @throws ilDateTimeException
     */
    protected function buildSubmission(): void {

        if (!$this->submission->canView()) {
            return;
        }

        $this->ctrl->setParameterByClass(
            "ilExSubmissionGUI",
            "ass_id",
            $this->assignment->getId()
        );

        // todo
        /*
        if ($a_submission->getAssignment()->hasTeam()) {
            ilExSubmissionTeamGUI::getOverviewContent($a_info, $a_submission);
        }*/

        $type_gui = $this->types_gui->getById($this->ex_ass->getType());
        $type_gui->setSubmission($this->submission);
        $type_gui->setExercise($this->exc);
        $type_gui->buildSubmissionPropertiesAndActions($this);

        //\ilExSubmissionGUI::getOverviewContent($a_info, $this->submission, $this->exc);


        return;

        $last_sub = null;
        if ($submission->hasSubmitted()) {
            $last_sub = $submission->getLastSubmission();
            if ($last_sub) {
                $last_sub = ilDatePresentation::formatDate(new ilDateTime($last_sub, IL_CAL_DATETIME));
                $a_info->addProperty($lng->txt("exc_last_submission"), $last_sub);
            }
        }

        if ($this->exc->getShowSubmissions()) {
            $this->addPublicSubmissions($a_info, $a_ass);
        }

        ilExPeerReviewGUI::getOverviewContent($a_info, $submission);

        // global feedback / sample solution
        if ($a_ass->getFeedbackDate() == ilExAssignment::FEEDBACK_DATE_DEADLINE) {
            $show_global_feedback = ($state->hasSubmissionEndedForAllUsers() && $a_ass->getFeedbackFile());
        }
        //If it is not well configured...(e.g. show solution before deadline)
        //the user can get the solution before he summit it.
        //we can check in the elseif $submission->hasSubmitted()
        elseif ($a_ass->getFeedbackDate() == ilExAssignment::FEEDBACK_DATE_CUSTOM) {
            $show_global_feedback = ($a_ass->afterCustomDate() && $a_ass->getFeedbackFile());
        } else {
            $show_global_feedback = ($last_sub && $a_ass->getFeedbackFile());
        }
        $this->addSubmissionFeedback($a_info, $a_ass, $submission->getFeedbackId(), $show_global_feedback);
    }

    protected function addSubmissionFeedback(
        ilInfoScreenGUI $a_info,
        ilExAssignment $a_ass,
        string $a_feedback_id,
        bool $a_show_global_feedback
    ): void {
        $lng = $this->lng;

        $storage = new ilFSStorageExercise($a_ass->getExerciseId(), $a_ass->getId());
        $cnt_files = $storage->countFeedbackFiles($a_feedback_id);

        $lpcomment = $a_ass->getMemberStatus()->getComment();
        $mark = $a_ass->getMemberStatus()->getMark();
        $status = $a_ass->getMemberStatus()->getStatus();

        if ($lpcomment != "" ||
            $mark != "" ||
            $status != "notgraded" ||
            $cnt_files > 0 ||
            $a_show_global_feedback) {
            $a_info->addSection($lng->txt("exc_feedback_from_tutor"));
            if ($lpcomment != "") {
                $a_info->addProperty(
                    $lng->txt("exc_comment"),
                    nl2br($lpcomment)
                );
            }
            if ($mark != "") {
                $a_info->addProperty(
                    $lng->txt("exc_mark"),
                    $mark
                );
            }

            if ($status != "" && $status != "notgraded") {
                //$img = $this->getIconForStatus($status);
                $a_info->addProperty(
                    $lng->txt("status"),
                    $img . " " . $lng->txt("exc_" . $status)
                );
            }

            if ($cnt_files > 0) {
                $a_info->addSection($lng->txt("exc_fb_files") .
                    '<a id="fb' . $a_ass->getId() . '"></a>');

                if ($cnt_files > 0) {
                    $files = $storage->getFeedbackFiles($a_feedback_id);
                    foreach ($files as $file) {
                        $a_info->addProperty(
                            $file,
                            $lng->txt("download"),
                            $this->getSubmissionLink("downloadFeedbackFile", array("file" => urlencode($file)))
                        );
                    }
                }
            }

            // #15002 - global feedback
            if ($a_show_global_feedback) {
                $a_info->addSection($lng->txt("exc_global_feedback_file"));

                $a_info->addProperty(
                    $a_ass->getFeedbackFile(),
                    $lng->txt("download"),
                    $this->getSubmissionLink("downloadGlobalFeedbackFile")
                );
            }
        }
    }

    /**
     * Get time string for deadline
     * @throws ilDateTimeException
     */
    protected function getTimeString(int $a_deadline): string
    {
        $lng = $this->lng;

        if ($a_deadline == 0) {
            return $lng->txt("exc_submit_convenience_no_deadline");
        }

        if ($a_deadline - time() <= 0) {
            $time_str = $lng->txt("exc_time_over_short");
        } else {
            $time_str = ilLegacyFormElementsUtil::period2String(new ilDateTime($a_deadline, IL_CAL_UNIX));
        }

        return $time_str;
    }

    protected function getSubmissionLink(
        string $a_cmd,
        array $a_params = null
    ): string {
        $ilCtrl = $this->ctrl;

        if (is_array($a_params)) {
            foreach ($a_params as $name => $value) {
                $ilCtrl->setParameterByClass("ilexsubmissiongui", $name, $value);
            }
        }

        $ilCtrl->setParameterByClass("ilexsubmissiongui", "ass_id", $this->assignment->getId());
        $url = $ilCtrl->getLinkTargetByClass("ilexsubmissiongui", $a_cmd);
        $ilCtrl->setParameterByClass("ilexsubmissiongui", "ass_id", "");

        if (is_array($a_params)) {
            foreach ($a_params as $name => $value) {
                $ilCtrl->setParameterByClass("ilexsubmissiongui", $name, "");
            }
        }

        return $url;
    }

    /**
     * Get the rendered icon for a status (failed, passed or not graded).
     */
    protected function getIconForStatus(string $status, int $variant = ilLPStatusIcons::ICON_VARIANT_LONG): string
    {
        $icons = ilLPStatusIcons::getInstance($variant);
        $lng = $this->lng;

        switch ($status) {
            case "passed":
                return $icons->renderIcon(
                    $icons->getImagePathCompleted(),
                    $lng->txt("exc_" . $status)
                );

            case "failed":
                return $icons->renderIcon(
                    $icons->getImagePathFailed(),
                    $lng->txt("exc_" . $status)
                );

            default:
                return $icons->renderIcon(
                    $icons->getImagePathNotAttempted(),
                    $lng->txt("exc_" . $status)
                );
        }
    }
}
