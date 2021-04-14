<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once('./Services/Table/classes/class.ilTable2GUI.php');

/**
*
* @author	Björn Heyser <bheyser@databay.de>
* @version	$Id$
*
* @ingroup	ModulesTest
*/

class ilTestManScoringParticipantsTableGUI extends ilTable2GUI
{
    const PARENT_DEFAULT_CMD = 'showManScoringParticipantsTable';
    const PARENT_APPLY_FILTER_CMD = 'applyManScoringParticipantsFilter';
    const PARENT_RESET_FILTER_CMD = 'resetManScoringParticipantsFilter';
    
    const PARENT_EDIT_SCORING_CMD = 'showManScoringParticipantScreen';

    // patch begin: manual scoring pilot
    /**
     * @var bool
     */
    protected $editScoringPilot = false;

    /**
     * @return null
     */
    public function isEditScoringPilot()
    {
        return $this->editScoringPilot;
    }

    /**
     * @param bool $editScoringPilot
     */
    public function setEditScoringPilot($editScoringPilot)
    {
        $this->editScoringPilot = $editScoringPilot;
    }
    // patch end: manual scoring pilot

    
    /**
     * @global	ilCtrl		$ilCtrl
     * @global	ilLanguage	$lng
     * @param	ilObjectGUI	$parentObj
     */
    public function __construct($parentObj)
    {
        $this->setPrefix('manScorePartTable');
        $this->setId('manScorePartTable');

        parent::__construct($parentObj, self::PARENT_DEFAULT_CMD);
        
        $this->setFilterCommand(self::PARENT_APPLY_FILTER_CMD);
        $this->setResetCommand(self::PARENT_RESET_FILTER_CMD);

        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];

        $this->setFormName('manScorePartTable');
        $this->setStyle('table', 'fullwidth');

        $this->enable('header');

        $this->setFormAction($ilCtrl->getFormAction($parentObj, self::PARENT_DEFAULT_CMD));

        $this->setRowTemplate("tpl.il_as_tst_man_scoring_participant_tblrow.html", "Modules/Test");

        $this->initColumns();
        $this->initOrdering();

        $this->initFilter();
    }

    // patch begin: manual scoring pilot
    //private function initColumns()
    public function initColumns()
    // patch end: manual scoring pilot
    {
        // patch begin: manual scoring pilot
        $this->column = array();
        // patch end: manual scoring pilot
        global $DIC;
        $lng = $DIC['lng'];
        
        if ($this->parent_obj->object->getAnonymity()) {
            $this->addColumn($lng->txt("name"), 'lastname', '');
        } else {
            $this->addColumn($lng->txt("lastname"), 'lastname', '');
            $this->addColumn($lng->txt("firstname"), 'firstname', '');
            $this->addColumn($lng->txt("login"), 'login', '');
        }

        // patch begin: manual scoring pilot
        if( $this->isEditScoringPilot() )
        {
            $this->addColumn($lng->txt('points'), 'points', '');
            $this->addColumn($lng->txt('tst_mark'), 'mark', '');
            $this->addColumn($lng->txt('tst_scoringdone'), 'scoringdone', '');
        }
        // patch end: manual scoring pilot
        
        $this->addColumn('', '', '1%');
    }
    
    private function initOrdering()
    {
        $this->enable('sort');

        $this->setDefaultOrderField("lastname");
        $this->setDefaultOrderDirection("asc");
    }

    public function numericOrdering($field)
    {
        return in_array($field, array('points'));
    }

    public function initFilter()
    {
        global $DIC;
        $lng = $DIC['lng'];
        
        $this->setDisableFilterHiding(true);
        
        include_once 'Services/Form/classes/class.ilSelectInputGUI.php';
        $participantStatus = new ilSelectInputGUI($lng->txt('tst_participant_status'), 'participant_status');

        $statusOptions = array();
        $statusOptions[ilTestScoringGUI::PART_FILTER_ALL_USERS] = $lng->txt("all_users");
        $statusOptions[ilTestScoringGUI::PART_FILTER_MANSCORING_NONE] = $lng->txt("manscoring_none");
        $statusOptions[ilTestScoringGUI::PART_FILTER_MANSCORING_DONE] = $lng->txt("manscoring_done");
        $statusOptions[ilTestScoringGUI::PART_FILTER_ACTIVE_ONLY] = $lng->txt("usr_active_only");
        $statusOptions[ilTestScoringGUI::PART_FILTER_INACTIVE_ONLY] = $lng->txt("usr_inactive_only");
        //$statusOptions[ ilTestScoringGUI::PART_FILTER_MANSCORING_PENDING ]	= $lng->txt("manscoring_pending");

        $participantStatus->setOptions($statusOptions);

        $this->addFilterItem($participantStatus);

        $participantStatus->readFromSession();
        
        if (!$participantStatus->getValue()) {
            $participantStatus->setValue(ilTestScoringGUI::PART_FILTER_MANSCORING_NONE);
        }
    }

    /**
     * @global	ilCtrl		$ilCtrl
     * @global	ilLanguage	$lng
     * @param	array		$row
     */
    public function fillRow($row)
    {
        //vd($row);
        
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];

        $ilCtrl->setParameter($this->parent_obj, 'active_id', $row['active_id']);
    
        if (!$this->parent_obj->object->getAnonymity()) {
            $this->tpl->setCurrentBlock('personal');
            $this->tpl->setVariable("PARTICIPANT_FIRSTNAME", $row['firstname']);
            $this->tpl->setVariable("PARTICIPANT_LOGIN", $row['login']);
            $this->tpl->parseCurrentBlock();
        }
        // patch begin: manual scoring pilot
        if(!$this->isEditScoringPilot())
        {
        // patch end: manual scoring pilot
        $this->tpl->setVariable("PARTICIPANT_LASTNAME", $row['lastname']);
        // patch begin: manual scoring pilot
        }
        else
        {
            $this->tpl->setVariable("PARTICIPANT_LASTNAME", $row['lastname']);

            $this->tpl->setVariable("PARTICIPANT_HREF_SCORE", $DIC->ctrl()->getLinkTargetByClass(
                ilTestScoringEssayGUI::class, 'showManualScoring'
            ));
        }
        // patch end: manual scoring pilot

        // patch begin: manual scoring pilot
        if( $this->isEditScoringPilot() )
        {
            $this->tpl->setCurrentBlock('extended_info');
            $this->tpl->setVariable("PARTICIPANT_POINTS", $this->buildPointsString($row));
            $this->tpl->setVariable("PARTICIPANT_GRADE", $row['final_mark']);
            $this->tpl->setVariable("PARTICIPANT_SCORED", $this->buildManScoringDoneString($row));
            $this->tpl->parseCurrentBlock();

            $this->tpl->setVariable("ACTIONS_LIST", $this->buildActionsMenu($row)->getHTML());
        }
        else
        {
        // patch end: manual scoring pilot
        $this->tpl->setVariable("HREF_SCORE_PARTICIPANT", $ilCtrl->getLinkTarget($this->parent_obj, self::PARENT_EDIT_SCORING_CMD));
        $this->tpl->setVariable("TXT_SCORE_PARTICIPANT", $lng->txt('tst_edit_scoring'));
        // patch begin: manual scoring pilot
        }
        // patch end: manual scoring pilot
    }
    
    public function getInternalyOrderedDataValues()
    {
        $this->determineOffsetAndOrder();
        
        return ilUtil::sortArray(
            $this->getData(),
            $this->getOrderField(),
            $this->getOrderDirection(),
            $this->numericOrdering($this->getOrderField())
        );
    }
    // patch begin: manual scoring pilot
    protected function buildActionsMenu($row)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $asl = new ilAdvancedSelectionListGUI();

        $asl->addItem(
            $DIC->language()->txt('tst_scoring'),
            '',
            $DIC->ctrl()->getLinkTargetByClass(
                ilTestScoringEssayGUI::class, 'showManualScoring'
            )
        );

        if( $this->isManScoringDoneFiltered() )
        {
            $asl->addItem(
                $DIC->language()->txt('tst_mark_unscored'),
                '',
                $DIC->ctrl()->getLinkTargetByClass(
                    ilTestScoringPilotGUI::class, 'markParticipantUnscored'
                )
            );
        }
        elseif( $this->isManScoringNotDoneFiltered() )
        {
            $asl->addItem(
                $DIC->language()->txt('tst_mark_scored'),
                '',
                $DIC->ctrl()->getLinkTargetByClass(
                    ilTestScoringPilotGUI::class, 'markParticipantScored'
                )
            );
        }
        elseif( ilTestService::isManScoringDone($row['active_id']) )
        {
            $asl->addItem(
                $DIC->language()->txt('tst_mark_unscored'),
                '',
                $DIC->ctrl()->getLinkTargetByClass(
                    ilTestScoringPilotGUI::class, 'markParticipantUnscored'
                )
            );
        }
        else
        {
            $asl->addItem(
                $DIC->language()->txt('tst_mark_scored'),
                '',
                $DIC->ctrl()->getLinkTargetByClass(
                    ilTestScoringPilotGUI::class, 'markParticipantScored'
                )
            );
        }

        return $asl;
    }
    public function isManScoringDoneFiltered()
    {
        foreach($this->filters as $filter)
        {
            /* @var ilSelectInputGUI $filter */
            if($filter->getPostVar() != 'participant_status')
            {
                continue;
            }

            return $filter->getValue() == ilTestScoringGUI::PART_FILTER_MANSCORING_DONE;
        }
    }
    public function isManScoringNotDoneFiltered()
    {
        foreach($this->filters as $filter)
        {
            /* @var ilSelectInputGUI $filter */
            if($filter->getPostVar() != 'participant_status')
            {
                continue;
            }

            return $filter->getValue() == ilTestScoringGUI::PART_FILTER_MANSCORING_NONE;
        }
    }
    protected function buildManScoringDoneString($row)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        if( $row['manscoring_done'] )
        {
            return $DIC->language()->txt('yes');
        }

        return $DIC->language()->txt('no');
    }
    protected function buildPointsString($row)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        return sprintf(
            $DIC->language()->txt('tst_reached_points_of_max'),
            $row['reached_points'],
            $row['max_points']
        );
    }
    // patch end: manual scoring pilot
}
