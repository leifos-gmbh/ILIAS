<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once './Services/Table/classes/class.ilTable2GUI.php';
/**
 * GUI class for the workflow of object deletion
 * 
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 * 
 *
 * @ingroup ServicesObject
 */
class ilObjectDeletionTableGUI extends ilTable2GUI
{
	const TYPES_ALL = 0;
	
	protected $filter = array();
	
	protected $objects = array();
	
	/**
	 * Constructor
	 * @param type $a_parent_obj
	 * @param type $a_parent_cmd
	 * @param type $a_id
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $a_id)
	{
		$this->setId('obj_deletion_table_'.$a_id);
		parent::__construct($a_parent_obj, $a_parent_cmd, '');
		
	}
	
	
	public function setObjects($a_ref_ids)
	{
		$this->objects = $a_ref_ids;
	}
	
	public function getObjects()
	{
		return $this->objects;
	}
	
	
	/**
	 * Init Table
	 */
	public function init()
	{
		$this->setExternalSorting(TRUE);
		$this->setFormAction($GLOBALS['ilCtrl']->getFormAction($this->getParentObject()));
		
		$this->setDisableFilterHiding(TRUE);
		$this->setRowTemplate('tpl.object_deletion_table_row.html', 'Services/Object');

		$this->addColumn('','','1px');
		$this->addColumn($this->lng->txt('title'),'');
		$this->addColumn($this->lng->txt('obj_tbl_col_subobjects'),'');
		#$this->addColumn($this->lng->txt('obj_tbl_col_notice'),'');
		
		
		$this->addMultiCommand('deleteSelectedItems', $this->lng->txt('delete'));
		
		$this->addCommandButton('deleteAllItems', $this->lng->txt('delete_all'));
		$this->addCommandButton('cancel', $this->lng->txt('cancel'));
		
		$this->initFilter();
	}
	
	/**
	 * 
	 */
	public function initFilter()
	{
		$title = $this->addFilterItemByMetaType('title', ilTable2GUI::FILTER_TEXT, FALSE, $this->lng->txt('title'));
		$this->filter['title'] = $title->getValue();
		
		include_once './Services/Form/classes/class.ilMultiSelectInputGUI.php';
		$type = new ilSelectInputGUI($this->lng->txt('objs_type'),'type');
		//$type->setMulti(TRUE);
		
		$this->lng->loadLanguageModule('administration');
		
		$all_types = array();
		foreach($this->getObjects() as $ref_id)
		{
			$all_types = array_unique(array_merge($all_types,$GLOBALS['tree']->getSubtreeTypes($ref_id)));
		}
		
		$options = array();
		$options[self::TYPES_ALL] = $this->lng->txt('adm_rep_tree_all_types');
		foreach($all_types as $obj_type)
		{
			$options[$obj_type] = $this->lng->txt('obj_'.$obj_type);
		}
		$type->setOptions($options);
		
		$this->addFilterItem($type, FALSE);
	    $type->readFromSession();
		$this->filter['type'] = $type->getValue();
		
		$level = $this->addFilterItemByMetaType('level', ilTable2GUI::FILTER_SELECT, FALSE, $this->lng->txt('obj_filter_level'));
		$level->setOptions(array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10));
		$this->filter['level'] = $level->getValue();
		#$this->filter['level'] = ($level->getValue() ? $level->getValue() : 1);
	}


	/**
	 * Fill row
	 * @param type $set
	 */
	public function fillRow($set)
	{
		$this->tpl->setVariable('VAL_ID',$set['ref_id']);

		for($d = 1; $d < $set['depth']; $d++)
		{
			$this->tpl->touchBlock('padding');
			$this->tpl->touchBlock('padding_end');
		}
		
		
		include_once './Services/Link/classes/class.ilLink.php';
		$this->tpl->setVariable('OBJ_TITLE_LINK',ilLink::_getLink($set['ref_id'], $set['type']));
		$this->tpl->setVariable('OBJ_TITLE',$set['title']);
		
		
		if(strlen($set['description']))
		{
			$this->tpl->setVariable('VAL_DESC',$set['description']);
		}
		
		$this->tpl->setVariable('TYPE_IMG',ilUtil::getTypeIconPath($set['type'], $set['obj_id']));
		$this->tpl->setVariable('TYPE_STR',$this->lng->txt('obj_'.$set['type']));
		
		$this->tpl->setVariable('VAL_SUBOBJECTS',(int) $set['subobjects']);
	}
	
	/**
	 * Parse objects
	 */
	public function parse()
	{
		
		$counter = 0;
		$set = array();
		foreach($this->getObjects() as $ref_id)
		{
			$this->parseItem($set, $counter, 0, $ref_id);
		}
		
		
		$filtered = array();
		foreach($set as $item)
		{
			$filtered[] = $item['ref_id'];
		}
		$this->getParentObject()->setSelectedFilteredObjects($filtered);
		$this->setData($set);
	}
	
	/**
	 * 
	 * @param type $a_ref_id
	 * @param type $a_level
	 */
	protected function parseItem(&$set, &$counter, $a_level, $a_ref_id)
	{
		$a_level++;
		
		$type = ilObject::_lookupType(ilObject::_lookupObjId($a_ref_id));
		if($type == 'rolf')
		{
			return TRUE;
		}
		
		// title filter
		if(
				!strlen($this->filter['title']) or
				(strlen($this->filter['title']) and stristr(ilObject::_lookupTitle(ilObject::_lookupObjId($a_ref_id)), $this->filter['title']))
		)
		{
			if(
					!$this->filter['type'] or
					($this->filter['type'] == ilObject::_lookupType(ilObject::_lookupObjId($a_ref_id)))
			)
			{
			
				$counter++;
				$set[$counter]['ref_id'] = $a_ref_id;
				$set[$counter]['obj_id'] = ilObject::_lookupObjId($a_ref_id);
				$set[$counter]['type'] = ilObject::_lookupType(ilObject::_lookupObjId($a_ref_id));
				$set[$counter]['title'] = ilObject::_lookupTitle(ilObject::_lookupObjId($a_ref_id));
				$set[$counter]['description'] = ilObject::_lookupDescription(ilObject::_lookupObjId($a_ref_id));
				$set[$counter]['subobjects'] = count((array) $GLOBALS['tree']->getSubTreeIds($a_ref_id));
				$set[$counter]['depth'] = $a_level;
			}
		}
		
		// iterate through childs
		$GLOBALS['ilLog']->write(__METHOD__.': '.$a_level.' <-> '. $this->filter['level']);
		if($a_level < $this->filter['level'])
		{
			foreach($GLOBALS['tree']->getChilds($a_ref_id) as $node)
			{
				$this->parseItem($set, $counter, $a_level, $node['child']);
			}
		}
	}
	
	/**
	 * Apply Title filter
	 * @global type $ilDB
	 * @return type
	 */
	protected function applyTitleFilter()
	{
		global $ilDB;
		
		if(!strlen($this->filter['title']))
		{
			return $this->getObjects();
		}
		
		// collect obj_ids
		$obj_ids = array();
		foreach($this->getObjects() as $ref_id)
		{
			$obj_ids[ilObject::_lookupObjId($ref_id)] = $ref_id;
		}
		
		$query = 'SELECT obj_id FROM object_data '.
				'WHERE '.$ilDB->like('title','text','%'.$this->filter['title'].'%').' '.
				'AND '.$ilDB->in('obj_id',  array_keys($obj_ids),FALSE,'integer');
		$res = $ilDB->query($query);
		
		$ref_ids = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$ref_ids[] = $obj_ids[$row->obj_id];
		}
		return (array) $ref_ids;
	}
	
}
?>
