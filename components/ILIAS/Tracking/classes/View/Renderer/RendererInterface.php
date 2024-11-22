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
use ILIAS\UI\Component\Item\Standard as UIStandardItem;
use ILIAS\UI\Component\Chart\ProgressMeter\Standard as UIStandardProgressMeter;

interface RendererInterface
{
    public function standardProgressMeter(
        LPInterface $lp_info
    ): UIStandardProgressMeter;

    public function standardItem(
        ObjectDataInterface $object_info,
        PropertyListInterface $property_list
    ): UIStandardItem;

    public function fixedSizeProgressMeter(
        LPInterface $lp_info
    ): UIStandardProgressMeter;
}
