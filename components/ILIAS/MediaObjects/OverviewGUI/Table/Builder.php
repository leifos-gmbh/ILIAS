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

namespace ILIAS\MediaObjects\OverviewGUI\Table;

use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;
use ilLanguage;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\MediaObjects\OverviewGUI\SubObjectRetrieval;
use ILIAS\StaticURL\Services as StaticURL;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\MetaData\Services\ServicesInterface as LOM;
use ilAccess;
use DateTimeImmutable;
use ILIAS\UI\Component\Listing\Unordered as UnorderedListing;

class Builder extends CommonTableBuilder
{
    public function __construct(
        object $parent_gui,
        string $parent_cmd,
        protected SubObjectRetrieval $sub_object_retrieval,
        protected ilLanguage $lng,
        protected UIFactory $ui_factory,
        protected StaticURL $static_url,
        protected DataFactory $data_factory,
        protected ilAccess $access,
        protected LOM $lom
    ) {
        parent::__construct($parent_gui, $parent_cmd, true);
    }

    protected function getId(): string
    {
        return 'mob_overview';
    }

    protected function getTitle(): string
    {
        return $this->lng->txt('mob_media_objects_overview');
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return new Retrieval(
            $this->sub_object_retrieval,
            $this->static_url,
            $this->data_factory,
            $this->access,
            $this->lom
        );
    }

    protected function transformRow(array $data_row): array
    {
        $data = [
            'id' => $data_row['id'],
            'title' => $data_row['title'] ?? '',
            'last_update' => new DateTimeImmutable('@' . ($data_row['last_update'] ?? 0)),
            'copyright' => $data_row['copyright'] ?? '',
            'internal_usages' => $this->buildLinkListingFromData($data_row['internal_usages'] ?? []),
            'mep_usages' => $this->buildLinkListingFromData($data_row['mep_usages'] ?? []),
            'external_usages' => $this->buildLinkListingFromData($data_row['external_usages'] ?? [])
        ];
        return $data;
    }

    protected function buildLinkListingFromData(array $usage_data): UnorderedListing
    {
        $links = [];
        foreach ($usage_data as $usage) {
            $title = $usage['title'] ?? '';
            $link_string = $usage['link'] ?? '';
            if ($title === '') {
                continue;
            }
            $link = $this->ui_factory->link()->standard($title, $link_string);
            if ($link_string === '') {
                $link = $link->withDisabled();
            }
            $links[] = $link;
        }
        return $this->ui_factory->listing()->unordered($links);
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $table = $table
            ->textColumn('title', $this->lng->txt('mob'), true)
            ->dateColumn('last_update', $this->lng->txt('mob_last_update'), true);
        if ($this->lom->copyrightHelper()->isCopyrightSelectionActive()) {
            $table = $table->textColumn('copyright', $this->lng->txt('mob_copyright'), true);
        }
        return $table
            ->linkListingColumn('internal_usages', $this->lng->txt('mob_internal_usages_in_object'))
            ->linkListingColumn('mep_usages', $this->lng->txt('mob_usages_in_media_pools'))
            ->linkListingColumn('external_usages', $this->lng->txt('mob_usages_in_other_objects'));
    }
}
