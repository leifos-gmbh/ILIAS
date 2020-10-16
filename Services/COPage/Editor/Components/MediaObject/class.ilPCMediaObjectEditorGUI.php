<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCMediaObjectEditorGUI implements \ILIAS\COPage\Editor\Components\PageComponentEditor
{
    /**
     * @inheritDoc
     */
    function getEditorElements(\ILIAS\COPage\Editor\Server\UIWrapper $ui_wrapper, string $page_type, ilPageObjectGUI $page_gui, int $style_id): array {
        global $DIC;
        $lng = $DIC->language();
        $lng->loadLanguageModule("content");

        $acc = new ilAccordionGUI();
        $acc->addItem($lng->txt("cont_upload_file"), $this->getRenderedUploadForm($ui_wrapper, $lng));
        $acc->addItem($lng->txt("cont_add_url"), $this->getRenderedUrlForm($ui_wrapper, $lng));
        $acc->addItem($lng->txt("cont_choose_from_pool"), $this->getRenderedPoolLink($ui_wrapper, $lng));
        $acc->setBehaviour(ilAccordionGUI::FIRST_OPEN);

        return [
            "creation_form" => $acc->getHTML()
        ];
    }

    /**
     * Get upload form
     * @param
     * @return
     */
    protected function getRenderedUploadForm(\ILIAS\COPage\Editor\Server\UIWrapper $ui_wrapper, $lng)
    {
        $form = new ilPropertyFormGUI();
        $form->setShowTopButtons(false);

        // standard type
        $hi = new ilHiddenInputGUI("standard_type");
        $hi->setValue("File");
        $form->addItem($hi);

        // standard size
        $hi2 = new ilHiddenInputGUI("standard_size");
        $hi2->setValue("original");
        $form->addItem($hi2);

        // standard size
        $hi3 = new ilHiddenInputGUI("full_type");
        $hi3->setValue("None");
        $form->addItem($hi3);

        // standard file
        $up = new ilFileInputGUI($lng->txt("cont_file"), "standard_file");
        $up->setSuffixes(ilObjMediaObject::getRestrictedFileTypes());
        $up->setForbiddenSuffixes(ilObjMediaObject::getForbiddenFileTypes());
        $form->addItem($up);

        $html = $ui_wrapper->getRenderedForm(
            $form,
            [["Page", "component.save", $lng->txt("save")],
             ["Page", "component.cancel", $lng->txt("cancel")]]
        );

        return $html;
    }

    /**
     * Get upload form
     * @param
     * @return
     */
    protected function getRenderedUrlForm(\ILIAS\COPage\Editor\Server\UIWrapper $ui_wrapper, $lng)
    {
        $form = new ilPropertyFormGUI();
        $form->setShowTopButtons(false);

        // standard type
        $hi = new ilHiddenInputGUI("standard_type");
        $hi->setValue("Reference");
        $form->addItem($hi);

        // standard size
        $hi2 = new ilHiddenInputGUI("standard_size");
        $hi2->setValue("original");
        $form->addItem($hi2);

        // standard size
        $hi3 = new ilHiddenInputGUI("full_type");
        $hi3->setValue("None");
        $form->addItem($hi3);

        // standard reference
        $ti = new ilTextInputGUI($lng->txt("url"), "standard_reference");
        $ti->setInfo($lng->txt("cont_url_info"));
        $form->addItem($ti);


        $html = $ui_wrapper->getRenderedForm(
            $form,
            [["Page", "component.save", $lng->txt("save")],
             ["Page", "component.cancel", $lng->txt("cancel")]]
        );

        return $html;
    }

    /**
     * Get pool link
     * @param
     * @return
     */
    protected function getRenderedPoolLink(\ILIAS\COPage\Editor\Server\UIWrapper $ui_wrapper, $lng)
    {
        global $DIC;

        $ui = $DIC->ui();
        $ctrl = $DIC->ctrl();
        $lng = $DIC->language();

        $ctrl->setParameterByClass("ilpcmediaobjectgui", "subCmd", "poolSelection");
        $l = $ui_wrapper->getRenderedLink($lng->txt("cont_choose_media_pool"), "media-action", "select.pool",
        ["url" => $ctrl->getLinkTargetByClass("ilpcmediaobjectgui", "insert")]);
        $ctrl->setParameterByClass("ilpcmediaobjectgui", "subCmd", "poolSelection");

        return $l;
        //http://scorsese.local/ilias_6/ilias.php?ref_id=86&obj_id=3&active_node=3&hier_id=pg&subCmd=poolSelection&cmd=insert&cmdClass=ilpcmediaobjectgui&cmdNode=ow:oi:o3:o6:ek:ec&baseClass=ilLMEditorGUI
    }

}