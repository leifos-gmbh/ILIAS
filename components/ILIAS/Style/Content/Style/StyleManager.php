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

namespace ILIAS\Style\Content\Style;

use ILIAS\Style\Content\InternalDataService;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\Style\Content\InternalRepoService;
use ILIAS\Style\Content\InternalDomainService;

class StyleManager
{
    protected StyleRepo $repo;
    public function __construct(
        protected InternalDataService $data,
        InternalRepoService $repo,
        protected InternalDomainService $domain,
        protected ResourceStakeholder $stakeholder,
        protected int $style_id
    ) {
        $this->repo = $repo->style();
    }

    public function writeCss() : void
    {
        $builder = $this->domain->cssBuilder(
            new \ilObjStyleSheet($this->style_id)
        );
        $css = $builder->getCss();
        $this->repo->writeCss(
            $this->style_id, $css, $this->stakeholder
        );
    }

    public function getPath(
        bool $add_random = true,
        bool $add_token = true
        ) : string
    {
        return $this->repo->getPath(
            $this->style_id,
            $add_random,
            $add_token
        );
    }
}
