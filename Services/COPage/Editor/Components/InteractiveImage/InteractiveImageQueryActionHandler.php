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


use ILIAS\DI\Exceptions\Exception;
use ILIAS\COPage\Editor\Server;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class InteractiveImageQueryActionHandler implements Server\QueryActionHandler
{
    protected string $pc_id = "";
    protected \ILIAS\DI\UIServices $ui;
    protected \ilLanguage $lng;
    protected \ilPageObjectGUI $page_gui;
    protected \ilObjUser $user;
    protected Server\UIWrapper $ui_wrapper;
    protected \ilCtrl $ctrl;
    protected \ilComponentFactory $component_factory;

    public function __construct(\ilPageObjectGUI $page_gui, string $pc_id = "")
    {
        global $DIC;

        $this->ui = $DIC->ui();
        $this->lng = $DIC->language();
        $this->page_gui = $page_gui;
        $this->user = $DIC->user();
        $this->ctrl = $DIC->ctrl();
        $this->component_factory = $DIC["component.factory"];
        $this->pc_id = $pc_id;

        $this->ui_wrapper = new Server\UIWrapper($this->ui, $this->lng);
    }

    /**
     * @throws Exception
     */
    public function handle(array $query): Server\Response
    {
        switch ($query["action"]) {
            case "init":
                return $this->init();

        }
        throw new Exception("Unknown action " . $query["action"]);
    }

    protected function allCommand(): Server\Response
    {
        $ctrl = $this->ctrl;
        $f = $this->ui->factory();
        $dd = $f->dropdown()->standard([
            $f->link()->standard("label", "#")
        ]);
        $r = $this->ui->renderer();
        $o = new \stdClass();
        $o->dropdown = $r->render($dd);
        $o->iim_model = $this->getIIMModel();
        /*
        $o->errorMessage = $this->getErrorMessage();
        $o->errorModalMessage = $this->getErrorModalMessage();
        $o->pcModel = $this->getPCModel();
        $o->pcDefinition = $this->getComponentsDefinitions();
        $o->modal = $this->getModalTemplate();
        $o->confirmation = $this->getConfirmationTemplate();
        $o->autoSaveInterval = $this->getAutoSaveInterval();
        $o->backUrl = $ctrl->getLinkTarget($this->page_gui, "edit");
        $o->loaderUrl = \ilUtil::getImagePath("loader.svg");*/

        return new Server\Response($o);
    }


    /**
     * Get interactive image model
     */
    protected function getIIMModel(): ?stdClass
    {
        if ($this->pc_id !== "") {
            $pc = $this->page_gui->getPageObject()->getContentObjectForPcId($this->pc_id);
            return $pc->getIIMModel();
        }
        return null;
    }

    protected function componentEditFormResponse(array $query): Server\Response
    {
        $pc_edit = \ilCOPagePCDef::getPCEditorInstanceByName($query["cname"]);
        $form = "";
        if (!is_null($pc_edit)) {
            $form = $pc_edit->getEditComponentForm(
                $this->ui_wrapper,
                $this->page_gui->getPageObject()->getParentType(),
                $this->page_gui,
                $this->page_gui->getStyleId(),
                $query["pcid"]
            );
        }
        $o = new \stdClass();
        $o->editForm = $form;
        return new Server\Response($o);
    }

    /**
     * Get components ui elements
     */
    protected function getComponentsEditorUI(): array
    {
        $ui = [];
        $config = $this->page_gui->getPageConfig();
        foreach (\ilCOPagePCDef::getPCDefinitions() as $def) {
            $pc_edit = \ilCOPagePCDef::getPCEditorInstanceByName($def["name"]);
            if ($config->getEnablePCType($def["name"])) {
                if (!is_null($pc_edit)) {
                    $ui[$def["name"]] = $pc_edit->getEditorElements(
                        $this->ui_wrapper,
                        $this->page_gui->getPageObject()->getParentType(),
                        $this->page_gui,
                        $this->page_gui->getStyleId()
                    );
                }
            }
        }
        return $ui;
    }

    protected function getComponentsDefinitions(): array
    {
        $pcdef = [];
        foreach (\ilCOPagePCDef::getPCDefinitions() as $def) {
            $pcdef["types"][$def["name"]] = $def["pc_type"];
            $pcdef["names"][$def["pc_type"]] = $def["name"];
            $pcdef["txt"][$def["pc_type"]] = $this->lng->txt("cont_" . "pc_" . $def["pc_type"]);
        }
        return $pcdef;
    }

    public function getModalTemplate(): array
    {
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
    public function getConfirmationTemplate(): string
    {
        $ui = $this->ui;

        $confirmation = $ui->factory()->messageBox()->confirmation("#text#");

        return $ui->renderer()->renderAsync($confirmation);
    }

    /**
     * Get auto save interval
     */
    protected function getAutoSaveInterval(): int
    {
        $aset = new \ilSetting("adve");
        return (int) $aset->get("autosave");
    }
}
