<?php

declare(strict_types=1);

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

namespace ILIAS\Wiki;

use ILIAS\Wiki\Page\Page;

/**
 * Wiki internal data service
 */
class InternalDataService
{
    public function __construct()
    {
    }

    public function page(
        int $id,
        int $wiki_id,
        string $title,
        string $lang = "-",
        bool $blocked = false,
        bool $rating = false,
        bool $hide_adv_md = false
    ) : Page
    {
        return new Page(
            $id,
            $wiki_id,
            $title,
            $lang,
            $blocked,
            $rating,
            $hide_adv_md
        );
    }
}
