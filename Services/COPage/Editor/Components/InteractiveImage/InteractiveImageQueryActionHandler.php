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

namespace ILIAS\COPage\Editor\Components\InteractiveImage;

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

    protected function init(): Server\Response
    {
        $ctrl = $this->ctrl;
        $f = $this->ui->factory();
        $dd = $f->dropdown()->standard([
            $f->link()->standard("label", "#")
        ]);
        $r = $this->ui->renderer();
        $o = new \stdClass();
        $o->uiModel = new \stdClass();
        $o->uiModel->dropdown = $r->render($dd);
        $o->uiModel->mainSlate = $this->getMainSlate();
        $o->uiModel->backgroundImage = $this->getBackgroundImage();

        $o->iimModel = $this->getIIMModel();
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
    protected function getIIMModel(): ?\stdClass
    {
        if ($this->pc_id !== "") {
            $pc = $this->page_gui->getPageObject()->getContentObjectForPcId($this->pc_id);
            return $pc->getIIMModel();
        }
        return null;
    }


    public function getMainSlate(): string
    {
        $lng = $this->lng;

        $tpl = new \ilTemplate("tpl.main_slate.html", true, true, "Services/COPage/PC/InteractiveImage");
        $tpl->setVariable("TITLE", $lng->txt("cont_iim_edit"));
        $tpl->setVariable("HEAD_TRIGGER", $lng->txt("cont_iim_trigger"));
        $tpl->setVariable("HEAD_SETTINGS", $lng->txt("cont_iim_settings"));
        $tpl->setVariable("HEAD_OVERVIEW", $lng->txt("cont_iim_overview"));

        $tpl->setVariable(
            "CLOSE_BUTTON",
            $this->ui_wrapper->getRenderedButton(
                $lng->txt("cont_iim_finish_editing"),
                "button",
                "component.back",
                null,
                "InteractiveImage",
                true
            )
        );

        $b = $this->ui_wrapper->getButton(
            $lng->txt("cont_iim_add_trigger"),
            "button",
            "add.trigger"
        );

        $tpl->setVariable(
            "MSBOX_TRIGGER",
            $this->ui_wrapper->getRenderedInfoBox(
                $lng->txt("cont_iim_add_trigger_text"),
                [$b]
            )
        );

        $tpl->setVariable(
            "LINK_SETTINGS",
            $this->ui_wrapper->getRenderedLink(
                $lng->txt("cont_iim_background_image_and_caption"),
                "InteractiveImage",
                "link",
                "switch.settings",
                null
            )
        );

        $tpl->setVariable(
            "LINK_OVERLAY",
            $this->ui_wrapper->getRenderedLink(
                $lng->txt("cont_iim_overlays"),
                "InteractiveImage",
                "link",
                "switch.overlays",
                null
            )
        );

        $tpl->setVariable(
            "LINK_POPUPS",
            $this->ui_wrapper->getRenderedLink(
                $lng->txt("cont_iim_popups"),
                "InteractiveImage",
                "link",
                "switch.popups",
                null
            )
        );

        return $tpl->get();
    }

    public function getBackgroundImage(
    ): string {

        if ($this->pc_id !== "") {
            /** @var \ilPCInteractiveImage $pc */
            $pc = $this->page_gui->getPageObject()->getContentObjectForPcId($this->pc_id);
        } else {
            return "";
        }

        $mob = $pc->getMediaObject();

        //$ilCtrl = $this->ctrl;

        $st_item = $mob->getMediaItem("Standard");

        // output image map
        $xml = "<dummy>";
        $xml .= $mob->getXML(IL_MODE_ALIAS);
        $xml .= $mob->getXML(IL_MODE_OUTPUT);
        $xml .= "</dummy>";
        $xsl = file_get_contents("./Services/COPage/xsl/page.xsl");
        //echo htmlentities($xml); exit;
        $args = array( '/_xml' => $xml, '/_xsl' => $xsl );
        $xh = xslt_create();
        $wb_path = \ilFileUtils::getWebspaceDir("output") . "/";
        $mode = "media";
        //echo htmlentities($ilCtrl->getLinkTarget($this, "showImageMap"));

        $random = new \ilRandom();
        $params = array(
                        'media_mode' => 'enable',
                        'pg_frame' => "",
                        'enlarge_path' => \ilUtil::getImagePath("enlarge.svg"),
                        'webspace_path' => $wb_path);
        $output = xslt_process($xh, "arg:/_xml", "arg:/_xsl", null, $args, $params);
        xslt_error($xh);
        xslt_free($xh);

        return $output;
    }

}
