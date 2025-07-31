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

namespace ILIAS\WebResource\Search;

use ILIAS\Search\Presentation\Result\Subitem\PropertiesReader;
use ILIAS\DI\Container;
use ILIAS\Data\URI;
use ILIAS\Data\Factory as DataFactory;
use ilLanguage;
use ilWebLinkItem;
use ilObject;
use ilWebLinkDatabaseRepository;
use ilWebLinkDatabaseRepositoryException;

class SubitemPropertiesReader implements PropertiesReader
{
    protected array $items_by_ref_id_and_item_id = [];

    protected ilLanguage $lng;
    protected DataFactory $data_factory;

    public static function type(): string
    {
        return 'webr';
    }

    public function init(Container $dic): void
    {
        $this->lng = $dic->language();
        $this->data_factory = new DataFactory();
    }

    public function getSubitemTitle(int $parent_ref_id, int $id): string
    {
        return $this->getItem($parent_ref_id, $id)?->getTitle() ?? '';
    }

    public function getLinkToSubitem(int $parent_ref_id, int $id): ?URI
    {
        $item = $this->getItem($parent_ref_id, $id);
        if ($item === null) {
            return null;
        }
        return $this->data_factory->uri($item->getResolvedLink(false));
    }

    public function openLinkInNewViewport(int $parent_ref_id, int $id): bool
    {
        return true;
    }

    public function getPresentableSubitemType(int $parent_ref_id, int $id): string
    {
        return $this->lng->txt('webr');
    }

    protected function getItem(int $parent_ref_id, int $id): ?ilWebLinkItem
    {
        if (isset($this->items_by_ref_id_and_item_id[$parent_ref_id][$id])) {
            return $this->items_by_ref_id_and_item_id[$parent_ref_id][$id];
        }
        $obj_id = ilObject::_lookupObjId($parent_ref_id);
        $repo = new ilWebLinkDatabaseRepository($obj_id);
        try {
            $item = $repo->getItemByLinkId($id);
        } catch (ilWebLinkDatabaseRepositoryException $e) {
            $item = null;
        }
        return $this->items_by_ref_id_and_item_id[$parent_ref_id][$id] = $item;
    }
}
