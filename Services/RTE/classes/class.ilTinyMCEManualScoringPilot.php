<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTinyMCEManualScoringPilot
 * @author      Björn Heyser <info@bjoernheyser.de>
 */
class ilTinyMCEManualScoringPilot extends ilTinyMCE
{
    /**
     * {@inheritdoc}
     */
    public function addRTESupport($obj_id, $obj_type, $a_module = "", $allowFormElements = false, $cfg_template = null, $hide_switch = false)
    {
        if ($this->browser->isMobile()) {
            ilObjAdvancedEditing::_setRichTextEditorUserState(0);
        } else {
            ilObjAdvancedEditing::_setRichTextEditorUserState(1);
        }

        include_once "./Services/UICore/classes/class.ilTemplate.php";
        if ((ilObjAdvancedEditing::_getRichTextEditorUserState() != 0) && (strcmp(ilObjAdvancedEditing::_getRichTextEditor(), "0") != 0)) {
            $tpl = new ilTemplate(($cfg_template === null ? "tpl.tinymce_manual_scoring_pilot.html" : $cfg_template), true, true, "Services/RTE");
            $this->handleImgContextMenuItem($tpl);
            $tags = ilObjAdvancedEditing::_getUsedHTMLTags($a_module);
            $this->handleImagePluginsBeforeRendering($tags);
            if ($allowFormElements) {
                $tpl->touchBlock("formelements");
            }
            if ($this->getInitialWidth() !== null && $tpl->blockExists('initial_width')) {
                $tpl->setCurrentBlock("initial_width");
                $tpl->setVariable('INITIAL_WIDTH', $this->getInitialWidth());
                $tpl->parseCurrentBlock();
            }
            $tpl->setCurrentBlock("tinymce");
            $tpl->setVariable("JAVASCRIPT_LOCATION", "./Services/RTE/tiny_mce" . $this->vd . "/tiny_mce.js");
            include_once "./Services/Object/classes/class.ilObject.php";
            $tpl->setVariable("OBJ_ID", $obj_id);
            $tpl->setVariable("OBJ_TYPE", $obj_type);
            $tpl->setVariable("CLIENT_ID", CLIENT_ID);
            $tpl->setVariable("SESSION_ID", $_COOKIE[session_name()]);
            $tpl->setVariable("BLOCKFORMATS", $this->_buildAdvancedBlockformatsFromHTMLTags($tags));
            $tpl->setVariable("VALID_ELEMENTS", $this->_getValidElementsFromHTMLTags($tags));

            $buttons_1 = $this->_buildAdvancedButtonsFromHTMLTags(1, $tags);
            $buttons_2 = $this->_buildAdvancedButtonsFromHTMLTags(2, $tags)
                . ',' . $this->_buildAdvancedTableButtonsFromHTMLTags($tags)
                . ($this->getStyleSelect() ? ',styleselect' : '');
            $buttons_3 = $this->_buildAdvancedButtonsFromHTMLTags(3, $tags);
            $tpl->setVariable('BUTTONS_1', self::removeRedundantSeparators($buttons_1));
            $tpl->setVariable('BUTTONS_2', self::removeRedundantSeparators($buttons_2));
            $tpl->setVariable('BUTTONS_3', self::removeRedundantSeparators($buttons_3));

            $tpl->setVariable("ADDITIONAL_PLUGINS", join(",", $this->plugins));
            include_once "./Services/Utilities/classes/class.ilUtil.php";
            //$tpl->setVariable("STYLESHEET_LOCATION", $this->getContentCSS());
            $tpl->setVariable("STYLESHEET_LOCATION", ilUtil::getNewContentStyleSheetLocation() . "," . ilUtil::getStyleSheetLocation("output", "delos.css"));
            $tpl->setVariable("LANG", $this->_getEditorLanguage());

            if ($this->getRTERootBlockElement() !== null) {
                $tpl->setVariable('FORCED_ROOT_BLOCK', $this->getRTERootBlockElement());
            }

            $tpl->parseCurrentBlock();

            $this->tpl->setVariable("CONTENT_BLOCK", $tpl->get());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addUserTextEditor($editor_selector)
    {
        $validtags = array("strong","em","p", "br", "div", "span");
        $buttontags = array("strong","em");
        include_once "./Services/UICore/classes/class.ilTemplate.php";
        $template = new ilTemplate("tpl.usereditor_manual_scoring_pilot.html", true, true, "Services/RTE");
        $this->handleImgContextMenuItem($template);
        $template->setCurrentBlock("tinymce");
        $template->setVariable("JAVASCRIPT_LOCATION", "./Services/RTE/tiny_mce" . $this->vd . "/tiny_mce.js");
        include_once "./Services/Object/classes/class.ilObject.php";
        $template->setVariable("SELECTOR", $editor_selector);
        $template->setVariable("BLOCKFORMATS", "");
        $template->setVariable("VALID_ELEMENTS", $this->_getValidElementsFromHTMLTags($validtags));
        if ($this->getStyleSelect()) {
            $template->setVariable("STYLE_SELECT", ",styleselect");
        }
        $template->setVariable("BUTTONS", $this->getButtonsForUserTextEditor($buttontags) . ",backcolor,removeformat");
        include_once "./Services/Utilities/classes/class.ilUtil.php";
        //$template->setVariable("STYLESHEET_LOCATION", $this->getContentCSS());
        $template->setVariable("STYLESHEET_LOCATION", ilUtil::getNewContentStyleSheetLocation() . "," . ilUtil::getStyleSheetLocation("output", "delos.css"));
        $template->setVariable("LANG", $this->_getEditorLanguage());
        $template->parseCurrentBlock();
        $this->tpl->setCurrentBlock("HeadContent");
        $this->tpl->setVariable("CONTENT_BLOCK", $template->get());
        $this->tpl->parseCurrentBlock();
    }
}
