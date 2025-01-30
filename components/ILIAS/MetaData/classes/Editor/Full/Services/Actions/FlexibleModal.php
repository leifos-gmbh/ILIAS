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

namespace ILIAS\MetaData\Editor\Full\Services\Actions;

use ILIAS\UI\Component\Prompt\Prompt;
use ILIAS\UI\Component\Modal\Modal;

class FlexibleModal
{
    protected Modal|Prompt|null $component = null;
    protected FlexibleSignal $flexible_signal;

    public function __construct(Modal|Prompt|string $component_or_link)
    {
        if (is_string($component_or_link)) {
            $this->flexible_signal = new FlexibleSignal(
                $component_or_link
            );
        } else {
            $this->component = $component_or_link;
            $this->flexible_signal = new FlexibleSignal(
                $this->component->getShowSignal()
            );
        }
    }

    public function getComponent(): Modal|Prompt|null
    {
        return $this->component;
    }

    public function getFlexibleSignal(): FlexibleSignal
    {
        return $this->flexible_signal;
    }
}
