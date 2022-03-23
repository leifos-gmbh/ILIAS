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
 * @author Alexander Killing <killing@leifos.de>
 */
class ItemPresentationManager
{
    protected ?array $type_grps = null;
    protected ?ItemSetManager $item_set = null;
    protected \ilContainer $container;
    protected ItemSessionRepository $item_repo;
    protected InternalDomainService $domain;

    public function __construct(
        InternalDomainService $domain,
        \ilContainer $container
    ) {
        $this->container = $container;
        $this->domain = $domain;

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
        if ($this->isClassificationFilterActive() && in_array(
            $this->container->getType(),
            ["grp", "crs"]
        )) {
            return true;
        }
        return false;
    }

    protected function init() : void
    {
        // already initialised?
        if (!is_null($this->item_set)) {
            return;
        }
        $ref_id = $this->container->getRefId();
        if ($this->filteredSubtree()) {
            $this->item_set = $this->domain->content()->itemSetTree($ref_id);
        } else {
            $this->item_set = $this->domain->content()->itemSetFlat($ref_id);
        }
    }

    /**
     * Get grouped repository object types
     * @todo from ilContainerContentGUI; remove
     */
    protected function getGroupedObjTypes() : array
    {
        $objDefinition = $this->domain->objectDefinition();

        if (is_null($this->type_grps)) {
            $this->type_grps =
                $objDefinition->getGroupedRepositoryObjectTypes($this->container->getType());
        }
        return $this->type_grps;
    }

    /**
     * @todo determinePageEmbeddedBlocks from ilContainerContent GUI, remove
     */
    public function getPageEmbeddedBlocks(
        string $a_container_page_html
    ) : void {
        $type_grps = $this->getGroupedObjTypes();

        // iterate all types
        foreach ($type_grps as $type => $v) {
            // set template (overall or type specific)
            if (is_int(strpos($a_container_page_html, "[list-" . $type . "]"))) {
                $this->addEmbeddedBlock("type", $type);
            }
        }

        // determine item groups
        while (preg_match('~\[(item-group-([0-9]*))\]~i', $a_container_page_html, $found)) {
            $this->addEmbeddedBlock("itgr", (int) $found[2]);

            $html = ''; // This was never defined before
            $a_container_page_html = preg_replace('~\[' . $found[1] . '\]~i', $html, $a_container_page_html);
        }
    }


    /**
     * Get all blocks in the correct order
     */
    protected function getBlocks() : \Iterator
    {
        //yield;
    }
}
