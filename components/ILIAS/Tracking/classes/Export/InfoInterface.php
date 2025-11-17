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

namespace ILIAS\Tracking\Export;

use ILIAS\Tracking\DB\LPCollection\Element\LPCollectionInterface;
use ILIAS\Tracking\DB\LPSettings\Element\LPSettingsInterface;
use ILIAS\Tracking\Status\CollectionInterface as LPStatusCollectionInterface;

interface InfoInterface
{
    public function getLPStatusCollection(): LPStatusCollectionInterface;

    public function getLPSettings(): LPSettingsInterface;

    public function getLPCollection(): LPCollectionInterface;

    public function withLPStatusCollection(
        LPStatusCollectionInterface $lp_status_collection
    ): InfoInterface;

    public function withLPSettings(
        LPSettingsInterface $lp_settings
    ): InfoInterface;

    public function withLPCollection(
        LPCollectionInterface $lp_collection
    ): InfoInterface;
}
