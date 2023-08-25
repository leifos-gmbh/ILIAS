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
use ILIAS\Wiki\InternalRepoService;

/**
 * Page manager
 */
class PageManager
{
    protected $ref_id;
    protected InternalDataService $data_service;

    public function __construct(
        InternalDataService $data_service,
        PageDBRepository $page_repo,
        \ILIAS\Wiki\Wiki\DomainService $wiki_domain,
        DomainService $page_domain,
        int $ref_id
    ) {
        $this->wiki_ref_id = $ref_id;
        $this->data_service = $data_service;
        $this->page_repo = $page_repo;
        $this->wiki_domain = $wiki_domain;
        $this->page_domain = $page_domain;
    }

    /**
     * @return iterable<Page>
     */
    public function getWikiPages(string $lang = "-") : \Iterator
    {
        return $this->page_repo->getWikiPages(
            $this->wiki_domain->getObjId($this->wiki_ref_id),
            $lang
        );
    }
}