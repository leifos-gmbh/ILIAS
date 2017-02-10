<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/COPage/classes/class.ilPageContent.php");

/**
 * Cell of a grid
 *
 * @author Alex Killing <killing@leifos.de>
 *
 * @ingroup ServicesCOPage
 */
class ilPCGridCell extends ilPageContent
{
	var $dom;

	/**
	* Init page content component.
	*/
	function init()
	{
		$this->setType("gcell");
	}

	/**
	* insert new cell item after current one
	*/
	function newItemAfter()
	{
		$grid_cell = $this->getNode();
		$new_cell = $this->dom->create_element("GridCell");
		if ($next_tab = $grid_cell->next_sibling())
		{
			$next_tab->insert_before($new_cell, $next_tab);
		}
		else
		{
			$parent_tabs = $grid_cell->parent_node();
			$parent_tabs->append_child($new_cell);
		}
	}


	/**
	* insert new tab item before current one
	*/
	function newItemBefore()
	{
		$grid_cell = $this->getNode();
		$new_cell = $this->dom->create_element("GridCell");
		$grid_cell->insert_before($new_cell, $grid_cell);
	}


	/**
	 * delete tab
	 */
	function deleteItem()
	{
		$grid_cell = $this->getNode();
		$grid_cell->unlink($grid_cell);
	}

	/**
	* move tab item down
	*/
	function moveItemDown()
	{
		$grid_cell = $this->getNode();
		$next = $grid_cell->next_sibling();
		$next_copy = $next->clone_node(true);
		$grid_cell->insert_before($next_copy, $grid_cell);
		$next->unlink($next);
	}

	/**
	* move tab item up
	*/
	function moveItemUp()
	{
		$grid_cell = $this->getNode();
		$prev = $grid_cell->previous_sibling();
		$grid_cell_copy = $grid_cell->clone_node(true);
		$prev->insert_before($grid_cell_copy, $prev);
		$grid_cell->unlink($grid_cell);
	}

}
?>
