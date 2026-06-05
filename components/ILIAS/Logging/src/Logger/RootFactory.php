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

namespace ILIAS\Logging\Logger;

use ILIAS\Logging\Config\ConfigInterface;

class RootFactory implements RootFactoryInterface
{
    public function __construct(
        protected InternalFactory $internal_factory,
        protected ConfigInterface $config
    ) {
    }

    public function get(string $component_id): LoggerInterface
    {
        return $this->internal_factory->get(
            $component_id,
            $this->config->getLevelForRootLogger()
        );
    }
}
