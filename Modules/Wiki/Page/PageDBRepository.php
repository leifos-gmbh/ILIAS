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

namespace ILIAS\Wiki\Page;

use ILIAS\Wiki\InternalDataService;

/**
 * Wiki page repo
 */
class PageDBRepository
{
    protected InternalDataService $data;
    protected \ilDBInterface $db;

    public function __construct(
        InternalDataService $data,
        \ilDBInterface $db
    )
    {
        $this->data = $data;
        $this->db = $db;
    }

    /**
     * @return iterable<Page>
     */
    public function getWikiPages(int $wiki_id, string $lang = "-") : \Iterator
    {
        $set = $this->db->queryF("SELECT * FROM il_wiki_page  " .
            " WHERE lang = %s AND wiki_id = %s ORDER BY title",
            ["string", "integer"],
            [$lang, $wiki_id]
        );
        while ($rec = $this->db->fetchAssoc($set)) {
            yield $this->data->page(
                (int) $rec["id"],
                (int) $rec["wiki_id"],
                $rec["title"],
                $rec["lang"],
                (bool) $rec["blocked"],
                (bool) $rec["rating"],
                (bool) $rec["hide_adv_md"]
            );
        }
    }
}
