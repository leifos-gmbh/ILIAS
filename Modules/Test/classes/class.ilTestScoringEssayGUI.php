<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestScoringEssayGUI
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @package     Services/AssessmentQuestion
 */
class ilTestScoringEssayGUI extends ilTestScoringGUI
{
    const MIN_MAIN_FRAME_WIDTH = '200px';
    const MIN_SIDE_FRAME_WIDTH = '100px';

    const INIT_SIDE_FRAME_WIDTH = '33%';
    const INIT_SIDE_FRAME_HIDDEN = false;

    const RESPECT_SIDE_FRAME_COOKIES = true;

    const ADDITIONALCMD_PARAMETER = 'additionalCommand';
    const CHANGEPASS_PARAMETER = 'passIndex';
    const CHANGEQST_PARAMETER = 'questionId';

    /**
     * @var int
     */
    protected $curActiveId = null;

    /**
     * @var int
     */
    protected $curPassIndex = null;

    /**
     * @var int
     */
    protected $curQuestionId = null;

    /**
     * @var array
     */
    protected $questionGuiList = array();

    /**
     * @param ilObjTest $a_object
     */
    public function __construct(ilObjTest $a_object)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        parent::__construct($a_object);

        if( isset($_POST['pass']) )
        {
            $_GET['pass'] = $_POST['pass'];
        }

        $DIC->ctrl()->saveParameter($this, 'active_id');
        $DIC->ctrl()->saveParameter($this, 'pass');
        $DIC->ctrl()->saveParameter($this, 'question_id');
    }

    /**
     * @return string
     */
    protected function getDefaultCommand()
    {
        return 'showManualScoring';
    }

    protected function initialise()
    {
        $this->curActiveId = $this->fetchActiveIdParameter();
        $this->curPassIndex = $this->fetchPassParameter($this->curActiveId);

        $this->questionGuiList = $this->service->getManScoringQuestionGuiList($this->curActiveId, $this->curPassIndex);
        $this->curQuestionId = $this->fetchQuestionIdParameter($this->curActiveId, $this->curPassIndex);
    }

    protected function fetchQuestionIdParameter($activeId, $passIndex)
    {
        if( isset($_POST['question_id']) && 0 < (int)$_POST['question_id'] ) {
            $questionId = (int)$_POST['question_id'];
        } elseif( isset($_GET['question_id']) && 0 < (int)$_GET['question_id'] ) {
            $questionId = (int)$_GET['question_id'];
        } else {
            $questionGui = current($this->questionGuiList);
            $questionId = $questionGui->object->getId();
        }

        return $questionId;
    }

    protected function checkAccess(ilTestParticipantList $participantList, ilTestPassesSelector $passSelector)
    {
        if( !$participantList->isActiveIdInList($this->curActiveId) )
        {
            ilObjTestGUI::accessViolationRedirect();
        }

        if( !in_array($this->curPassIndex, $passSelector->getClosedPasses()) )
        {
            ilObjTestGUI::accessViolationRedirect();
        }

        foreach($this->questionGuiList as $questionGui)
        {
            if( $questionGui->object->getId() == $this->curQuestionId )
            {
                return;
            }
        }

        ilObjTestGUI::accessViolationRedirect();
    }

    public function executeCommand()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $DIC->tabs()->clearTargets();
        $DIC->tabs()->clearSubTabs();

        $DIC->tabs()->setBackTarget(
            $DIC->language()->txt('back'),
            $DIC->ctrl()->getLinkTargetByClass('ilTestScoringPilotGUI')
        );

        $this->initialise();

        switch( $DIC->ctrl()->getNextClass($this) )
        {
            default:

                $this->saveParameters();

                $command = $DIC->ctrl()->getCmd($this->getDefaultCommand()).'Cmd';
                $this->{$command}();
        }
    }

    protected function saveParameters()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $DIC->ctrl()->setParameter($this, 'active_id', $this->curActiveId);
        $DIC->ctrl()->setParameter($this, 'pass', $this->curPassIndex);
        $DIC->ctrl()->setParameter($this, 'question_id', $this->curQuestionId);
    }

    /**
     * @return ilTestParticipantList
     */
    protected function buildParticipantList()
    {
        $participantList = new ilTestParticipantList($this->object);

        $participantList->initializeFromDbRows(
            $this->object->getTestParticipantsForManualScoring()
        );

        $participantList = $participantList->getAccessFilteredList(
            ilTestParticipantAccessFilter::getScoreParticipantsUserFilter($this->ref_id)
        );

        return $participantList;
    }

    /**
     * @return ilTestPassesSelector
     */
    protected function buildPassSelector()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        $passSelector = new ilTestPassesSelector($DIC->database(), $this->object);
        $passSelector->setActiveId($this->curActiveId);
        $passSelector->loadLastFinishedPass();
        return $passSelector;
    }

    protected function showManualScoringCmd()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $participantList = $this->buildParticipantList();
        $passSelector = $this->buildPassSelector();

        $this->checkAccess($participantList, $passSelector);
        $this->saveParameters();

        $questionGuiList = $this->service->getManScoringQuestionGuiList(
            $this->curActiveId, $this->curPassIndex
        );

        $this->buildToolbar($passSelector, $questionGuiList);

        $questionGui = $this->getCurrentQuestionGUI();

        $mainContent = $this->getMainFrameContent($questionGui);
        $leftContent = $this->getLeftFrameContent($questionGui);
        $rightContent = $this->getRightFrameContent($questionGui);

        $frameSet = $this->buildFrameset('msp'.$this->curQuestionId,
            $mainContent, $leftContent, $rightContent
        );

        $panel = $DIC->ui()->factory()->panel()->standard($this->buildPanelTitle(), $frameSet);

        $DIC->ui()->mainTemplate()->setContent($this->buildTextQuestionOutput($panel));
    }

    protected function saveManualScoringCmd()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $manScored = isset($_POST['manual_scored']) && (bool)$_POST['manual_scored'];
        ilTestService::setManScoringDone($this->curActiveId, $manScored);

        $this->object->saveManualFeedback(
            $this->curActiveId, $this->curQuestionId, $this->curPassIndex,
            $this->getCurrentQuestionGUI()->object->getHtmlQuestionContentPurifier()->purify($_POST['manual_feedback'])
        );

        $pointsValidAndSaved = false;

        $manPoints = $_POST['manual_points'];
        $maxPoints = assQuestion::_getMaximumPoints($this->curQuestionId);

        if( is_numeric($manPoints) && $manPoints >= 0 && $manPoints <= $maxPoints )
        {
            assQuestion::_setReachedPoints(
                $this->curActiveId,
                $this->curQuestionId,
                $manPoints,
                $maxPoints,
                $this->curPassIndex,
                1,
                $this->object->areObligationsEnabled()
            );

            $pointsValidAndSaved = true;
        }
        else
        {
            $failureMessage = sprintf($DIC->language()->txt('invalid_man_scoring_points'), $maxPoints, $manPoints);
            ilUtil::sendFailure($failureMessage, true);

            assQuestion::_setReachedPoints(
                $this->curActiveId,
                $this->curQuestionId,
                0,
                $maxPoints,
                $this->curPassIndex,
                1,
                $this->object->areObligationsEnabled()
            );
        }

        if( $pointsValidAndSaved )
        {
            $this->handleAdditionalActions();
        }

        $DIC->ctrl()->redirect($this, 'showManualScoring');
    }

    protected function handleAdditionalActions()
    {
        if( isset($_POST[self::ADDITIONALCMD_PARAMETER]) && strlen($_POST[self::ADDITIONALCMD_PARAMETER]) )
        {
            switch($_POST[self::ADDITIONALCMD_PARAMETER])
            {
                case 'changeQuestion':
                    $this->handleChangeQuestionAction();
                    break;

                case 'sendNotification':
                    $this->handleSendNotificationAction();
                    break;
            }
        }
    }

    protected function handleChangeQuestionAction()
    {
        if( isset($_POST[self::CHANGEPASS_PARAMETER]) )
        {
            $this->curPassIndex = (int)$_POST[self::CHANGEPASS_PARAMETER];
        }

        if( isset($_POST[self::CHANGEQST_PARAMETER]) )
        {
            $this->curQuestionId = (int)$_POST[self::CHANGEQST_PARAMETER];
        }

        $this->saveParameters();
    }

    protected function handleSendNotificationAction()
    {
        $notificationData[$this->curQuestionId] = array(
            'points' => assQuestion::_getReachedPoints($this->curActiveId, $this->curQuestionId),
            'feedback' => $this->object->getManualFeedback(
                $this->curActiveId, $this->curQuestionId, $this->curPassIndex
            )
        );

        $notification = new ilTestManScoringParticipantNotification(
            $this->object->_getUserIdFromActiveId($this->curActiveId),
            $this->object->getRefId()
        );

        $notification->setAdditionalInformation(array(
            'test_title' => $this->object->getTitle(),
            'test_pass' => $this->curPassIndex + 1,
            'questions_gui_list' => $this->questionGuiList,
            'questions_scoring_data' => $notificationData
        ));

        $notification->send();
    }

    protected function buildTextQuestionOutput($panel)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $manualScoringPilot = $DIC->ui()->renderer()->render($panel) . $this->getJavacript();

        $tpl = new ilTemplate('tpl.manual_scoring_pilot.html', true, true, 'Modules/Test');
        $tpl->setCurrentBlock('manual_scoring_pilot');
        $tpl->setVariable('MANUAL_SCORING_PILOT', $manualScoringPilot);
        $tpl->parseCurrentBlock();

        return $tpl->get();
    }

    /**
     * @return string
     */
    protected function getJavacript()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $tpl = new ilTemplate('tpl.manual_scoring_essay.html', true, true, 'Modules/Test');

        $tpl->setVariable('ADDITIONALCMD_PARAMETER', self::ADDITIONALCMD_PARAMETER);
        $tpl->setVariable('CHANGEPASS_PARAMETER', self::CHANGEPASS_PARAMETER);
        $tpl->setVariable('CHANGEQST_PARAMETER', self::CHANGEQST_PARAMETER);

        $tpl->setVariable('ID', $this->curQuestionId);

        return $tpl->get();
    }

    /**
     * @return assQuestionGUI
     */
    protected function getCurrentQuestionGUI()
    {
        foreach($this->questionGuiList as $questionGUI)
        {
            if( $questionGUI->object->getId() != $this->curQuestionId )
            {
                continue;
            }

            return $questionGUI;
        }
    }

    protected function buildPanelTitle()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $userId = $this->object->_getUserIdFromActiveId($this->curActiveId);
        $userFullname = $this->object->userLookupFullName($userId, false, true);

        $title = $DIC->language()->txt('tst_participant').': '.$userFullname.'<br />';
        $title .= $DIC->language()->txt('pass') . ' ' . ($this->curPassIndex + 1);
        $title .= ': ' . $this->getCurrentQuestionGUI()->object->getTitle();

        return $title;
    }

    /**
     * @param string $identifier
     * @param string $mainContent
     * @param string $leftContent
     * @param string $rightContent
     * @return \ILIAS\UI\Component\Frameset\Set
     */
    protected function buildFrameset($identifier, $mainContent, $leftContent, $rightContent)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $f = $DIC->ui()->factory();

        $mainFrame = $f->frameset()->frame($DIC->ui()->factory()->legacy($mainContent))
                                   ->withMinimalWidth(self::MIN_MAIN_FRAME_WIDTH);

        $leftFrame = $f->frameset()->frame($DIC->ui()->factory()->legacy($leftContent))
                                   ->withMinimalWidth(self::MIN_SIDE_FRAME_WIDTH)
                                   ->withInitialWidth(self::INIT_SIDE_FRAME_WIDTH)
                                   ->withInitiallyHidden(self::INIT_SIDE_FRAME_HIDDEN);

        $rightFrame = $f->frameset()->frame($DIC->ui()->factory()->legacy($rightContent))
                                    ->withMinimalWidth(self::MIN_SIDE_FRAME_WIDTH)
                                    ->withInitialWidth(self::INIT_SIDE_FRAME_WIDTH)
                                    ->withInitiallyHidden(self::INIT_SIDE_FRAME_HIDDEN);

        $frameSet = $f->frameset()->set($identifier, $mainFrame)
                                  ->withLeftFrame($leftFrame)
                                  ->withRightFrame($rightFrame)
                                  ->withRespectCookies(self::RESPECT_SIDE_FRAME_COOKIES);

        $frameSet = $frameSet->withJavascriptAfterResizeCallback('resizeTinyMce');

        return $frameSet;
    }

    protected function getMainFrameContent(assTextQuestionGUI $questionGui)
    {
        return $questionGui->getUserSolutionSnippet($this->curActiveId, $this->curPassIndex);
    }

    protected function getLeftFrameContent(assTextQuestionGUI $questionGui)
    {
        return $questionGui->getQuestionTextSnippet();
    }

    protected function getRightFrameContent(assTextQuestionGUI $questionGui)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $editorId = "tinymce_manscoring_{$questionGui->object->getId()}";

        $DIC->ctrl()->setParameter($this, 'cmd', 'post');
        $formaction = $DIC->ctrl()->getFormAction($this);
        $DIC->ctrl()->setParameter($this, 'cmd', '');

        $rtestring = ilRTE::_getRTEClassname();
        $rte = new $rtestring(); /* @var ilTinyMCE $rte */
        // patch begin: manual scoring pilot
        require_once 'Services/RTE/classes/class.ilRTEManualScoringPilot.php';
        $rtestring = ilRTEManualScoringPilot::_getRTEClassname();
        $rte = new $rtestring(); /* @var ilTinyMCEManualScoringPilot $rte */
        // patch end: manual scoring pilot
        $rte->addRteSupport($this->object->getId(), $this->object->getType(), $editorId);

        $manualFeedback = $this->object->getManualFeedback(
            $this->curActiveId, $this->curQuestionId, $this->curPassIndex
        );

        $manualPoints = assQuestion::_getReachedPoints($this->curActiveId, $this->curQuestionId, $this->curPassIndex);
        $manualPoints = $manualPoints ? $manualPoints : '';

        $maxPoints = assQuestion::_getMaximumPoints($this->curQuestionId);

        $manualScored = ilTestService::isManScoringDone($this->curActiveId);

        $formHtml = $this->renderManualScoringForm(
            $formaction, $editorId, $manualFeedback, $manualPoints, $maxPoints, $manualScored
        );

        return $formHtml;
    }

    /**
     * @param string $formaction
     * @param string $editorId
     * @param string $manualFeedback
     * @param integer $manualPoints
     * @param integer $maxPoints
     * @param bool $manualScored
     * @return string
     * @throws ilTemplateException
     */
    protected function renderManualScoringForm($formaction, $editorId, $manualFeedback, $manualPoints, $maxPoints, $manualScored)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $tpl = new ilTemplate('tpl.manual_scoring_rawform.html', true, true, 'Modules/Test');

        $tpl->setCurrentBlock('rawform');

        $tpl->setVariable('FORMACTION', $formaction);
        $tpl->setVariable('EDITOR_SELECTOR', $editorId);

        $tpl->setVariable('MANUAL_FEEDBACK', $manualFeedback);

        $tpl->setVariable('POINTS_INPUT_ID', 'manual_points');
        $tpl->setVariable('POINTS_INPUT_LABEL', sprintf($DIC->language()->txt('granted_points'), $maxPoints));
        $tpl->setVariable('POINTS_INPUT_VALUE', $manualPoints);

        $tpl->setVariable('SCORED_INPUT_ID', 'manual_scored');
        $tpl->setVariable('SCORED_INPUT_LABEL', $DIC->language()->txt('tst_mark_scored'));
        $tpl->setVariable('SCORED_INPUT_VALUE', $manualScored ? 'checked="checked"' : '');

        $tpl->setVariable('ADDITIONALCMD_PARAMETER', self::ADDITIONALCMD_PARAMETER);
        $tpl->setVariable('CHANGEPASS_PARAMETER', self::CHANGEPASS_PARAMETER);
        $tpl->setVariable('CHANGEQST_PARAMETER', self::CHANGEQST_PARAMETER);

        $tpl->setVariable('SUBMIT_LABEL', $DIC->language()->txt('save'));
        $tpl->setVariable('SUBMIT_CMD', 'saveManualScoring');

        $tpl->parseCurrentBlock();

        return $tpl->get();
    }

    /**
     * @param ilTestPassesSelector $passSelector
     * @param array $questionGuiList
     */
    protected function buildToolbar(ilTestPassesSelector $passSelector, $questionGuiList)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $passOptions = $this->buildPassDropdownOptions($passSelector);
        $qstOptions = $this->buildQuestionsDropdownOptions($questionGuiList);

        $passSelect = new ilSelectInputGUI('', 'passIndexSelect');
        $passSelect->setOptions($passOptions);
        $passSelect->setValue($this->curPassIndex);
        $passSelect->setRequired(true);
        $DIC->toolbar()->addInputItem($passSelect);

        $qstSelect = new ilSelectInputGUI('', 'questionIdSelect');
        $qstSelect->setOptions($qstOptions);
        $qstSelect->setValue($this->curQuestionId);
        $qstSelect->setRequired(true);
        $DIC->toolbar()->addInputItem($qstSelect);

        $changeQuestionBtn = $this->buildChangeQuestionButton();
        $DIC->toolbar()->addButtonInstance($changeQuestionBtn);

        $DIC->toolbar()->addSeparator();

        $sendNotificationBtn = $this->buildSendNotificationButton();
        $DIC->toolbar()->addButtonInstance($sendNotificationBtn);
    }

    /**
     * @return array
     */
    protected function buildPassDropdownOptions(ilTestPassesSelector $passSelector)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $passOptions = array();

        foreach ($passSelector->getClosedPasses() as $passIndex) {
            $passOptions[$passIndex] = $DIC->language()->txt('pass') . ' ' . ($passIndex + 1);
        }

        return $passOptions;
    }

    /**
     * @param array $questionGuiList
     * @return array
     */
    protected function buildQuestionsDropdownOptions($questionGuiList)
    {
        $qstOptions = array();

        foreach($this->questionGuiList as $questionGUI)
        {
            /* @var assQuestionGUI $questionGUI */
            $qstOptions[$questionGUI->object->getId()] = $questionGUI->object->getTitle();
        }

        return $qstOptions;
    }

    /**
     * @return ilLinkButton
     */
   protected function buildChangeQuestionButton()
    {
        $changeQuestionBtn = ilLinkButton::getInstance();
        $changeQuestionBtn->setId('changeQuestionBtn');
        $changeQuestionBtn->setCaption('open');

        return $changeQuestionBtn;
    }

    /**
     * @return ilLinkButton
     */
    protected function buildSendNotificationButton()
    {
        $sendNotificationBtn = ilLinkButton::getInstance();
        $sendNotificationBtn->setId('sendNotificationBtn');
        $sendNotificationBtn->setCaption('tst_manscoring_user_notification');

        return $sendNotificationBtn;
    }
}
