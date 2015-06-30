<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilCourseTemplates
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilCourseTemplates
{
	protected $plugin_id; 
	protected static $templates; 
	
	protected function __construct($a_plugin_id)
	{
		$this->plugin_id = $a_plugin_id;
	}
	
	public function getInstance($a_plugin_id)
	{
		return new self($a_plugin_id);
	}
	
	public function getAvailableRepositoryCategories()
	{
		global $tree;
		
		$options = array();
		foreach($tree->getSubTree($tree->getNodeData($tree->getRootId()), true, "cat") as $item)
		{
			$options[$item["ref_id"]] = "[".$item["ref_id"]."] ".$item["title"];
		}
		
		return $options;
	}
	
	public function getGlobalTemplateCategory()
	{
		$set = new ilSetting($this->plugin_id);
		return $set->get("cat_ref_id", null);
	}
	
	public function setGlobalTemplateCategory($a_ref_id)
	{		
		$set = new ilSetting($this->plugin_id);
		$set->set("cat_ref_id", $a_ref_id);			
	}
	
	public function getTemplatesData()
	{
		global $tree;
				
		$cat_ref_id = $this->getGlobalTemplateCategory();
		$tmpl_ids = $this->getCoursesWithTemplateStatus();

		$valid = array();
		foreach($tree->getSubTree($tree->getNodeData($cat_ref_id), true, "crs") as $item)
		{
			if(!in_array($item["obj_id"], $tmpl_ids) ||
				$tree->isDeleted($item["ref_id"]))
			{
				continue;
			}			
			$valid[] = $item;			
		}
		return $valid;
	}
	
	public function getAvailableTemplates()
	{		
		if(!is_array(self::$templates))
		{					
			$valid = array();
			foreach($this->getTemplatesData() as $item)
			{
				$valid[$item["ref_id"]] = "[".$item["ref_id"]."] ".$item["title"];
			}

			self::$templates = $valid;
		}
		
		return self::$templates;
	}
	
	public function setCourseTemplateStatus($a_crs_obj_id)
	{
		global $ilDB, $ilUser;				
		
		$ilDB->replace("crs_templates",
			array(
				"crs_id"=>array("integer", $a_crs_obj_id)
			),
			array(
				"usr_id"=>array("integer", $ilUser->getId()),
				"tstamp"=>array("integer", time()),			
			)
		);		
	}
	
	public function getCoursesWithTemplateStatus()
	{
		global $ilDB;
		
		$tmpl_ids = array();
		$set = $ilDB->query("SELECT crs_id".
			" FROM crs_templates".
			" WHERE parent IS NULL");
		while($row = $ilDB->fetchAssoc($set))
		{
			$tmpl_ids[] = $row["crs_id"];					
		}
		return $tmpl_ids;
	}
	
	public function getCoursesFromTemplate()
	{
		global $ilDB;
		
		$tmpl_ids = array();
		$set = $ilDB->query("SELECT crs_id, parent".
			" FROM crs_templates".
			" WHERE parent IS NOT NULL");
		while($row = $ilDB->fetchAssoc($set))
		{
			$tmpl_ids[$row["crs_id"]] = $row["parent"];					
		}
		return $tmpl_ids;
	}
	
	public function setCourseFromTemplateStatus($a_tmpl_obj_id, $a_crs_obj_id)
	{
		global $ilDB, $ilUser;				
		
		$ilDB->replace("crs_templates",
			array(
				"crs_id"=>array("integer", $a_crs_obj_id)
			),
			array(
				"usr_id"=>array("integer", $ilUser->getId()),
				"tstamp"=>array("integer", time()),	
				"parent"=>array("integer", $a_tmpl_obj_id)
			)
		);		
	}
}