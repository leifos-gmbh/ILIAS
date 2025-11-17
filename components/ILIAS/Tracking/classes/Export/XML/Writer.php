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

namespace ILIAS\Tracking\Export\XML;

use ILIAS\Tracking\Export\Info;
use ILIAS\Tracking\Status\LPStatusInterface;
use SimpleXMLElement;

class Writer implements WriterInterface
{
    protected SimpleXMLElement $xml_root;

    public function __construct()
    {
        $this->xml_root = new SimpleXMLElement('<Tracking></Tracking>');
    }

    public function writeXMLByExportInfo(
        Info $info
    ): void {
        $info_lp_settings = $info->getLPSettings();
        $info_lp_collection = $info->getLPCollection();
        $this->xml_root = new SimpleXMLElement('<Tracking></Tracking>');
        $this->xml_root->addAttribute('object_id', (string) $info_lp_settings->getObjectId());
        $this->xml_root->addAttribute('object_type', $info_lp_settings->getObjType());
        $this->xml_root->addAttribute('u_mode', (string) $info_lp_settings->getUMode());
        $this->xml_root->addAttribute('visits', (string) $info_lp_settings->getVisits());
        $lp_collections = $this->xml_root->addChild('LPCollection');
        foreach ($info_lp_collection as $info_lp_collection_element) {
            $lp_collection = $lp_collections->addChild('LPCollectionElement');
            $lp_collection->addAttribute('item_id', (string) $info_lp_collection_element->getItemId());
            $lp_collection->addAttribute('grouping_id', (string) $info_lp_collection_element->getGroupingId());
            $lp_collection->addAttribute('num_obligatory', (string) $info_lp_collection_element->getNumObligatory());
            $lp_collection->addAttribute('active', (string) ((int) $info_lp_collection_element->isActive()));
            $lp_collection->addAttribute('lp_mode', (string) $info_lp_collection_element->getLpMode());
        }
        $xml_root_lp_status = $this->xml_root->addChild('LPStatusCollection');
        foreach ($info->getLPStatusCollection() as $lp_status) {
            $this->addLPStatusData($info->getLPSettings()->getObjectId(), $xml_root_lp_status, $lp_status);
        }
    }

    protected function addLPStatusData(
        int $object_id,
        SimpleXMLElement $xml_root,
        LPStatusInterface $lp_status
    ): void {
        $status_root = $xml_root->addChild('LPStatus');
        $status_root->addAttribute('lp_status_id', $lp_status->getLPStatusId());
        $nodes_to_add = [$lp_status->getCustomLPSettingsExportXML($object_id)];
        $ref_stack = [$status_root];

        while(count($nodes_to_add) > 0) {
            $current_node_to_add = array_pop($nodes_to_add);
            $pointer_node = array_pop($ref_stack);
            $xml_child_node = $pointer_node->addChild(sprintf('%s', $current_node_to_add->getName()));
            foreach($current_node_to_add->attributes() as $key => $value) {
                $xml_child_node->addAttribute($key, (string) $value);
            }
            $child_count = count($current_node_to_add->children());
            if ($child_count === 0) {
                continue;
            }
            $nodes_to_add = array_merge($nodes_to_add, $current_node_to_add->children());
            $ref_stack = array_merge($ref_stack, array_fill(0, $child_count, $xml_child_node));
        }
    }

    public function __toString(): string
    {
        return trim(str_replace('<?xml version="1.0"?>', '', $this->xml_root->asXML()));
    }
}
