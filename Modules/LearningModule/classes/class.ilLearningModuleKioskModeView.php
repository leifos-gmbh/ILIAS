<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\KioskMode\ControlBuilder;
use ILIAS\KioskMode\State;
use ILIAS\KioskMode\URLBuilder;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\MessageBox\MessageBox;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ilLearningModuleKioskModeView
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLearningModuleKioskModeView extends ilKioskModeView
{
    const CMD_TOGGLE_LEARNING_PROGRESS = 'toggleManualLearningProgress';

    /** @var \ilObjLearningModule */
    protected $lm;

    /**
     * @var \ilLMPresentationService
     */
    protected $lm_pres_service;
    /**
     * @var \ilLMPresentationGUI
     */
    protected $lm_pres;

    /** @var \ilObjUser */
    protected $user;

    /** @var Factory */
    protected $uiFactory;

    /** @var Renderer */
    protected $uiRenderer;

    /** @var \ilCtrl */
    protected $ctrl;

    /** @var \ilTemplate */
    protected $mainTemplate;

    /** @var ServerRequestInterface */
    protected $httpRequest;

    /** @var \ilTabsGUI */
    protected $tabs;

    /** @var MessageBox */
    protected $messages = [];

    protected $current_page_id = 0;

    /**
     * @inheritDoc
     */
    protected function getObjectClass() : string
    {
        return \ilObjLearningModule::class;
    }

    /**
     * @inheritDoc
     */
    protected function setObject(\ilObject $object)
    {
        global $DIC;

        $this->lm = $object;
        $this->ctrl = $DIC->ctrl();
        $this->mainTemplate = $DIC->ui()->mainTemplate();
        $this->uiFactory = $DIC->ui()->factory();
        $this->uiRenderer = $DIC->ui()->renderer();
        $this->httpRequest = $DIC->http()->request();
        $this->tabs = $DIC->tabs();
        $this->user = $DIC->user();
    }

    /**
     * @inheritDoc
     */
    public function updateGet(State $state, string $command, int $param = null) : State
    {
        switch ($command) {
            case "layout":
                if ($param > 0) {
                    $this->current_page_id = $param;
                    $state->withValueFor("current_page", (string) $this->current_page_id);
                }
                break;
            case self::CMD_TOGGLE_LEARNING_PROGRESS:
                $this->toggleLearningProgress($command);
                break;
        }

        $this->initLMService();

        return $state;
    }

    /**
     * Init learning module presentation service
     */
    protected function initLMService($current_page = "")
    {
        if (is_object($this->lm_pres)) {
            return;
        }
        $current_page = $this->current_page_id;
        $this->lm_pres = new ilLMPresentationGUI(
            "",
            false,
            "",
            false,
            ["ref_id" => $this->lm->getRefId(),
             "obj_id" => (int) $current_page],
            true
        );

        $this->lm_pres_service = $this->lm_pres->getService();
    }

    /**
     * @inheritDoc
     */
    protected function hasPermissionToAccessKioskMode() : bool
    {
        return $this->access->checkAccess('read', '', $this->lm->getRefId());
    }

    /**
     * @inheritDoc
     */
    public function buildInitialState(State $state) : State
    {
        $state->withValueFor("current_page", "");
        return $state;
    }

    /**
     * @inheritDoc
     */
    public function buildControls(State $state, ControlBuilder $builder)
    {
        // this may be necessary if updateGet has not been processed

        // THIS currently fails
        //$this->initLMService($state->getValueFor("current_page"));
        $this->initLMService();

        $nav_stat = $this->lm_pres_service->getNavigationStatus();

        // next
        $succ_id = $nav_stat->getSuccessorPageId();
        if ($succ_id > 0) {
            $builder->next("layout", $succ_id);
        }

        // previous
        $prev_id = $nav_stat->getPredecessorPageId();
        if ($prev_id > 0) {
            $builder->previous("layout", $prev_id);
        }

        $this->builtLearningProgressToggleControl($builder);
    }

    /**
     * @param ControlBuilder $builder
     */
    protected function builtLearningProgressToggleControl(ControlBuilder $builder)
    {
        $learningProgress = \ilObjectLP::getInstance($this->lm->getId());
        if ($learningProgress->getCurrentMode() == \ilLPObjSettings::LP_MODE_MANUAL) {
            $isCompleted = \ilLPMarks::_hasCompleted($this->user->getId(), $this->lm->getId());

            $this->lng->loadLanguageModule('lm');
            $learningProgressToggleCtrlLabel = $this->lng->txt('lm_btn_lp_toggle_state_completed');
            if (!$isCompleted) {
                $learningProgressToggleCtrlLabel = $this->lng->txt('lm_btn_lp_toggle_state_not_completed');
            }
            $builder->generic(
                $learningProgressToggleCtrlLabel,
                self::CMD_TOGGLE_LEARNING_PROGRESS,
                1
            );
        }
    }

    /**
     * @param string $command
     */
    protected function toggleLearningProgress(string $command)
    {
        if (self::CMD_TOGGLE_LEARNING_PROGRESS === $command) {
            $learningProgress = \ilObjectLP::getInstance($this->lm->getId());
            if ($learningProgress->getCurrentMode() == \ilLPObjSettings::LP_MODE_MANUAL) {
                $marks = new \ilLPMarks($this->lm->getId(), $this->user->getId());
                $marks->setCompleted(!$marks->getCompleted());
                $marks->update();

                \ilLPStatusWrapper::_updateStatus($this->lm->getId(), $this->user->getId());

                $this->lng->loadLanguageModule('trac');

                $this->messages[] = $this->uiFactory->messageBox()->success(
                    $this->lng->txt('trac_updated_status')
                );
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function updatePost(State $state, string $command, array $post) : State
    {
        return $state;
    }

    /**
     * @inheritDoc
     */
    public function render(
        State $state,
        Factory $factory,
        URLBuilder $url_builder,
        array $post = null
    ) : Component {
        $this->ctrl->setParameterByClass("illmpresentationgui", 'ref_id', $this->lm->getRefId());
        $content = $this->ctrl->getHTML($this->lm_pres, ["cmd" => "layout"], ["illmpresentationgui"]);
        return $factory->legacy($content);
    }

    /**
     * Renders the content style of a ContentPage object into main template
     */
    protected function renderContentStyle()
    {
        $this->mainTemplate->addCss(\ilObjStyleSheet::getSyntaxStylePath());
        $this->mainTemplate->addCss(
            \ilObjStyleSheet::getContentStylePath(
                $this->contentPageObject->getStyleSheetId()
            )
        );
    }
}
