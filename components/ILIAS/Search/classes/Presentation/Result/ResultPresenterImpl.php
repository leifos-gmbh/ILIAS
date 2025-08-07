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
use DateTimeImmutable;
use ILIAS\UI\Component\Item\Item;
use ILIAS\Search\Presentation\Result\Subitem\PropertiesAggregator as SubitemPropertiesAggregator;
use Generator;
use ILIAS\Search\Presentation\Result\UI\ComponentFactory;
use ILIAS\Search\Presentation\Result\UI\Sanitizer;
use ILIAS\Search\Presentation\Result\Object\PropertiesAggregator as ObjectPropertiesAggregator;
use ILIAS\Search\Presentation\Result\Object\AccessChecker;
use ILIAS\Search\GUI\Param;

class ResultPresenterImpl implements ResultPresenter
{
    protected const int MAX_SUBITEMS = 49;
    protected const int MAX_SUBITEMS_PER_PAGE = 5;

    public function __construct(
        protected ComponentFactory $component_factory,
        protected ObjectPropertiesAggregator $obj_properties,
        protected SubitemPropertiesAggregator $subitem_properties,
        protected AccessChecker $access,
        protected Sanitizer $sanitizer
    ) {
    }

    /**
     * @return array{0: ListingPanel, 1: Modal[]}
     */
    public function getDirectSearchResultAsPanel(
        ilSearchResult $result,
        ViewControlInfos $view_control_infos
    ): array {
        $items = [];
        $subitem_modals = [];

        $items_with_sort_data = [];
        $subitem_ids_by_obj_id = $result->getSubitemIds();
        foreach ($result->getResultsForPresentation() as $ref_id => $obj_id) {
            $title = $this->obj_properties->lookupTitle($obj_id);
            $creation_date = $this->obj_properties->lookupCreationDate($obj_id);
            $type = $this->obj_properties->lookupType($obj_id);

            $subitem_ids = [];
            if ($this->access->canSeeSubitemsOfObject($ref_id)) {
                $subitem_ids = $subitem_ids_by_obj_id[$obj_id] ?? [];
            }
            $truncated_subitem_ids = array_slice($subitem_ids, 0, self::MAX_SUBITEMS);
            $subitem_modal = $this->component_factory->getModalForSubitems(
                $title,
                self::MAX_SUBITEMS_PER_PAGE,
                count($subitem_ids) > self::MAX_SUBITEMS,
                ...$this->getItemsForSubitemsFromDirectSearch($ref_id, $type, ...$truncated_subitem_ids),
            );
            if ($subitem_modal !== null) {
                $subitem_modals[] = $subitem_modal;
            }

            $item = $this->component_factory->getItemForObject(
                $this->obj_properties->buildIconPath($obj_id, $type),
                $this->obj_properties->makeTypePresentable($type),
                $title,
                $this->obj_properties->buildLink($ref_id, $type),
                $this->obj_properties->lookupDescription($obj_id),
                '',
                $this->obj_properties->buildRepositoryPath($ref_id),
                $creation_date,
                $subitem_modal?->getShowSignal()
            );
            $items_with_sort_data[] = [
                'relevance' => 0,
                'title' => $title,
                'creation_date' => $creation_date,
                'item' => $item
            ];
        }

        $items = $this->sortObjectItems($sortation, ...$items_with_sort_data);

        return [
            $this->component_factory->getPanel(
                $sortation,
                $current_page,
                $max_pages,
                $result,
                $pagination_action,
                $page_param_name,
                $sortation_action,
                $sortation_param_name,
                ...$items
            ),
            $subitem_modals
        ];
    }

    protected function getItemsForSubitemsFromDirectSearch(
        int $ref_id,
        string $type,
        int ...$sub_ids
    ): Generator {
        $sub_ids_as_strings = [];
        foreach ($sub_ids as $sub_id) {
            $sub_ids_as_strings[] = (string) $sub_id;
        }
        $subitem_properties = $this->subitem_properties->getSubitemProperties($ref_id, $type, ...$sub_ids_as_strings);
        foreach ($subitem_properties as $properties) {
            yield $this->component_factory->getItemForSubitem(
                $properties->title(),
                $properties->link(),
                $properties->openLinkInNewViewport(),
                '',
                $properties->presentableSubitemType(),
            );
        }
    }

    /**
     * @return array{0: ListingPanel, 1: Modal[]}
     */
    public function getLuceneSearchResultAsPanel(
        ilLuceneSearchResultFilter $result,
        ilLuceneHighlighterResultParser $highlighter,
        ViewControlInfos $view_control_infos
    ): array {
        $items = [];
        $subitem_modals = [];

        $items_with_sort_data = [];
        foreach ($result->getResults() as $ref_id => $obj_id) {
            $creation_date = $this->obj_properties->lookupCreationDate($obj_id);
            $type = $this->obj_properties->lookupType($obj_id);
            $title_no_highlights = $this->obj_properties->lookupTitle($obj_id);

            $subitem_ids = [];
            if ($this->access->canSeeSubitemsOfObject($ref_id)) {
                $subitem_ids = $highlighter->getSubItemIds($obj_id);
            }
            $truncated_subitem_ids = array_slice($subitem_ids, 0, self::MAX_SUBITEMS);
            $subitem_modal = $this->component_factory->getModalForSubitems(
                $title_no_highlights,
                self::MAX_SUBITEMS_PER_PAGE,
                count($subitem_ids) > self::MAX_SUBITEMS,
                ...$this->getItemsForSubitemsFromLuceneSearch($highlighter, $obj_id, $ref_id, $type, ...$truncated_subitem_ids),
            );
            if ($subitem_modal !== null) {
                $subitem_modals[] = $subitem_modal;
            }

            $item = $this->component_factory->getItemForObject(
                $this->obj_properties->buildIconPath($obj_id, $type),
                $this->obj_properties->makeTypePresentable($type),
                $highlighter->getTitle($obj_id, 0) ?: $title_no_highlights,
                $this->obj_properties->buildLink($ref_id, $type),
                $highlighter->getDescription($obj_id, 0) ?: $this->obj_properties->lookupDescription($obj_id),
                $highlighter->getContent($obj_id, 0),
                $this->obj_properties->buildRepositoryPath($ref_id),
                $creation_date,
                $subitem_modal?->getShowSignal()
            );
            $items_with_sort_data[] = [
                'relevance' => $highlighter->getRelevance($obj_id, 0),
                'title' => $title_no_highlights,
                'creation_date' => $creation_date,
                'item' => $item
            ];
        }

        $items = $this->sortObjectItems($sortation, ...$items_with_sort_data);

        return [
            $this->component_factory->getPanel(
                $sortation,
                $current_page,
                $max_pages,
                $result,
                $pagination_action,
                $page_param_name,
                $sortation_action,
                $sortation_param_name,
                ...$items
            ),
            $subitem_modals
        ];
    }

    protected function getItemsForSubitemsFromLuceneSearch(
        ilLuceneHighlighterResultParser $highlighter,
        int $obj_id,
        int $ref_id,
        string $type,
        int ...$sub_ids
    ): Generator {
        $sub_ids_as_strings = [];
        foreach ($sub_ids as $sub_id) {
            $sub_ids_as_strings[] = (string) $sub_id;
        }
        $subitem_properties = $this->subitem_properties->getSubitemProperties($ref_id, $type, ...$sub_ids_as_strings);
        foreach ($subitem_properties as $properties) {
            $subitem_id = (int) $properties->id();
            yield $this->component_factory->getItemForSubitem(
                $highlighter->getTitle($obj_id, $subitem_id) ?: $properties->title(),
                $properties->link(),
                $properties->openLinkInNewViewport(),
                $highlighter->getContent($obj_id, $subitem_id),
                $properties->presentableSubitemType()
            );
        }
    }

    /**
     * @param array{relevance: int, title: string, creation_date: DateTimeImmutable, item: Item} ...$items_with_sort_data
     * @return Item[]
     */
    protected function sortObjectItems(Sortation $sortation, array ...$items_with_sort_data): \Generator
    {
        $sort_callable = match ($sortation) {
            Sortation::RELEVANCE_DESC => fn($a, $b) => $b['relevance'] <=> $a['relevance'],
            Sortation::TITLE_ASC => fn($a, $b) => [$a['title'], $a['relevance']] <=> [$b['title'], $b['relevance']],
            Sortation::TITLE_DESC => fn($a, $b) => [$b['title'], $b['relevance']] <=> [$a['title'], $a['relevance']],
            Sortation::CREATION_DATE_ASC => fn($a, $b) => [$a['creation_date'], $a['relevance']] <=> [$b['creation_date'], $b['relevance']],
            Sortation::CREATION_DATE_DESC => fn($a, $b) => [$b['creation_date'], $b['relevance']] <=> [$a['creation_date'], $a['relevance']]
        };

        usort($items_with_sort_data, $sort_callable);

        foreach ($items_with_sort_data as $item) {
            yield $item['item'];
        }
    }

    public function getViewControlInfos(
        Sortation $sortation,
        int $current_page,
        int $max_pages,
        URI $pagination_action,
        Param $page_param_name,
        URI $sortation_action,
        Param $sortation_param_name
    ): ViewControlInfos {
        return new ViewControlInfosImpl(
            $sortation,
            $current_page,
            $max_pages,
            $pagination_action,
            $page_param_name,
            $sortation_action,
            $sortation_param_name
        );
    }

    public function replacePlaceholders(string $html): string
    {
        return $this->sanitizer->replacePlaceholders($html);
    }
}
