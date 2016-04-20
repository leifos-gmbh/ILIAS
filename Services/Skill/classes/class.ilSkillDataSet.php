<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/DataSet/classes/class.ilDataSet.php");

/**
 * Skill Data set class
 * 
 * This class implements the following entities:
 * - skmg: Skill management top entity (no data, only dependecies)
 * - skl_subtree: Skill subtree (includes data of skl_tree, skl_tree_node and skl_templ_ref)
 * - skl_templ_subtree: Skill template subtree (includes data of skl_tree and skl_tree_node)
 * - skl_level: Skill levels
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ingroup ServicesSkill
 */
class ilSkillDataSet extends ilDataSet
{
	/**
	 * @var ilSkillTree
	 */
	protected $skill_tree;

	/**
	 * Constructor
	 */
	function __construct()
	{
		include_once("./Services/Skill/classes/class.ilSkillTree.php");
		$this->skill_tree = new ilSkillTree();
	}

	/**
	 * Get supported versions
	 *
	 * @return array of version strings
	 */
	public function getSupportedVersions()
	{
		return array("5.1.0");
	}
	
	/**
	 * Get xml namespace
	 *
	 * @param
	 * @return
	 */
	function getXmlNamespace($a_entity, $a_schema_version)
	{
		return "http://www.ilias.de/xml/Services/Skill/".$a_entity;
	}
	
	/**
	 * Get field types for entity
	 *
	 * @param
	 * @return
	 */
	protected function getTypes($a_entity, $a_version)
	{
		if ($a_entity == "skmg")
		{
			switch ($a_version)
			{
				case "5.1.0":
					return array(
							"Mode" => "text"
					);
			}
		}
		if ($a_entity == "skl_subtree")
		{
			switch ($a_version)
			{
				case "5.1.0":
					return array(
							"SklTreeId" => "integer",
							"TopNode" => "integer",
							"Child" => "integer",
							"Parent" => "integer",
							"Depth" => "integer",
							"Type" => "text",
							"Title" => "text",
							"SelfEval" => "integer",
							"OrderNr" => "integer",
							"Status" => "integer",
							"TemplateId" => "integer"
					);
			}
		}
		if ($a_entity == "skl_templ_subtree")
		{
			switch ($a_version)
			{
				case "5.1.0":
					return array(
							"SklTreeId" => "integer",
							"TopNode" => "integer",
							"Child" => "integer",
							"Parent" => "integer",
							"Depth" => "integer",
							"Type" => "text",
							"Title" => "text",
							"SelfEval" => "integer",
							"OrderNr" => "integer",
							"Status" => "integer"
					);
			}
		}
		if ($a_entity == "skl_level")
		{
			switch ($a_version)
			{
				case "5.1.0":
					return array(
							"LevelId" => "integer",
							"SkillId" => "integer",
							"Nr" => "integer",
							"Title" => "text",
							"Description" => "text"
					);
			}
		}
		return array();
	}

	/**
	 * Read data
	 *
	 * @param
	 * @return
	 */
	function readData($a_entity, $a_version, $a_ids, $a_field = "")
	{
		global $ilDB;

		$this->data = array();

		if (!is_array($a_ids))
		{
			$a_ids = array($a_ids);
		}
		if ($a_entity == "skmg")	// dummy node
		{
			switch ($a_version)
			{
				case "5.1.0":
					$this->data[] = array("Mode" => "Default");
					break;

			}
		}
		if ($a_entity == "skl_subtree")	// get subtree for top node
		{
			switch ($a_version)
			{
				case "5.1.0":
					foreach ($a_ids as $id)
					{
						$sub = $this->skill_tree->getSubTree($this->skill_tree->getNodeData($id));
						foreach ($sub as $s)
						{
							$set = $ilDB->query("SELECT * FROM skl_templ_ref ".
								" WHERE skl_node_id = ".$ilDB->quote($s["child"], "integer")
								);
							$rec = $ilDB->fetchAssoc($set);

							$top_node = ($s["child"] == $id)
									? 1
									: 0;
							$this->data[] = array(
									"SklTreeId" => $s["skl_tree_id"],
									"TopNode" => $top_node,
									"Child" => $s["child"],
									"Parent" => $s["parent"],
									"Depth" => $s["depth"],
									"Type" => $s["type"],
									"Title" => $s["title"],
									"SelfEval" => $s["self_eval"],
									"OrderNr" => $s["order_nr"],
									"Status" => $s["status"],
									"TemplateId" => (int) $rec["templ_id"]
								);
						}
					}
					break;

			}
		}

		if ($a_entity == "skl_templ_subtree")	// get template subtree for template id
		{
			switch ($a_version)
			{
				case "5.1.0":
					foreach ($a_ids as $id)
					{
						$sub = $this->skill_tree->getSubTree($this->skill_tree->getNodeData($id));
						foreach ($sub as $s)
						{
							$top_node = ($s["child"] == $id)
									? 1
									: 0;
							$this->data[] = array(
									"SklTreeId" => $s["skl_tree_id"],
									"TopNode" => $top_node,
									"Child" => $s["child"],
									"Parent" => $s["parent"],
									"Depth" => $s["depth"],
									"Type" => $s["type"],
									"Title" => $s["title"],
									"SelfEval" => $s["self_eval"],
									"OrderNr" => $s["order_nr"],
									"Status" => $s["status"]
							);
						}
					}
					break;

			}
		}

		if ($a_entity == "skl_level")
		{
			switch ($a_version)
			{
				case "5.1.0":
					$this->getDirectDataFromQuery("SELECT id level_id, skill_id, nr, title, description".
							" FROM skl_level WHERE ".
							$ilDB->in("skill_id", $a_ids, false, "integer")." ORDER BY skill_id ASC, nr ASC");
					break;

			}
		}

	}
	
	/**
	 * Determine the dependent sets of data 
	 */
	protected function getDependencies($a_entity, $a_version, $a_rec, $a_ids)
	{
		global $ilDB;

		include_once("./Services/Skill/classes/class.ilSkillTreeNode.php");

		switch ($a_entity)
		{
			case "skmg":
				// determine top nodes of main tree to be exported and all referenced template nodes
				$childs = $this->skill_tree->getChildsByTypeFilter($this->skill_tree->readRootId(), array("skll", "scat", "sctr", "sktr"));
				$deps = array();
				foreach ($childs as $c)
				{
					$deps["skl_subtree"]["ids"][] = $c["child"];
				}

				// determine template subtrees
				$ref_nodes = array();
				if (is_array($deps["skl_subtree"]["ids"]))
				{
					foreach ($deps["skl_subtree"]["ids"] as $id)
					{
						if (ilSkillTreeNode::_lookupType($id) == "sktr")
						{
							$ref_nodes[$id] = $id;
						}
						else
						{
							$sub = $this->skill_tree->getSubTree($this->skill_tree->getNodeData($id), true, "sktr");
							foreach ($sub as $s)
							{
								$ref_nodes[$s["child"]] = $s["child"];
							}
						}
					}
				}

				$set = $ilDB->query("SELECT DISTINCT(templ_id) FROM skl_templ_ref ".
					" WHERE ".$ilDB->in("skl_node_id", $ref_nodes, false, "integer"));
				while ($rec = $ilDB->fetchAssoc($set))
				{
					$deps["skl_templ_subtree"]["ids"][] = $rec["templ_id"];
				}
				return $deps;

			case "skl_subtree":
			case "skl_templ_subtree":
				$deps = array();
				if (in_array($a_rec["Type"], array("skll", "sktp")))
				{
					$deps["skl_level"]["ids"][] = $a_rec["Child"];
				}
				return $deps;

		}

		return false;
	}
	
	
	/**
	 * Import record
	 *
	 * @param
	 * @return
	 */
	function importRecord($a_entity, $a_types, $a_rec, $a_mapping, $a_schema_version)
	{
		switch ($a_entity)
		{
			case "skll":

				return;
				include_once("./Modules/Wiki/classes/class.ilObjWiki.php");
				if($new_id = $a_mapping->getMapping('Services/Container','objs',$a_rec['Id']))
				{
					$newObj = ilObjectFactory::getInstanceByObjId($new_id,false);
				}
				else
				{
					$newObj = new ilObjWiki();
					$newObj->setType("wiki");
					$newObj->create(true);
				}
					
				$newObj->setTitle($a_rec["Title"]);
				$newObj->setDescription($a_rec["Description"]);
				$newObj->setShortTitle($a_rec["Short"]);
				$newObj->setStartPage($a_rec["StartPage"]);
				$newObj->setRatingOverall($a_rec["RatingOverall"]);
				$newObj->setRating($a_rec["Rating"]);
				$newObj->setIntroduction($a_rec["Introduction"]);
				$newObj->setPublicNotes($a_rec["PublicNotes"]);
				
				// >= 4.3
				if(isset($a_rec["PageToc"]))
				{
					// $newObj->setImportantPages($a_rec["ImpPages"]);
					$newObj->setPageToc($a_rec["PageToc"]);
					$newObj->setRatingAsBlock($a_rec["RatingSide"]);
					$newObj->setRatingForNewPages($a_rec["RatingNew"]);
					$newObj->setRatingCategories($a_rec["RatingExt"]);			
				}
				
				$newObj->update(true);
				$this->current_obj = $newObj;
				$a_mapping->addMapping("Modules/Wiki", "wiki", $a_rec["Id"], $newObj->getId());
				$a_mapping->addMapping("Services/Rating", "rating_category_parent_id", $a_rec["Id"], $newObj->getId());
				$a_mapping->addMapping("Services/AdvancedMetaData", "parent", $a_rec["Id"], $newObj->getId());
				break;

		}
	}
}
?>