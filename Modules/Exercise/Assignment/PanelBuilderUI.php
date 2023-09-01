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

class PanelBuilderUI
{
    protected \ilLanguage $lng;
    protected PropertyAndActionBuilderUI $prop_builder;
    protected \ILIAS\UI\Factory $ui_factory;

    public function __construct(
        PropertyAndActionBuilderUI $prop_builder,
        \ILIAS\UI\Factory $ui_factory,
        \ilCtrl $ctrl,
        \ilLanguage $lng
    )
    {
        $this->ui_factory = $ui_factory;
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
        foreach ([$pb::SEC_INSTRUCTIONS] as $sec) {
            $title = "";
            switch ($sec) {
                case $pb::SEC_INSTRUCTIONS:
                    $title = $this->lng->txt("exc_instructions");
                    break;
            }
            $content = "";
            foreach ($pb->getProperties($sec) as $prop) {
                $content.= "<div>".$prop["prop"].": " .$prop["val"]."</div>";
            }
            $sub_panels[] = $this->ui_factory->panel()->sub(
                $title,
                $this->ui_factory->legacy($content)
            );
        }

        $panel = $this->ui_factory->panel()->standard(
            $ass->getTitle(),
            $sub_panels
        );

        return $panel;
    }


}