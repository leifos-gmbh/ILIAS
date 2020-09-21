<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\COPage\Editor\Components\Page;

use ILIAS\DI\Exceptions\Exception;
use ILIAS\COPage\Editor\Server;
use ILIAS\COPage\Editor\Components\Paragraph\ParagraphStyleSelector;
use ILIAS\COPage\Editor\Components\Section\SectionStyleSelector;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class PageQueryActionHandler implements Server\QueryActionHandler
{
    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * @var \ilPageObjectGUI
     */
    protected $page_gui;

    /**
     * @var \ilObjUser
     */
    protected $user;

    /**
     * @var Server\UIWrapper
     */
    protected $ui_wrapper;

    function __construct(\ilPageObjectGUI $page_gui)
    {
        global $DIC;

        $this->ui = $DIC->ui();
        $this->lng = $DIC->language();
        $this->page_gui = $page_gui;
        $this->user = $DIC->user();

        $this->ui_wrapper = new Server\UIWrapper($this->ui, $this->lng);
    }

    /**
     * @param $query
     * @param $body
     * @return Server\Response
     */
    public function handle($query) : Server\Response
    {
        $action = explode(".", $query["action"]);
        if ($action[0] === "ui") {
            switch ($action[1]) {
                case "all":
                    return $this->allCommand();
                    break;
            }
        }
        throw new Exception("Unknown action " . $query["action"]);
    }

    /**
     * All command
     * @param
     * @return
     */
    protected function allCommand() : Server\Response
    {
        $f = $this->ui->factory();
        $dd = $f->dropdown()->standard([
            $f->link()->standard("label", "#")
        ]);
        $r = $this->ui->renderer();
        $o = new \stdClass();
        $o->addDropdown = $r->render($dd);
        $o->addCommands = $this->getAddCommands();
        $o->pageHelp = $this->getPageHelp();
        $o->multiActions = $this->getMultiActions();
        $o->cutConfirm = $this->getCutCopyConfirm("cut");
        $o->copyConfirm = $this->getCutCopyConfirm("copy");
        $o->config = $this->getConfig();
        $o->components = $this->getComponentsEditorUI();
        $o->pcModel = $this->getPCModel();
        $o->pcDefinition = $this->getComponentsDefinitions();
        $o->formatSelection = $this->getFormatSelection();
        $o->modal = $this->getModalTemplate();
        $o->confirmation = $this->getConfirmationTemplate();
        return new Server\Response($o);
    }

    /**
     * Get config
     * @return \stdClass
     */
    protected function getConfig()
    {
        $config = new \stdClass();
        $config->user = $this->user->getLogin();
        $config->content_css =
            \ilObjStyleSheet::getContentStylePath((int) $this->page_gui->getStyleId()).", ".
            \ilUtil::getStyleSheetLocation().", ".
            "./Services/COPage/css/tiny_extra.css";
        $config->text_formats = \ilPCParagraphGUI::_getTextCharacteristics($this->page_gui->getStyleId());

        return $config;
    }

    /**
     * Get add commands
     * @param
     * @return
     */
    protected function getAddCommands()
    {
        $lng = $this->lng;

        $commands = [];

        $config = $this->page_gui->getPageConfig();
        foreach ($config->getEnabledTopPCTypes() as $def) {
            $commands[$def["pc_type"]] = $lng->txt("cont_ed_insert_" . $def["pc_type"]);
        }
        return $commands;
    }

    /**
     * Get page help (drag drop explanation)
     * @param
     * @return
     */
    protected function getPageHelp()
    {
        // @todo remove legacy
        $lng = $this->lng;
        $lng->loadLanguageModule("content");
        $tpl = new \ilTemplate("tpl.editor_slate.html", true, true, "Services/COPage");
        $tpl->setCurrentBlock("help");
        $tpl->setVariable("TXT_ADD_EL", $lng->txt("cont_add_elements"));
        $tpl->setVariable("PLUS", \ilGlyphGUI::get(\ilGlyphGUI::ADD));
        $tpl->setVariable("DRAG_ARROW", \ilGlyphGUI::get(\ilGlyphGUI::DRAG));
        $tpl->setVariable("TXT_DRAG", $lng->txt("cont_drag_and_drop_elements"));
        $tpl->setVariable("TXT_SEL", $lng->txt("cont_double_click_to_delete"));
        $tpl->parseCurrentBlock();
        return $tpl->get();
    }

    /**
     * Get multi actions
     * @return string
     */
    protected function getMultiActions()
    {
        $groups = [
            [
                "cut" => "cut",
                "copy" => "copy",
                "delete" => "delete"
            ],
            [
                "all" => "select_all",
                "none" => "cont_select_none",
            ],
            [
                "activate" => "cont_ed_enable",
                "characteristic" => "cont_assign_characteristic"
            ]
        ];

        return $this->ui_wrapper->getRenderedButtonGroups($groups);
    }

    /**
     * Confirmation screen for cut/paste step
     * @return string
     */
    protected function getCutCopyConfirm($action)
    {
        $lng = $this->lng;

        $groups = [
            [
                "cancel.".$action => "cancel"
            ]
        ];
        $html = $this->ui_wrapper->getRenderedInfoBox($lng->txt("cont_sel_el_".$action."_use_paste")).
            $this->ui_wrapper->getRenderedButtonGroups($groups);

        return $html;
    }

    /**
     * Format selection
     */
    protected function getFormatSelection() {
        $lng = $this->lng;
        $ui = $this->ui;
        $tpl = new \ilTemplate("tpl.format_selection.html", true, true, "Services/COPage/Editor");
        $tpl->setVariable("TXT_PAR", $lng->txt("cont_choose_characteristic_text"));
        $tpl->setVariable("TXT_SECTION", $lng->txt("cont_choose_characteristic_section"));

        $par_sel = new ParagraphStyleSelector($this->ui_wrapper, (int) $this->page_gui->getStyleId());
        $tpl->setVariable("PAR_SELECTOR", $ui->renderer()->renderAsync($par_sel->getStyleSelector("", "format", "format.paragraph", "format")));

        $sec_sel = new SectionStyleSelector($this->ui_wrapper, (int) $this->page_gui->getStyleId());
        $tpl->setVariable("SEC_SELECTOR", $ui->renderer()->renderAsync($sec_sel->getStyleSelector("", "format", "format.section", "format")));

        $tpl->setVariable("SAVE_BUTTON", $this->ui_wrapper->getRenderedButton(
            $lng->txt("save"),  "format", "format.save")
        );
        return $tpl->get();
    }


    /**
     * Get page component model
     * @param
     * @return
     */
    protected function getPCModel()
    {
        return $this->page_gui->getPageObject()->getPCModel();
    }


    /**
     * Get components ui elements
     * @param
     * @return
     */
    protected function getComponentsEditorUI()
    {
        $ui = [];
        foreach (\ilCOPagePCDef::getPCDefinitions() as $def) {
            $pc_edit = \ilCOPagePCDef::getPCEditorInstanceByName($def["name"]);
            if (!is_null($pc_edit)) {
                $ui[$def["name"]] = $pc_edit->getEditorElements(
                    $this->ui_wrapper,
                    $this->page_gui->getPageObject()->getParentType(),
                    $this->page_gui->getPageConfig(),
                    $this->page_gui->getStyleId()
                );
            }
        }
        return $ui;
    }

    /**
     * Get components ui elements
     * @return array
     */
    protected function getComponentsDefinitions() {
        $pcdef = [];
        foreach (\ilCOPagePCDef::getPCDefinitions() as $def) {
            $pcdef["types"][$def["name"]] = $def["pc_type"];
            $pcdef["names"][$def["pc_type"]] = $def["name"];
        }
        return $pcdef;
    }

    /**
     * Get modal template
     */
    public function getModalTemplate() {
        $ui = $this->ui;
        $modal = $ui->factory()->modal()->roundtrip('#title#', $ui->factory()->legacy('#content#'))
                    ->withActionButtons([
                        $ui->factory()->button()->standard('#button_title#', '#'),
                    ]);
        $modalt["signal"] = $modal->getShowSignal()->getId();
        $modalt["template"] = $ui->renderer()->renderAsync($modal);

        return $modalt;
    }

    /**
     * Get confirmation template
     */
    public function getConfirmationTemplate() {
        $ui = $this->ui;

        $confirmation= $ui->factory()->messageBox()->confirmation("#text#");

        return $ui->renderer()->renderAsync($confirmation);
    }


}