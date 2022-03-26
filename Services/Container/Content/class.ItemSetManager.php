<?php declare(strict_types = 1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

namespace ILIAS\Container\Content;

use ILIAS\Container\InternalDomainService;

/**
 * Manages container subitems set
 * @author Alexander Killing <killing@leifos.de>
 */
class ItemSetManager
{
    const FLAT = 0;
    const TREE = 1;

    protected int $ref_id = 0;
    protected InternalDomainService $domain;
    protected array $raw = [];
    protected int $mode = self::FLAT;
    protected \ilContainerUserFilter $user_filter;

    /**
     * @param int $mode self::TREE|self::FLAT
     */
    public function __construct(
        InternalDomainService $domain,
        int $mode,
        int $ref_id,
        ?\ilContainerUserFilter $user_filter = null
    ) {
        $this->ref_id = $ref_id;
        $this->domain = $domain;
        $this->mode = $mode;        // might be refactored as subclasses
        $this->init();
    }

    /**
     * @todo from ilContainer, should be removed there
     */
    protected function init() : void
    {
        $tree = $this->domain->repositoryTree();
        if ($this->mode === self::TREE) {
            $this->raw = $tree->getSubTree($tree->getNodeData($this->ref_id));
        } else {
            $this->raw = $tree->getChilds($this->ref_id, "title");
        }
        $this->applyUserFilter();
    }

    /**
     * @todo from ilContainer, should be removed there
     */
    public function gotItems() : bool
    {
        return count($this->raw) > 0;
    }

    /**
     * Apply container user filter on objects
     * @throws \ilException
     */
    protected function applyUserFilter() : void
    {
        $filter = $this->domain->content()->filter(
            $this->raw,
            $this->user_filter,
            !\ilContainer::_lookupContainerSetting(
                \ilObject::_lookupObjId($this->ref_id),
                "filter_show_empty",
                "0"
            )
        );
        $this->raw = $filter->apply();
    }
}
