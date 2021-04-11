<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ilTestScoringByQuestionsGUI
 * @author     Björn Heyser <info@bjoernheyser.de>
 * @ilCtrl_Calls ilTestScoringPilotGUI: ilTestScoringEssayGUI
 */
class ilTestScoringPilotGUI extends ilTestScoringGUI
{
    /**
     * @param ilObjTest $a_object
     */
    public function __construct(ilObjTest $a_object)
    {
        parent::__construct($a_object);
    }

    /**
     * @return string
     */
    protected function getDefaultCommand()
    {
        return 'showParticipants';
    }

    /**
     * @return string
     */
    protected function getActiveSubTabId()
    {
        return 'man_scoring_essay';
    }

    public function executeCommand()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        if (!$this->getTestAccess()->checkScoreParticipantsAccess()) {
            ilObjTestGUI::accessViolationRedirect();
        }

        require_once 'Modules/Test/classes/class.ilObjAssessmentFolder.php';
        if (!ilObjAssessmentFolder::_mananuallyScoreableQuestionTypesExists()) {
            // allow only if at least one question type is marked for manual scoring
            ilUtil::sendFailure($this->lng->txt("manscoring_not_allowed"), true);
            $this->ctrl->redirectByClass("ilobjtestgui", "infoScreen");
        }

        $DIC->tabs()->activateTab(ilTestTabsManager::TAB_ID_MANUAL_SCORING);
        $this->buildSubTabs($this->getActiveSubTabId());

        switch( $DIC->ctrl()->getNextClass($this) )
        {
            case strtolower(ilTestScoringEssayGUI::class):
                $gui = new ilTestScoringEssayGUI($this->object);
                $DIC->ctrl()->forwardCommand($gui);
                break;

            default:
                $command = $DIC->ctrl()->getCmd($this->getDefaultCommand()).'Cmd';
                $this->{$command}();
        }
    }

    protected function showManScoringParticipantsTableCmd()
    {
        $this->showParticipantsCmd();
    }

    protected function showParticipantsCmd()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $table = $this->buildManScoringParticipantsTable(true);
        $table->setRowTemplate('tpl.il_as_tst_man_scoring_pilot_participant_tblrow.html', 'Modules/Test');
        $table->setEditScoringPilot(true);
        $table->initColumns();

        $DIC->ui()->mainTemplate()->setContent($table->getHTML());
    }

    protected function applyManScoringParticipantsFilterCmd()
    {
        $table = $this->buildManScoringParticipantsTable(false);

        $table->resetOffset();
        $table->writeFilterToSession();

        $this->showParticipantsCmd();
    }

    protected function resetManScoringParticipantsFilterCmd()
    {
        $table = $this->buildManScoringParticipantsTable(false);

        $table->resetOffset();
        $table->resetFilter();

        $this->showParticipantsCmd();
    }

    protected function markParticipantScoredCmd()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        ilTestService::setManScoringDone($this->getActiveIdParameter(), true);

        $DIC->ctrl()->redirect($this);
    }

    protected function markParticipantUnscoredCmd()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        ilTestService::setManScoringDone($this->getActiveIdParameter(), false);

        $DIC->ctrl()->redirect($this);
    }

    /**
     * @return int
     */
    protected function getActiveIdParameter()
    {
        $activeId = (int) $_GET['active_id'];

        if (!$this->getTestAccess()->checkScoreParticipantsAccessForActiveId($activeId)) {
            ilObjTestGUI::accessViolationRedirect();
        }

        return $activeId;
    }

    /**
     * @return ilTestManScoringParticipantsTableGUI
     */
    private function buildManScoringParticipantsTable($withData = false)
    {
        require_once 'Modules/Test/classes/tables/class.ilTestManScoringParticipantsTableGUI.php';
        $table = new ilTestManScoringParticipantsTableGUI($this);

        if ($withData) {
            $participantStatusFilterValue = $table->getFilterItemByPostVar('participant_status')->getValue();

            require_once 'Modules/Test/classes/class.ilTestParticipantList.php';
            $participantList = new ilTestParticipantList($this->object);

            $participantList->initializeFromDbRows(
                $this->object->getTestParticipantsForManualScoring($participantStatusFilterValue)
            );

            $participantList = $participantList->getAccessFilteredList(
                ilTestParticipantAccessFilter::getScoreParticipantsUserFilter($this->ref_id)
            );

            $table->setData($this->getParticipantsTableData($participantList));
        }

        return $table;
    }

    protected function getParticipantsTableData(ilTestParticipantList $participantList)
    {
        $participantList = $participantList->getScoredParticipantList();
        $participants = $participantList->getManScoringPilotTableRows();

        foreach($participants as $key => $data)
        {
            $participants[$key]['manscoring_done'] = ilTestService::isManScoringDone($data['active_id']);
        }

        return $participants;
    }
}
