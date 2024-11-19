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

declare(strict_types=0);

namespace ILIAS\Tracking\View\Renderer;

use ILIAS\Tracking\View\DataRetrieval\Info\LPInterface;
use ILIAS\Tracking\View\DataRetrieval\Info\ObjectDataInterface;
use ILIAS\Tracking\View\PropertyList\PropertyListInterface;
use ILIAS\Tracking\View\Renderer\RendererInterface;
use ILIAS\UI\Component\Chart\ProgressMeter\Standard as UIStandardProgressMeter;
use ILIAS\UI\Component\Item\Standard as UIStandardItem;
use ILIAS\DI\UIServices;
use ILIAS\UI\Component\Symbol\Icon\Icon as UIIconIcon;
use ILIAS\UI\Component\Symbol\Icon\Standard as UIStandardIcon;

class Renderer implements RendererInterface
{
    public function __construct(
        protected UIServices $ui
    ) {
    }

    public function standardProgressMeter(
        LPInterface $lp_info
    ): UIStandardProgressMeter {
        return $this->ui->factory()->chart()->progressMeter()->standard(
            100,
            $lp_info->getPercentage()
        );
    }

    public function standardItem(
        ObjectDataInterface $object_info,
        PropertyListInterface $property_list
    ): UIStandardItem {
        $properties = [];
        foreach ($property_list as $property) {
            $properties[$property_list->key()] = $property;
        }
        $icon = $this->ui->factory()->symbol()->icon()->standard(
            $object_info->getType(),
            $object_info->getType() . " Icon",
            UIIconIcon::MEDIUM
        );
        return $this->ui->factory()->item()->standard($object_info->getTitle())
            ->withProperties($properties)
            ->withDescription($object_info->getDescription())
            ->withLeadIcon($icon);
    }
}
