<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Style\Content;
use \ILIAS\Style;
use \ILIAS\Style\Content\Access;

/**
 * TableGUI class for characteristics
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class CharacteristicTableGUI extends \ilTable2GUI
{
    /**
     * @var \ilCtrl
     */
    protected $ctrl;

    /**
     * @var \ilAccessHandler
     */
    protected $access;

    /**
     * @var \ilRbacSystem
     */
    protected $rbacsystem;

    /**
     * @var Style\Content\CharacteristicManager
     */
    protected $manager;

    /**
     * @var Access\StyleAccessManager
     */
    protected $access_manager;

    /**
     * @var \ilObjStyleSheet
     */
    protected $style;

    /**
     * @var string
     */
    protected $super_type;

    /**
     * @var bool
     */
    protected $hideable;

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * @var int
     */
    protected $order_cnt = 0;

    /**
    * Constructor
    */
    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        array $a_chars,
        string $a_super_type,
        \ilObjStyleSheet $a_style,
        Style\Content\CharacteristicManager $manager,
        Access\StyleAccessManager $access_manager)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->rbacsystem = $DIC->rbac()->system();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $this->manager = $manager;
        $this->access_manager = $access_manager;
        $this->ui = $DIC->ui();
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setExternalSorting(true);
        $this->super_type = $a_super_type;
        $this->style = $a_style;
        $all_super_types = \ilObjStyleSheet::_getStyleSuperTypes();
        $this->types = $all_super_types[$this->super_type];
        $this->core_styles = \ilObjStyleSheet::_getCoreStyles();
        $this->getItems();
        $this->setTitle($lng->txt("sty_" . $a_super_type . "_char"));
        $this->setLimit(9999);

        // check, whether any of the types is expandable
        $this->expandable = false;
        $this->hideable = false;
        foreach ($this->types as $t) {
            if (\ilObjStyleSheet::_isExpandable($t)) {
                $this->expandable = true;
            }
            if (\ilObjStyleSheet::_isHideable($t)) {
                $this->hideable = true;
            }
        }

        $this->addColumn("", "", "1");	// checkbox
        if ($this->expandable) {
            $this->addColumn($this->lng->txt("sty_order"));
        }
        $this->addColumn($this->lng->txt("sty_class_name"));
        $this->addColumn($this->lng->txt("title"));
        $this->addColumn($this->lng->txt("sty_type"));
        $this->addColumn($this->lng->txt("sty_example"));
        if ($this->hideable) {
            $this->addColumn($this->lng->txt("sty_hide"));	// hide checkbox
        }
        $this->addColumn($this->lng->txt("sty_outdated"));
        $this->addColumn($this->lng->txt("actions"));
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.style_row.html", "Services/Style/Content/Characteristic");
        $this->disable("footer");

        if ($this->access_manager->checkWrite()) {
            // action commands
            if ($this->hideable || $this->expandable) {
                $txt = $lng->txt("sty_save_hide_status");
                if ($this->hideable && $this->expandable) {
                    $txt = $lng->txt("sty_save_hide_order_status");
                } else if (!$this->hideable) {
                    $txt = $lng->txt("sty_save_order_status");
                }

                $this->addCommandButton("saveStatus", $txt);
            }
    
            $this->addMultiCommand("copyCharacteristics", $lng->txt("copy"));
            $this->addMultiCommand("setOutdated", $lng->txt("sty_set_outdated"));
            $this->addMultiCommand("removeOutdated", $lng->txt("sty_remove_outdated"));

            // action commands
            if ($this->expandable) {
                $this->addMultiCommand("deleteCharacteristicConfirmation", $lng->txt("delete"));
                //$this->addCommandButton("addCharacteristicForm", $lng->txt("sty_add_characteristic"));
            }
        }
        
        $this->setEnableTitle(true);
    }

    /**
     * Get items
     * @param
     * @return
     */
    protected function getItems()
    {
        $data = [];
        foreach ($this->manager->getBySuperType($this->super_type) as $char) {
            $data[] = [
                "obj" => $char
            ];
        }
        $this->setData($data);
    }

    /**
    * Standard Version of Fill Row. Most likely to
    * be overwritten by derived class.
    */
    protected function fillRow($a_set)
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ui = $this->ui;

        $char = $a_set["obj"];

        if ($this->expandable) {
            $this->order_cnt = $this->order_cnt + 10;
            $this->tpl->setCurrentBlock("order");
            $this->tpl->setVariable("OCHAR", $char->getType() . "." .
                \ilObjStyleSheet::_determineTag($char->getType()) .
                "." . $char->getCharacteristic());
            $this->tpl->setVariable("ORDER", $this->order_cnt);
            $this->tpl->parseCurrentBlock();
        }


        $this->tpl->setCurrentBlock("checkbox");
        $this->tpl->setVariable("CHAR", $char->getType() . "." .
            \ilObjStyleSheet::_determineTag($char->getType()) .
                    "." . $char->getCharacteristic());
        $this->tpl->parseCurrentBlock();

        if ($this->hideable) {
            if (!\ilObjStyleSheet::_isHideable($char->getType()) ||
                (!empty($this->core_styles[$char->getType() . "." .
                \ilObjStyleSheet::_determineTag($char->getType()) .
                "." . $char->getCharacteristic()]))) {
                $this->tpl->touchBlock("no_hide_checkbox");
            } else {
                $this->tpl->setCurrentBlock("hide_checkbox");
                $this->tpl->setVariable("CHAR", $char->getType() . "." .
                    \ilObjStyleSheet::_determineTag($char->getType()) .
                    "." . $char->getCharacteristic());
                if ($this->style->getHideStatus($char->getType(), $char->getCharacteristic())) {
                    $this->tpl->setVariable("CHECKED", "checked='checked'");
                }
                $this->tpl->parseCurrentBlock();
            }
        }

        // example
        $this->tpl->setVariable(
            "EXAMPLE",
            \ilObjStyleSheetGUI::getStyleExampleHTML($char->getType(), $char->getCharacteristic())
        );
        $tag_str = \ilObjStyleSheet::_determineTag($char->getType()) . "." . $char->getCharacteristic();
        $this->tpl->setVariable("TXT_TAG", $char->getCharacteristic());
        $this->tpl->setVariable("TXT_TYPE", $lng->txt("sty_type_" . $char->getType()));

        $this->tpl->setVariable("TITLE", $this->manager->getPresentationTitle(
            $char->getType(),
            $char->getCharacteristic(),
            false
        ));

        if ($this->access_manager->checkWrite()) {

            $ilCtrl->setParameter($this->parent_obj, "tag", $tag_str);
            $ilCtrl->setParameter($this->parent_obj, "style_type", $char->getType());
            $ilCtrl->setParameter($this->parent_obj, "char", $char->getCharacteristic());

            $links = [];
            $links[] = $ui->factory()->link()->standard(
                $this->lng->txt("edit"),
                $ilCtrl->getLinkTargetByClass("ilStyleCharacteristicGUI", "editTagStyle")
            );

            if (!\ilObjStyleSheet::isCoreStyle($char->getType(), $char->getCharacteristic())) {
                if ($char->isOutdated()) {
                    $this->tpl->setVariable("OUTDATED", $lng->txt("yes"));
                    $links[] = $ui->factory()->link()->standard(
                        $this->lng->txt("sty_remove_outdated"),
                        $ilCtrl->getLinkTargetByClass("ilStyleCharacteristicGUI", "removeOutdated")
                    );
                } else {
                    $this->tpl->setVariable("OUTDATED", $lng->txt("no"));
                    $links[] = $ui->factory()->link()->standard(
                        $this->lng->txt("sty_set_outdated"),
                        $ilCtrl->getLinkTargetByClass("ilStyleCharacteristicGUI", "setOutdated")
                    );
                }
            }

            $dd = $ui->factory()->dropdown()->standard($links);

            $this->tpl->setVariable("ACTIONS",
                $ui->renderer()->render($dd)
            );
        }
    }
}
