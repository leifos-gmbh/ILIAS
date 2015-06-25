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
	
	public function getAvailableTemplates()
	{
		global $tree;
		
		$cat_ref_id = $this->getGlobalTemplateCategory();
		
		$options = array();
		foreach($tree->getSubTree($tree->getNodeData($cat_ref_id), true, "crs") as $item)
		{
			$options[$item["ref_id"]] = "[".$item["ref_id"]."] ".$item["title"];
		}
		
		return $options;
	}
}