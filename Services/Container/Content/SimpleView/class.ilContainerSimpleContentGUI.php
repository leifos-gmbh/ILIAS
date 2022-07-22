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

/**
 * Shows all items in one block.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilContainerSimpleContentGUI extends ilContainerContentGUI
{
    public function renderItemList() : string
    {
        $this->initRenderer();
        return $this->renderer->renderItemBlockSequence($this->item_presentation->getItemBlockSequence());
    }

    private function showMaterials(
        ilTemplate $a_tpl
    ) : void {
        $lng = $this->lng;

        $this->items = $this->getContainerObject()->getSubItems($this->getContainerGUI()->isActiveAdministrationPanel());
        $this->clearAdminCommandsDetermination();
        
        $this->initRenderer();
        
        $output_html = $this->getContainerGUI()->getContainerPageHTML();
        
        // get embedded blocks
        if ($output_html !== "") {
            $output_html = $this->insertPageEmbeddedBlocks($output_html);
        }

        // item groups
        $this->getItemGroupsHTML();
        
        if (isset($this->items['_all']) && is_array($this->items["_all"])) {
            $title = $this->getContainerObject()->filteredSubtree()
                ? $lng->txt("cont_found_objects")
                : $lng->txt("content");
            $this->renderer->addCustomBlock("_all", $title);
            
            $position = 1;
            foreach ($this->items["_all"] as $k => $item_data) {
                if (!$this->renderer->hasItem($item_data["child"])) {
                    $html = $this->renderItem($item_data, $position++, true);
                    if ($html != "") {
                        $this->renderer->addItemToBlock("_all", $item_data["type"], $item_data["child"], $html);
                    }
                }
            }
        }

        $output_html .= $this->renderer->getHTML();
        
        $a_tpl->setVariable("CONTAINER_PAGE_CONTENT", $output_html);
    }
}
