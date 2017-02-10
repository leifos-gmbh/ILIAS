<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

require_once("./Services/COPage/classes/class.ilPageContent.php");

/**
 * Grid element
 *
 * @author Alex Killing <killing@leifos.de>
 *
 * @ingroup ServicesCOPage
 */
class ilPCGrid extends ilPageContent
{
	protected $grid_node;

	/**
	 * Init page content component.
	 */
	function init()
	{
		$this->setType("grid");
	}

	/**
	 * Set content node
	 * @param object $a_node
	 */
	function setNode($a_node)
	{
		parent::setNode($a_node);		// this is the PageContent node
		$this->grid_node = $a_node->first_child();		// this is the Tabs node
	}

	/**
	 * Get sizes
	 * @return array
	 */
	static function getSizes()
	{
		return array("xs" => "xs", "s" => "s", "m" => "m", "l" => "l");
	}

	/**
	 * Get widths
	 * @return array
	 */
	static function getWidths()
	{
		return array(
			"1" => "1/12", "2" => "2/12", "3" => "3/12",
			"4" => "4/12", "5" => "5/12", "6" => "6/12",
			"7" => "7/12", "8" => "8/12", "9" => "9/12",
			"10" => "10/12", "11" => "11/12", "12" => "12/12"
		);
	}

	/**
	* Create new Grid node
	*/
	function create($a_pg_obj, $a_hier_id, $a_pc_id = "")
	{
		$this->node = $this->createPageContentNode();
		$a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
		$this->grid_node = $this->dom->create_element("Grid");
		$this->grid_node = $this->node->append_child($this->grid_node);
	}

	/**
	 * Set attribute of grid tag
	 *
	 * @param string $a_attr	attribute name
	 * @param string $a_value attribute value
	 */
	protected function setTabsAttribute($a_attr, $a_value)
	{
		if (!empty($a_value))
		{
			$this->grid_node->set_attribute($a_attr, $a_value);
		}
		else
		{
			if ($this->grid_node->has_attribute($a_attr))
			{
				$this->grid_node->remove_attribute($a_attr);
			}
		}
	}


	/**
	 * Save positions of grid cells
	 *
	 * @param array $a_pos
	 */
	function savePositions($a_pos)
	{
		asort($a_pos);

		$childs = $this->grid_node->child_nodes();
		$nodes = array();
		for ($i=0; $i<count($childs); $i++)
		{
			if ($childs[$i]->node_name() == "GridCell")
			{
				$pc_id = $childs[$i]->get_attribute("PCID");
				$hier_id = $childs[$i]->get_attribute("HierId");
				$nodes[$hier_id.":".$pc_id] = $childs[$i];
				$childs[$i]->unlink($childs[$i]);
			}
		}
		
		foreach($a_pos as $k => $v)
		{
			if (is_object($nodes[$k]))
			{
				$nodes[$k] = $this->grid_node->append_child($nodes[$k]);
			}
		}
	}

	/**
	 * Delete grid cell
	 */
	function deleteGridCell($a_hier_id, $a_pc_id)
	{
		$childs = $this->grid_node->child_nodes();
		for ($i=0; $i<count($childs); $i++)
		{
			if ($childs[$i]->node_name() == "GridCell")
			{
				if ($a_pc_id == $childs[$i]->get_attribute("PCID") &&
					$a_hier_id == $childs[$i]->get_attribute("HierId"))
				{
					$childs[$i]->unlink($childs[$i]);
				}
			}
		}
	}

	/**
	 * Add grid cell
	 */
	function addGridCell($a_xs, $a_s, $a_m, $a_l)
	{
		$new_item = $this->dom->create_element("GridCell");
		$new_item = $this->grid_node->append_child($new_item);
		$new_item->set_attribute("xs", $a_xs);
		$new_item->set_attribute("s", $a_s);
		$new_item->set_attribute("m", $a_m);
		$new_item->set_attribute("l", $a_l);
	}

	/**
	 * Get lang vars needed for editing
	 * @return array array of lang var keys
	 */
	static function getLangVars()
	{
		return array("pc_grid", "pc_grid_cell");
	}

	/**
	 * Get Javascript files
	 */
	function getJavascriptFiles($a_mode)
	{
		return parent::getJavascriptFiles($a_mode);
	}

	/**
	 * Get Javascript files
	 */
	function getCssFiles($a_mode)
	{
		return parent::getCssFiles($a_mode);
	}

}
?>
