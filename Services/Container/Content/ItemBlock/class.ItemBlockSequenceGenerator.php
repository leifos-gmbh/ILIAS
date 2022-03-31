<?php declare(strict_types=1);

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

namespace ILIAS\Container\Content\ItemBlock;

use ILIAS\Container\Content\DataService;
use ILIAS\Container\Content\BlockSequence;
use ILIAS\Container\Content\BlockSequencePart;
use ILIAS\Container\Content\ItemSetManager;
use ILIAS\Container\Content;
use ILIAS\Container\InternalDomainService;

/**
 * Generates concrete blocks with items
 * for the view
 * @author Alexander Killing <killing@leifos.de>
 */
class ItemBlockSequenceGenerator
{
    protected \ilAccessHandler $access;
    protected int $block_limit;
    protected DataService $data_service;
    protected BlockSequence $block_sequence;
    protected ItemSetManager $item_set_manager;
    protected InternalDomainService $domain_service;
    protected \ilContainer $container;
    protected ?ItemBlockSequence $sequence = null;
    protected bool $has_other_block = false;
    /** @var int[] */
    protected array $accumulated_ref_ids = [];

    public function __construct(
        DataService $data_service,
        InternalDomainService $domain_service,
        \ilContainer $container,
        BlockSequence $block_sequence,
        ItemSetManager $item_set_manager
    ) {
        $this->access = $domain_service->access();
        $this->data_service = $data_service;
        $this->domain_service = $domain_service;
        $this->block_sequence = $block_sequence;
        $this->item_set_manager = $item_set_manager;
        $this->block_limit = (int) \ilContainer::_lookupContainerSetting($container->getId(), "block_limit");
    }

    public function getSequence() : ItemBlockSequence
    {
        if (is_null($this->sequence)) {
            $item_blocks = [];
            $sorted_blocks = [];

            // get blocks from block sequence parts (item groups, by type, objective)
            foreach ($this->block_sequence->getParts() as $part) {
                foreach ($this->getBlocksForPart($part) as $block) {
                    $item_blocks[$block->getId()] = $block;
                }
            }

            // get other block
            $other_block = $this->getOtherBlock();
            if (!is_null($other_block)) {
                $item_blocks["_other"] = $other_block;
            }

            // get blocks of page, put them to the start
            $embedded_ids = $this->getPageEmbeddedBlockIds();
            foreach ($embedded_ids as $id) {
                if (isset($item_blocks[$id])) {
                    $item_blocks[$id]->setPageEmbedded(true);
                    $sorted_blocks[] = $item_blocks[$id];
                    unset($item_blocks[$id]);
                }
            }

            // manual sorting
            $pos = 10;
            $sorting = \ilContainerSorting::_getInstance($this->container->getId());
            foreach ($sorting->getBlockPositions() as $id) {
                if (isset($item_blocks[$id])) {
                    $item_blocks[$id]->setPosition($pos);
                    $sorted_blocks[] = $item_blocks[$id];
                    unset($item_blocks[$id]);
                    $pos += 10;
                }
            }

            // rest in order of base block sequence
            foreach ($item_blocks as $block) {
                $block->setPosition($pos);
                $sorted_blocks[] = $block;
                $pos += 10;
            }

            $this->sequence = new ItemBlockSequence($sorted_blocks);
        }
        return $this->sequence;
    }

    /**
     * @return ItemBlock[]
     */
    protected function getBlocksForPart(BlockSequencePart $part) : \Iterator
    {
        // TypeBlocks
        if ($part instanceof Content\TypeBlocks) {
            foreach ($this->getGroupedObjTypes() as $type) {
                $ref_ids = $this->item_set_manager->getRefIdsOfType($type);
                $block_items = $this->determineBlockItems($ref_ids);
                if (count($block_items->getRefIds()) > 0) {
                    yield $this->data_service->itemBlock(
                        $type,
                        $this->data_service->typeBlock($type),
                        $block_items->getRefIds(),
                        $block_items->getLimitExhausted()
                    );
                }
            }
        }
        // TypeBlock
        if ($part instanceof Content\TypeBlock) {
            $ref_ids = $this->item_set_manager->getRefIdsOfType($part->getType());
            $block_items = $this->determineBlockItems($ref_ids);
            if (count($block_items->getRefIds()) > 0) {
                yield $this->data_service->itemBlock(
                    $part->getType(),
                    $this->data_service->typeBlock($part->getType()),
                    $block_items->getRefIds(),
                    $block_items->getLimitExhausted()
                );
            }
        }
        // SessionBlock
        if ($part instanceof Content\SessionBlock) {
            $ref_ids = $this->item_set_manager->getRefIdsOfType("sess");
            $block_items = $this->determineBlockItems($ref_ids);
            if (count($block_items->getRefIds()) > 0) {
                yield $this->data_service->itemBlock(
                    "sess",
                    $this->data_service->sessionBlock(),
                    $block_items->getRefIds(),
                    $block_items->getLimitExhausted()
                );
            }
        }
        // ItemGroupBlock
        if ($part instanceof Content\ItemGroupBlock) {
            $block = $this->getItemGroupBlock($part->getRefId());
            if (!is_null($block)) {
                yield $block;
            }
        }
        // ItemGroupBlocks
        if ($part instanceof Content\ItemGroupBlocks) {
            foreach ($this->item_set_manager->getRefIdsOfType("itgr") as $item_group_ref_id) {
                $block = $this->getItemGroupBlock($item_group_ref_id);
                if (!is_null($block)) {
                    yield $block;
                }
            }
        }
        // ItemGroupBlocks
        if ($part instanceof Content\OtherBlock) {
            $this->has_other_block = true;
        }
    }

    protected function determineBlockItems(array $ref_ids) : BlockItemsInfo
    {
        $exhausted = false;
        $accessible_ref_ids = [];
        for (; !$exhausted && ($ref_id = current($ref_ids));) {
            if ($this->access->checkAccess('visible', '', $ref_id)) {
                if (count($accessible_ref_ids) >= $this->block_limit) {
                    $exhausted = true;
                } else {
                    $accessible_ref_ids[] = $ref_id;
                }
            }
        }
        return $this->data_service->blockItemsInfo(
            $accessible_ref_ids,
            $exhausted
        );
    }

    protected function accumulateRefIds(array $ref_ids) : void
    {
        foreach ($ref_ids as $ref_id) {
            $this->accumulated_ref_ids[$ref_id] = $ref_id;
        }
    }

    protected function getOtherBlock() : ?ItemBlock
    {
        $remaining_ref_ids = array_filter(
            $this->item_set_manager->getAllRefIds(),
            fn ($i) => !isset($this->accumulated_ref_ids[$i])
        );
        $block_items = $this->determineBlockItems($remaining_ref_ids);
        if (count($block_items->getRefIds()) > 0) {
            return $this->data_service->itemBlock(
                "_other",
                $this->data_service->otherBlock(),
                $block_items->getRefIds(),
                $block_items->getLimitExhausted()
            );
        }
        return null;
    }

    /**
     * Get grouped repository object types
     * @todo from ilContainerContentGUI; remove
     */
    protected function getGroupedObjTypes() : \Iterator
    {
        foreach (\ilObjectDefinition::getGroupedRepositoryObjectTypes($this->container->getType())
            as $type) {
            yield $type;
        }
    }

    protected function getItemGroupBlock(int $item_group_ref_id) : ?ItemBlock
    {
        $ref_ids = array_map(function ($i) {
            return $i["child"];
        }, \ilObjectActivation::getItemsByItemGroup($item_group_ref_id));
        $block_items = $this->determineBlockItems($ref_ids);
        if (count($block_items->getRefIds()) > 0) {
            return $this->data_service->itemBlock(
                (string) $item_group_ref_id,
                $this->data_service->itemGroupBlock($item_group_ref_id),
                $block_items->getRefIds(),
                $block_items->getLimitExhausted()
            );
        }
        return null;
    }

    /**
     * @todo determinePageEmbeddedBlocks from ilContainerContent GUI, remove
     */
    public function getPageEmbeddedBlockIds() : array
    {
        $ids = [];
        $page = $this->domain_service->page($this->container);
        $container_page_html = $page->getHtml();

        $type_grps = $this->getGroupedObjTypes();
        // iterate all types
        foreach ($type_grps as $type => $v) {
            // set template (overall or type specific)
            if (is_int(strpos($container_page_html, "[list-" . $type . "]"))) {
                $ids[] = $type;
            }
        }

        $type = "_other";
        if (is_int(strpos($container_page_html, "[list-" . $type . "]"))) {
            $ids[] = $type;
        }

        // determine item groups
        while (preg_match('~\[(item-group-([0-9]*))\]~i', $container_page_html, $found)) {
            $ids[] = $found[2];
        }
        return $ids;
    }
}
