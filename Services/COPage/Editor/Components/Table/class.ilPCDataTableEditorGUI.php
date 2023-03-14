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

use ILIAS\COPage\Editor\Server\UIWrapper;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCDataTableEditorGUI implements \ILIAS\COPage\Editor\Components\PageComponentEditor
{
    protected \ILIAS\DI\UIServices $ui;
    protected \ilLanguage $lng;
    protected \ilCtrl $ctrl;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->ui = $DIC->ui();
    }

    public function getEditorElements(
        UIWrapper $ui_wrapper,
        string $page_type,
        ilPageObjectGUI $page_gui,
        int $style_id
    ): array {
        global $DIC;

        $lng = $DIC->language();
        $lng->loadLanguageModule("content");

        $acc = new ilAccordionGUI();
        $acc->addItem($lng->txt("cont_set_manuall"), $this->getCreationForm($page_gui, $ui_wrapper, $style_id));
        $acc->addItem($lng->txt("cont_paste_from_spreadsheet"), $this->getImportForm($page_gui, $ui_wrapper, $style_id));
        $acc->setBehaviour(ilAccordionGUI::FIRST_OPEN);

        $form = $this->getCreationForm($page_gui, $ui_wrapper, $style_id);
        return [
            "creation_form" => $acc->getHTML(true),
            "icon" => $ui_wrapper->getRenderedIcon("pedt"),
            "top_actions" => $this->getTopActions($ui_wrapper, $page_gui)
        ];
    }

    protected function getCreationForm(
        ilPageObjectGUI $page_gui,
        UIWrapper $ui_wrapper,
        int $style_id
    ): string {
        $lng = $this->lng;

        $tab_gui = new ilPCDataTableGUI($page_gui->getPageObject(), null, "", "");
        $tab_gui->setStyleId($style_id);

        /** @var ilPropertyFormGUI $form */
        $form = $tab_gui->initCreationForm();

        $html = $ui_wrapper->getRenderedForm(
            $form,
            [
                ["Page", "component.save", $lng->txt("insert")],
                ["Page", "component.cancel", $lng->txt("cancel")]
            ]
        );

        return $html;
    }


    protected function getImportForm(
        ilPageObjectGUI $page_gui,
        UIWrapper $ui_wrapper,
        int $style_id
    ): string {
        $lng = $this->lng;

        $tab_gui = new ilPCDataTableGUI($page_gui->getPageObject(), null, "", "");
        $tab_gui->setStyleId($style_id);

        /** @var ilPropertyFormGUI $form */
        $form = $tab_gui->initImportForm();

        $html = $ui_wrapper->getRenderedForm(
            $form,
            [
                ["Page", "component.save", $lng->txt("insert")],
                ["Page", "component.cancel", $lng->txt("cancel")]
            ]
        );

        return $html;
    }

    public function getEditComponentForm(
        UIWrapper $ui_wrapper,
        string $page_type,
        \ilPageObjectGUI $page_gui,
        int $style_id,
        string $pcid
    ): string {
        global $DIC;

        $lng = $DIC->language();
        $lng->loadLanguageModule("content");

        /** @var ilPCResources $pc_res */
        $pc_res = $page_gui->getPageObject()->getContentObjectForPcId($pcid);
        $res_gui = new ilPCResourcesGUI($page_gui->getPageObject(), $pc_res, "", $pcid);

        /** @var ilPropertyFormGUI $form */
        $form = $res_gui->initEditingForm();

        $html = $ui_wrapper->getRenderedForm(
            $form,
            [["Page", "component.update", $lng->txt("save")],
             ["Page", "component.cancel", $lng->txt("cancel")]]
        );

        return $html;
    }

    protected function getTopActions(UIWrapper $ui_wrapper, ilPageObjectGUI $page_gui): string
    {
        $ui = $this->ui;
        $ctrl = $this->ctrl;

        $lng = $this->lng;
        $lng->loadLanguageModule("content");
        $tpl = new \ilTemplate("tpl.table_top_actions.html", true, true, "Services/COPage/Editor");

        $b = $ui->factory()->button()->standard(
            $lng->txt("cont_table_editing"),
            $ctrl->getLinkTarget($page_gui, "edit")
        );
        $tpl->setVariable("QUIT_BUTTON", $ui->renderer()->renderAsync($b));

        $html = $ui_wrapper->getRenderedViewControl(
            [
                ["Table", "switch.edit.table", $lng->txt("cont_edit_table")],
                ["Table", "switch.format.cells", $lng->txt("cont_format_cells")]
            ]
        );
        $tpl->setVariable("SWITCH", $html);

        return $tpl->get();
    }

}
