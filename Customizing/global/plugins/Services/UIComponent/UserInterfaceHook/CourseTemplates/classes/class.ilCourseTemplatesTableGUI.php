<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * List all course templates
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilCourseTemplatesTableGUI extends ilTable2GUI
{
	protected $plugin;
	protected $templates;
	
	function __construct($a_parent_obj, $a_parent_cmd, $a_plugin, $a_templates)
	{		
		global $lng;
		
		$this->plugin = $a_plugin;
		$this->templates = $a_templates;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
	
		$this->setLimit(9999);
		
		$this->addColumn($lng->txt("title"), "title");
		$this->addColumn($lng->txt("description"));
		$this->addColumn($lng->txt("online"), "online");
		$this->addColumn($lng->txt("objs_crs"));		
		
		$this->setDefaultOrderField("title");	
		
		$this->setRowTemplate($this->plugin->getDirectory()."/templates/tpl.course_template_row.html");
		
		$this->getItems();
	}

	function getItems()
	{
		include_once "Services/Link/classes/class.ilLink.php";
		include_once "Modules/Course/classes/class.ilObjCourseAccess.php";
		
		$parent_map = array();
		foreach($this->templates->getCoursesFromTemplate() as $crs_id => $parent_id)
		{
			$parent_map[$parent_id][] = $crs_id;
		}
		
		$data = array();
		foreach($this->templates->getTemplatesData() as $item)
		{
			$crs_info = "";
			if(array_key_exists($item["obj_id"], $parent_map))
			{
				$crs_info = array();
				foreach($parent_map[$item["obj_id"]] as $crs_id)
				{
					$crs_ref = ilObject::_getAllReferences($crs_id);
					$crs_ref = array_pop($crs_ref);
					
					$path = $this->buildPath($crs_ref);
					if($path)
					{
						$crs_info[$crs_ref] = $path;

						if(!ilObjCourseAccess::_isActivated($crs_id))
						{
							$crs_info[$crs_ref] = "(".$crs_info[$crs_ref].")";
						}
					}
				}
			}
			
			asort($crs_info);
			
			$data[] = array(
				"title" => $item["title"],
				"desc" => ilObject::_lookupDescription($item["obj_id"]),
				"url" => ilLink::_getLink($item["ref_id"]),
				"online" => ilObjCourseAccess::_isActivated($item["obj_id"]),
				"crs" => $crs_info
			);
		}
		$this->setData($data);
	}
	
	protected function buildPath($a_ref_id)
	{
		global $tree, $lng;
		
		$res = array();
		
		$path = $tree->getNodePath($a_ref_id);
		if(is_array($path))
		{
			array_shift($path); // root
			$res[] = $lng->txt("repository");
			foreach($path as $item)
			{
				$res[] = $item["title"];
			}	
		}
		
		return implode(" &raquo; ", $res);
	}
	
	protected function fillRow($set)
	{
		global $lng;

		if(is_array($set["crs"]))
		{
			$this->tpl->setCurrentBlock("crs_bl");
			foreach($set["crs"] as $ref_id => $title)
			{
				$this->tpl->setVariable("CRS_TITLE", $title);
				$this->tpl->setVariable("CRS_URL", ilLink::_getLink($ref_id));
				$this->tpl->parseCurrentBlock();
			}
		}
				
		$this->tpl->setVariable("URL", $set["url"]);
		$this->tpl->setVariable("TITLE", $set["title"]);		
		$this->tpl->setVariable("DESC", nl2br(trim($set["desc"])));		
		$this->tpl->setVariable("ONLINE", $set["online"] 
			? $lng->txt("yes")
			: $lng->txt("no"));		
	}

}
	
?>