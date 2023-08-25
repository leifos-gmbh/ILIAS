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

    protected function getPageFromRecord($rec) : Page
    {
        return $this->data->page(
            (int) $rec["id"],
            (int) $rec["wiki_id"],
            $rec["title"],
            $rec["lang"],
            (bool) $rec["blocked"],
            (bool) $rec["rating"],
            (bool) $rec["hide_adv_md"]
        );
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
            yield $this->getPageFromRecord($rec);
        }
    }

    public function getMasterPagesWithoutTranslation(int $wiki_id, string $trans) : \Iterator
    {
        $set = $this->db->queryF("SELECT w1.* FROM il_wiki_page w1 LEFT JOIN il_wiki_page w2 " .
            " ON w1.id = w2.id AND w2.lang = %s " .
            " WHERE w1.lang = %s AND w1.wiki_id = %s AND w2.id IS NULL ORDER BY w1.title",
            ["string", "string", "integer"],
            [$trans, "-", $wiki_id]
        );
        while ($rec = $this->db->fetchAssoc($set)) {
            yield $this->getPageFromRecord($rec);
        }
    }

}
