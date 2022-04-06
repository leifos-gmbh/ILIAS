<?php

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

/**
 * Shows all items grouped by type.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilContainerByTypeContentGUI extends ilContainerContentGUI
{
    public function renderItemList() : string
    {
        $this->initRenderer();

        if (true) {
            return $this->renderer->renderItemBlockSequence($this->item_presentation->getItemBlockSequence());
        }
        
        // text/media page content
        $output_html = $this->getContainerGUI()->getContainerPageHTML();
        
        // get embedded blocks
        if ($output_html != "") {
            $output_html = $this->insertPageEmbeddedBlocks($output_html);
        }

        // item groups
        $pos = $this->getItemGroupsHTML();
        
        // iterate all types
        foreach ($this->getGroupedObjTypes() as $type => $v) {
            if (isset($this->items[$type]) && is_array($this->items[$type]) &&
                $this->renderer->addTypeBlock($type)) {
                $this->renderer->setBlockPosition($type, ++$pos);
                
                $position = 1;
                
                foreach ($this->items[$type] as $item_data) {
                    $item_ref_id = $item_data["child"];

                    if ($this->block_limit > 0 && !$this->getContainerGUI()->isActiveItemOrdering() && $position == $this->block_limit + 1) {
                        if ($position == $this->block_limit + 1) {
                            // render more button
                            $this->renderer->addShowMoreButton($type);
                        }
                        continue;
                    }

                    if (!$this->renderer->hasItem($item_ref_id)) {
                        $html = $this->renderItem($item_data, $position++);
                        if ($html != "") {
                            $this->renderer->addItemToBlock($type, $item_data["type"], $item_ref_id, $html);
                        }
                    }
                }
            }
        }
        
        $output_html .= $this->renderer->getHTML();
        
        return $output_html;
    }
}
