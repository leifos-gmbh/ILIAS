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

namespace ILIAS\Help\Module;

use ILIAS\Help\InternalRepoService;
use ILIAS\Help\InternalDomainService;

class ModuleManager
{
    protected \ilSetting $settings;

    public function __construct(
        InternalDomainService $domain
    )
    {
        $this->settings = $domain->settings();
    }

    public function getHelpLMId(): int
    {
        $lm_id = 0;
        if ((int) OH_REF_ID > 0) {
            $lm_id = \ilObject::_lookupObjId((int) OH_REF_ID);
        } else {
            $hm = (int) $this->settings->get("help_module");
            if ($hm > 0) {
                $lm_id = \ilObjHelpSettings::lookupModuleLmId($hm);
            }
        }

        return $lm_id;
    }
}