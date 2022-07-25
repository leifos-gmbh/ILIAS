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
 * GUI class for glossary flashcard boxes
 * @author Thomas Famula <famula@leifos.de>
 */
class ilGlossaryFlashcardBoxGUI
{
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected \ILIAS\UI\Factory $ui_fac;
    protected \ILIAS\UI\Renderer $ui_ren;
    protected \ILIAS\Glossary\Presentation\PresentationGUIRequest $request;

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ui_fac = $DIC->ui()->factory();
        $this->ui_ren = $DIC->ui()->renderer();
        $this->request = $DIC->glossary()
                             ->internal()
                             ->gui()
                             ->presentation()
                             ->request();

        $this->ctrl->saveParameter($this, array("box_id"));
    }

    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            default:
                $cmd = $this->ctrl->getCmd("show");
                $ret = $this->$cmd();
                break;
        }
    }

    public function show() : void
    {
        if (true) { // hier prüfen ob es bereits Flashcards vom heutigen Tag gibt in der Box
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($this->ctrl->getFormAction($this));
            $cgui->setHeaderText($this->lng->txt("Hier fragen welche Items benutzen"));
            $cgui->setCancel($this->lng->txt("use_remaining_items"), "showRemainingItems");
            $cgui->setConfirm($this->lng->txt("use_all_items"), "showAllItems");
            $this->tpl->setContent($cgui->getHTML());
        } else {
            // Box mit allen Terms füllen
            $this->showAllItems();
        }
    }

    public function showAllItems() : void
    {
        // hier alle Items in die Box einfügen
        $this->ctrl->redirect($this, "showHidden");
    }

    public function showRemainingItems() : void
    {
        // hier nur Items in die Box einfügen, die nicht von heute sind
        $this->ctrl->redirect($this, "showHidden");
    }

    public function showHidden() : void
    {
        $this->tpl->setDescription($this->lng->txt("box") . " " . $this->request->getBoxId()); //Sprachvariable

        $progress_bar = ilProgressBar::getInstance();
        $progress_bar->setCurrent(30); // Berechnung des Progress in Manager Klasse

        if (true) { //prüfen auf Modus
            $flashcard = $this->ui_fac->panel()->standard(
                $this->lng->txt("term") . ": ???", //Sprachvariable
                $this->ui_fac->legacy($this->getDefinitionText(0))
            );
        } else {
            $flashcard = $this->ui_fac->panel()->standard(
                $this->lng->txt("term") . ": " . $this->getTermText(0), //Sprachvariable
                $this->ui_fac->legacy("...")
            );
        }

        if (true) { //prüfen auf Modus
            $btn_show = $this->ui_fac->button()->standard(
                $this->lng->txt("show_term"), //Sprachvariable
                $this->ctrl->getLinkTarget($this, "showRevealed")
            );
        } else {
            $btn_show = $this->ui_fac->button()->standard(
                $this->lng->txt("show_def"), //Sprachvariable
                $this->ctrl->getLinkTarget($this, "showRevealed")
            );
        }

        $btn_quit = $this->ui_fac->button()->standard(
            $this->lng->txt("quit_box"), //Sprachvariable
            $this->ctrl->getLinkTargetByClass("ilglossaryflashcardgui", "listBoxes")
        );

        $html = $progress_bar->render() . $this->ui_ren->render($flashcard) . $this->ui_ren->render($btn_show) .
                $this->ui_ren->render($btn_quit);
        $this->tpl->setContent($html);
    }

    public function showRevealed() : void
    {
        $this->tpl->setDescription($this->lng->txt("box") . " " . $this->request->getBoxId()); //Sprachvariable

        $progress_bar = ilProgressBar::getInstance();
        $progress_bar->setCurrent(30); // Berechnung des Progress in Manager Klasse

        $flashcard = $this->ui_fac->panel()->standard(
            $this->lng->txt("term") . ": " . $this->getTermText(0), //Sprachvariable
            $this->ui_fac->legacy($this->getDefinitionText(0))
        );

        $btn_correct = $this->ui_fac->button()->standard(
            $this->lng->txt("answered_correctly"), //Sprachvariable
            $this->ctrl->getLinkTarget($this, "showHidden") // hier irgendwie zum nächsten Term kommen
        );

        $btn_not_correct = $this->ui_fac->button()->standard(
            $this->lng->txt("answered_not_correctly"), //Sprachvariable
            $this->ctrl->getLinkTarget($this, "showHidden") // hier irgendwie zum nächsten Term kommen
        );

        $html = $progress_bar->render() . $this->ui_ren->render($flashcard) . $this->ui_ren->render($btn_correct) .
            $this->ui_ren->render($btn_not_correct);
        $this->tpl->setContent($html);
    }

    protected function getTermText(int $term_id) : string // wahrscheinlich in Manager Klasse erledigen und nicht hier als Funktion
    {
        return "Lorem ipsum"; // Hier kommt der Termtext hin
    }

    protected function getDefinitionText(int $term_id) : string // wahrscheinlich in Manager Klasse erledigen und nicht hier als Funktion
    {
        return "Lorem ipsum dolor sit amet"; // Hier kommt der Definitionstext hin
    }
}
