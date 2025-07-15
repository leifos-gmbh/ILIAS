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

namespace ILIAS\Help\GuidedTour;

use ILIAS\DI\Container;
use ILIAS\Repository\GlobalDICDomainServices;
use ILIAS\Help\GuidedTour\Settings\SettingsManager;
use ILIAS\Help\GuidedTour\Tour\TourManager;

class InternalDomainService
{
    use GlobalDICDomainServices;

    static protected array $instance = [];

    public function __construct(
        Container $DIC,
        protected InternalRepoService $repo,
        protected InternalDataService $data
    ) {
        $this->initDomainServices($DIC);
    }

    public function tourSettings(): SettingsManager
    {
        return self::$instance["settings"] ??= new SettingsManager(
            $this->data,
            $this->repo
        );
    }

    public function tour(): TourManager
    {
        return self::$instance["tour"] ??= new TourManager(
            $this->data,
            $this
        );
    }

}
