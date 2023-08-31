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

class ItemBuilderUI
{
    protected PropertyAndActionBuilderUI $prop_builder;
    protected \ILIAS\UI\Factory $ui_factory;

    public function __construct(
        PropertyAndActionBuilderUI $prop_builder,
        \ILIAS\UI\Factory $ui_factory
    )
    {
        $this->ui_factory = $ui_factory;
        $this->prop_builder = $prop_builder;
    }

    protected function addPropertyToItemProperties(array &$props, ?array $prop) : void
    {
        if ($prop) {
            $props[$prop["prop"]] = $prop["val"];
        }
    }

    public function getItem(Assignment $ass, int $user_id) : \ILIAS\UI\Component\Item\Standard
    {
        $pb = $this->prop_builder;
        $pb->build($ass, $user_id);

        $props = [];
        $this->addPropertyToItemProperties($props, $pb->getHeadProperty($pb::PROP_DEADLINE));
        $this->addPropertyToItemProperties($props, $pb->getHeadProperty($pb::PROP_REQUIREMENT));
        foreach ($pb->getAdditionalHeadProperties() as $p) {
            $this->addPropertyToItemProperties($props, $p);
        }

        $item =  $this->ui_factory->item()->standard(
            $ass->getTitle()
        )->withProperties($props)->withLeadText($pb->getLeadText() . " ");
        return $item;
    }


}