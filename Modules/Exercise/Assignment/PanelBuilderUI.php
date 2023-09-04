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

namespace ILIAS\Exercise\Assignment;

use ILIAS\Exercise\Assignment\Mandatory\MandatoryAssignmentsManager;
use ILIAS\UI\Component\Component;
use function PHPUnit\Framework\isInstanceOf;
use ILIAS\UI\Component\Button\Button;
use ILIAS\UI\Component\Link\Link;

class PanelBuilderUI
{
    protected \ilLanguage $lng;
    protected PropertyAndActionBuilderUI $prop_builder;
    protected \ILIAS\UI\Factory $ui_factory;

    public function __construct(
        PropertyAndActionBuilderUI $prop_builder,
        \ILIAS\UI\Factory $ui_factory,
        \ILIAS\UI\Renderer $ui_renderer,
        \ilCtrl $ctrl,
        \ilLanguage $lng
    )
    {
        $this->ui_factory = $ui_factory;
        $this->ui_renderer = $ui_renderer;
        $this->prop_builder = $prop_builder;
        $this->ctrl = $ctrl;
        $this->lng = $lng;
    }

    protected function addPropertyToItemProperties(array &$props, ?array $prop) : void
    {
        if ($prop) {
            $props[$prop["prop"]] = $prop["val"];
        }
    }

    public function getPanel(Assignment $ass, int $user_id) : \ILIAS\UI\Component\Panel\Standard
    {
        $pb = $this->prop_builder;
        $pb->build($ass, $user_id);

        $sub_panels = [];
        foreach ([$pb::SEC_INSTRUCTIONS, $pb::SEC_FILES, $pb::SEC_SUBMISSION] as $sec) {
            $title = "";
            switch ($sec) {
                case $pb::SEC_INSTRUCTIONS:
                    $title = $this->lng->txt("exc_instructions");
                    break;
                case $pb::SEC_FILES:
                    $title = $this->lng->txt("exc_files");
                    break;
                case $pb::SEC_SUBMISSION:
                    $title = $this->lng->txt("exc_submission");
                    break;
            }
            $ctpl = new \ilTemplate("tpl.panel_content.html", true, true,
            "Modules/Exercise/Assignment");

            // properties
            foreach ($pb->getProperties($sec) as $prop) {
                $ctpl->setCurrentBlock("entry");
                $ctpl->setVariable("LABEL", $prop["prop"]);
                $ctpl->setVariable("VALUE", $prop["val"]);
                $ctpl->parseCurrentBlock();
            }

            // actions
            $this->renderActionButton($ctpl, $pb->getMainAction($sec));
            foreach ($pb->getActions($sec) as $action) {
                $this->renderActionButton($ctpl, $action);
            }

            // links
            $this->renderLinkList($ctpl, $sec);

            $sub_panels[] = $this->ui_factory->panel()->sub(
                $title,
                $this->ui_factory->legacy($ctpl->get())
            );
        }

        $panel = $this->ui_factory->panel()->standard(
            $ass->getTitle(),
            $sub_panels
        );

        return $panel;
    }

    protected function renderActionButton(\ilTemplate $tpl, ?Component $c) : void
    {
        if (!is_null($c) && $c instanceof Button) {
            $tpl->setCurrentBlock("action");
            $tpl->setVariable("BUTTON", $this->ui_renderer->render($c));
            $tpl->parseCurrentBlock();
        }
    }

    protected function renderLinkList(\ilTemplate $tpl, string $sec) : void
    {
        foreach ($this->prop_builder->getActions($sec) as $action) {
            if ($action instanceof Link) {
                $tpl->setCurrentBlock("link");
                $tpl->setVariable("LINK", $this->ui_renderer->render($action));
                $tpl->parseCurrentBlock();
            }
        }
    }

}