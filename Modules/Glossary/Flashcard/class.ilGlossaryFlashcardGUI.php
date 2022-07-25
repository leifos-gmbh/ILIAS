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

/**
 * GUI class for glossary flashcards
 * @author Thomas Famula <famula@leifos.de>
 * @ilCtrl_Calls ilGlossaryFlashcardGUI: ilGlossaryFlashcardBoxGUI
 */
class ilGlossaryFlashcardGUI
{
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilTabsGUI $tabs_gui;
    protected \ILIAS\UI\Factory $ui_fac;
    protected \ILIAS\UI\Renderer $ui_ren;
    protected \ILIAS\Glossary\Presentation\PresentationGUIRequest $request;

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->tabs_gui = $DIC->tabs();
        $this->ui_fac = $DIC->ui()->factory();
        $this->ui_ren = $DIC->ui()->renderer();

        //$this->ctrl->saveParameter($this, array("term_id"));

        $this->request = $DIC->glossary()
                             ->internal()
                             ->gui()
                             ->presentation()
                             ->request();

        //$this->ref_id = $this->request->getRefId();

        /*
        if ($a_id != 0) {
            $this->term = new ilGlossaryTerm($a_id);
            if (ilObject::_lookupObjectId($this->ref_id) == ilGlossaryTerm::_lookGlossaryID($a_id)) {
                $this->term_glossary = new ilObjGlossary($this->ref_id, true);
            } else {
                $this->term_glossary = new ilObjGlossary(ilGlossaryTerm::_lookGlossaryID($a_id), false);
            }
        }
        */
    }

    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            case "ilglossaryflashcardboxgui":
                $flash_boxes = new ilGlossaryFlashcardBoxGUI();
                $this->ctrl->forwardCommand($flash_boxes);
                break;

            default:
                $cmd = $this->ctrl->getCmd("listBoxes");
                $ret = $this->$cmd();
                break;
        }
    }

    public function listBoxes() : void
    {
        $this->tabs_gui->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTargetByClass("ilglossarypresentationgui", "listTerms")
        );

        $flashcard_tpl = new ilTemplate("tpl.flashcard_overview.html", true, true, "Modules/Glossary");

        $reset_btn = $this->ui_fac->button()->standard(
            $this->lng->txt("reset_all_boxes"), //Sprachvariable
            $this->ctrl->getLinkTarget($this, "confirmResetBoxes")
        );
        $flashcard_tpl->setVariable("RESET_BUTTON", $this->ui_ren->render($reset_btn));

        $intro_box = $this->ui_fac->panel()->standard(
            $this->lng->txt("introduction"), //Sprachvariable
            $this->ui_fac->legacy($this->lng->txt("flashcards_intro")) //Sprachvariable
        );
        $flashcard_tpl->setVariable("INTRO_BOX", $this->ui_ren->render($intro_box));

        $boxes = [];
        for ($b = 1; $b <= 5; $b++) {
            $box = $this->getItemBox($b);
            $boxes[] = $box;
        }

        $boxes_pnl = $this->ui_fac->panel()->listing()->standard(
            $this->lng->txt("boxes"), //Sprachvariable
            [$this->ui_fac->item()->group("", $boxes)]
        );
        $flashcard_tpl->setVariable("BOXES", $this->ui_ren->render($boxes_pnl));

        $this->tpl->setContent($flashcard_tpl->get());
    }

    protected function getItemBox(int $nr) : \ILIAS\UI\Component\Item\Item
    {
        $box = $this->ui_fac->item()->standard($this->lng->txt("box") . $nr) //Sprachvariable
            ->withProperties([
                $this->lng->txt("items") => "0", //Sprachvariable
                $this->lng->txt("box_last_presented") => "X days ago / never" //Sprachvariable
            ]);

        if ($this->isBoxFilled()) {
            $this->ctrl->setParameterByClass("ilglossaryflashcardboxgui", "box_id", $nr);

            $action = $this->ui_fac->dropdown()->standard([
                $this->ui_fac->button()->shy(
                    $this->lng->txt("start_box"), //Sprachvariable
                    $this->ctrl->getLinkTargetByClass('ilGlossaryFlashcardBoxGUI', 'show')
                ),
            ]);

            $box = $box->withActions($action);
        }

        return $box;
    }

    protected function isBoxFilled() : bool // Funktion verschieben in Manager(?) Klasse
    {
        return true;
    }

    public function confirmResetBoxes() : void
    {
        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setHeaderText($this->lng->txt("Hier rein klappt"));
        $cgui->setCancel($this->lng->txt("no"), "cancelResetBoxes");
        $cgui->setConfirm($this->lng->txt("yes"), "resetBoxes");
        $this->tpl->setContent($cgui->getHTML());
    }

    public function cancelResetBoxes() : void
    {
        $this->ctrl->redirect($this, "listBoxes");
    }

    public function resetBoxes() : void
    {
        // hier übernimmt die Manager Klasse für die Flashcards das Reset
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("boxes_reset"), true);
        $this->ctrl->redirect($this, "listBoxes");
    }
}
