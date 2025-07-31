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

namespace ILIAS\Search\Presentation\Result;

use ilSearchResult;
use ilLuceneSearchResultFilter;
use ilLuceneHighlighterResultParser;
use ILIAS\Data\URI;
use ILIAS\UI\Component\Modal\Modal;
use ILIAS\UI\Component\Panel\Listing\Listing as ListingPanel;

interface ResultPresenter
{
    /**
     * @return array{0: ListingPanel, 1: Modal[]}
     */
    public function getDirectSearchResultAsPanel(
        ilSearchResult $result,
        Sortation $sortation,
        int $current_page,
        int $max_pages,
        URI $pagination_action,
        string $page_param_name,
        URI $sortation_action,
        string $sortation_param_name
    ): array;

    /**
     * @return array{0: ListingPanel, 1: Modal[]}
     */
    public function getLuceneSearchResultAsPanel(
        ilLuceneSearchResultFilter $result,
        ilLuceneHighlighterResultParser $highlighter,
        Sortation $sortation,
        int $current_page,
        int $max_pages,
        URI $pagination_action,
        string $page_param_name,
        URI $sortation_action,
        string $sortation_param_name
    ): array;
}
