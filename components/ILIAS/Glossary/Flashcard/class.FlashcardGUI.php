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

namespace ILIAS\Glossary\Flashcard;

use ILIAS\UI;
use ILIAS\Glossary;
use ILIAS\Glossary\Presentation;
use ILIAS\Glossary\Settings;

/**
 * GUI class for glossary flashcards
 * @author Thomas Famula <famula@leifos.de>
 */
class FlashcardGUI
{
    protected \ilCtrl $ctrl;
    protected \ilLanguage $lng;
    protected \ilGlobalTemplateInterface $tpl;
    protected \ilTabsGUI $tabs_gui;
    protected UI\Factory $ui_fac;
    protected UI\Renderer $ui_ren;
    protected Presentation\PresentationGUIRequest $request;
    protected int $ref_id;
    protected \ilObjGlossary $glossary;
    protected int $user_id;
    protected FlashcardManager $flashcard_manager;
    protected Settings\UserSettingsManager $user_settings_manager;

    public function __construct(
        protected Glossary\InternalDataService $data,
        protected Glossary\InternalDomainService $domain,
        protected Glossary\InternalGUIService $gui,
    ) {
        $this->ctrl = $this->gui->ctrl();
        $this->lng = $this->domain->lng();
        $this->tpl = $this->gui->ui()->mainTemplate();
        $this->tabs_gui = $this->gui->tabs();
        $this->ui_fac = $this->gui->ui()->factory();
        $this->ui_ren = $this->gui->ui()->renderer();
        $this->request = $this->gui->presentation()->request();
        $this->ref_id = $this->request->getRefId();
        $this->glossary = new \ilObjGlossary($this->ref_id);
        $this->user_id = $this->domain->user()->getId();
        $this->flashcard_manager = $this->domain->flashcard($this->ref_id);
        $this->user_settings_manager = $this->domain->userSettings();
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            case strtolower(FlashcardBoxGUI::class):
                $flash_boxes = $this->gui->flashcard()->flashcardBoxGUI();
                $this->ctrl->forwardCommand($flash_boxes);
                break;

            default:
                $cmd = $this->ctrl->getCmd("listBoxes");
                $ret = $this->$cmd();
                break;
        }
    }

    public function listBoxes(): void
    {
        $flashcard_tpl = new \ilTemplate("tpl.flashcard_overview.html", true, true, "components/ILIAS/Glossary");

        $reset_btn = $this->ui_fac->button()->standard(
            $this->lng->txt("glo_reset_all_boxes"),
            $this->ctrl->getLinkTarget($this, "confirmResetBoxes")
        );
        $flashcard_tpl->setVariable("RESET_BUTTON", $this->ui_ren->render($reset_btn));

        if ($this->glossary->isFlashcardModeChange()) {
            $mode_control = $this->ui_fac->input()->viewControl()->mode(
                [
                    FlashcardBox::MODE_TERM => $this->lng->txt("glo_term_vs_def"),
                    FlashcardBox::MODE_DEFINITION => $this->lng->txt("glo_def_vs_term"),
                ]
            )->withValue($this->getCurrentFlashcardMode());
            $vc_container = $this->ui_fac->input()->container()->viewControl()->standard([$mode_control])
                                         ->withRequest($this->gui->http()->request());
            $flashcard_tpl->setVariable("MODE_CONTROL", $this->ui_ren->render($vc_container));

            if ($vc_data = $vc_container->getData()) {
                $this->setFlashcardMode($vc_data);
            }
        }

        $intro_box = $this->ui_fac->panel()->standard(
            $this->lng->txt("glo_introduction"),
            $this->ui_fac->legacy()->content($this->lng->txt("glo_flashcards_intro"))
        );
        $flashcard_tpl->setVariable("INTRO_BOX", $this->ui_ren->render($intro_box));

        $boxes = [];
        for ($b = 1; $b <= 5; $b++) {
            $box = $this->getItemBox($b);
            $boxes[] = $box;
        }

        $boxes_pnl = $this->ui_fac->panel()->listing()->standard(
            $this->lng->txt("glo_boxes"),
            [$this->ui_fac->item()->group("", $boxes)]
        );
        $flashcard_tpl->setVariable("BOXES", $this->ui_ren->render($boxes_pnl));

        $this->tpl->setContent($flashcard_tpl->get());
    }

    protected function getCurrentFlashcardMode(): string
    {
        if ($settings = $this->user_settings_manager->getByUserAndGlossary($this->user_id, $this->glossary->getId())) {
            return $settings->getFlashcardMode();
        }
        return $this->glossary->getFlashcardModeDefault();
    }

    protected function setFlashcardMode(array $vc_data): void
    {
        $mode = $vc_data[0];
        $user_settings = $this->data->userSettings($this->user_id, $this->glossary->getId(), $mode);

        if ($this->user_settings_manager->getByUserAndGlossary($this->user_id, $this->glossary->getId())) {
            $this->user_settings_manager->update($user_settings);
        } else {
            $this->user_settings_manager->create($user_settings);
        }
    }

    protected function getItemBox(int $nr): \ILIAS\UI\Component\Item\Item
    {
        $item_cnt = $this->flashcard_manager->getItemsForBoxCount($nr);
        $last_access = $this->flashcard_manager->getLastAccessForBoxAsDaysText($nr);

        if (($this->flashcard_manager->getUserTermsForBox($nr) && $nr !== FlashcardBox::LAST_BOX)
            || ($this->flashcard_manager->getAllTermsWithoutEntry() && $nr === FlashcardBox::FIRST_BOX)) {
            $this->ctrl->setParameterByClass(strtolower(FlashcardBoxGUI::class), "box_id", $nr);
            $title = $this->ui_fac->link()->standard(
                $this->lng->txt("glo_box") . " " . $nr,
                $this->ctrl->getLinkTargetByClass(strtolower(FlashcardBoxGUI::class), "show")
            );
        } else {
            $title = $this->lng->txt("glo_box") . " " . $nr;
        }

        $box = $this->ui_fac->item()->standard($title);
        if ($nr === FlashcardBox::LAST_BOX) {
            $box = $box->withProperties([
                $this->lng->txt("glo_flashcards") => (string) $item_cnt
            ]);
        } else {
            $box = $box->withProperties([
                $this->lng->txt("glo_flashcards") => (string) $item_cnt,
                $this->lng->txt("glo_box_last_presented") => $last_access
            ]);
        }

        return $box;
    }

    public function confirmResetBoxes(): void
    {
        $yes_button = $this->ui_fac->button()->standard(
            $this->lng->txt("yes"),
            $this->ctrl->getLinkTarget($this, "resetBoxes")
        );
        $no_button = $this->ui_fac->button()->standard(
            $this->lng->txt("no"),
            $this->ctrl->getLinkTarget($this, "cancelResetBoxes")
        );
        $cbox = $this->ui_fac->messageBox()->confirmation($this->lng->txt("glo_boxes_really_reset"))
                             ->withButtons([$yes_button, $no_button]);
        $this->tpl->setContent($this->ui_ren->render($cbox));
    }

    public function cancelResetBoxes(): void
    {
        $this->ctrl->redirectByClass("ilglossarypresentationgui", "showFlashcards");
    }

    public function resetBoxes(): void
    {
        $this->flashcard_manager->resetEntries();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("glo_boxes_reset"), true);
        $this->ctrl->redirectByClass("ilglossarypresentationgui", "showFlashcards");
    }
}
