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
use ILIAS\Container\Content\ItemBlock\ItemBlockSequence;

/**
 * High level business logic class. Orchestrates item set,
 * view and block sequence generator.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ItemPresentationManager
{
    protected ItemBlock\ItemBlockSequenceGenerator $sequence_generator;
    protected \ilContainerUserFilter $container_user_filter;
    protected ?array $type_grps = null;
    protected ?ItemSetManager $item_set = null;
    protected \ilContainer $container;
    protected ItemSessionRepository $item_repo;
    protected InternalDomainService $domain;

    public function __construct(
        InternalDomainService $domain,
        \ilContainer $container,
        \ilContainerUserFilter $container_user_filter
    ) {
        $this->container = $container;
        $this->domain = $domain;
        $this->container_user_filter = $container_user_filter;

        // sequence from view manager
    }

    /**
     * @todo from ilContainer, should be removed there
     * @todo make proper service in classification component
     */
    protected function isClassificationFilterActive() : bool
    {
        $ref_id = $this->container->getRefId();
        // apply container classification filters
        $repo = new \ilClassificationSessionRepository($ref_id);
        foreach (\ilClassificationProvider::getValidProviders(
            $this->container->getRefId(),
            $this->container->getId(),
            $this->container->getType()
        ) as $class_provider) {
            $id = get_class($class_provider);
            $current = $repo->getValueForProvider($id);
            if ($current) {
                return true;
            }
        }
        return false;
    }

    /**
     * @todo from ilContainer, should be removed there
     */
    protected function filteredSubtree() : bool
    {
        return $this->isClassificationFilterActive() && in_array(
            $this->container->getType(),
            ["grp", "crs"]
        );
    }

    protected function init() : void
    {
        // already initialised?
        if (!is_null($this->item_set)) {
            return;
        }

        // get item set
        $ref_id = $this->container->getRefId();
        if ($this->filteredSubtree()) {
            $this->item_set = $this->domain->content()->itemSetTree($ref_id);
        } else {
            $this->item_set = $this->domain->content()->itemSetFlat($ref_id);
        }

        // get view
        $view = $this->domain->content()->view($this->container);

        // get item block sequence generator
        $this->sequence_generator = $this->domain->content()->itemBlockSequenceGenerator(
            $this->container,
            $view->getBlockSequence(),
            $this->item_set
        );
    }

    public function hasItems() : bool
    {
        $this->init();
        return $this->item_set->hasItems();
    }

    public function getItemBlockSequence() : ItemBlockSequence
    {
        $this->init();
        return $this->sequence_generator->getSequence();
    }
}
