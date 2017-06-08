<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';

/**
* Object selection of delete progress
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ingroup ServicesTracking
*/
class ilResetProgressSelectionTableGUI extends ilTable2GUI
{
	
	private $valid_types = array('lm','htlm','tst','exc');
	
	private $selected_users = array();

	/**
	 * 
	 * @param object $a_parent_class
	 * @param string $a_parent_cmd
	 * @return 
	 */
	public function __construct($a_parent_class,$a_parent_cmd)
	{
		global $lng,$ilCtrl,$ilUser,$objDefinition;
		
		parent::__construct($a_parent_class,$a_parent_cmd);
		
		$this->lng = $lng;
		$this->lng->loadLanguageModule('trac');
		$this->ctrl = $ilCtrl;
		
		$this->setTitle($this->lng->txt('trac_reset_progress_select_resources'));
		
		$this->addColumn('','','1px');
		$this->addColumn($this->lng->txt('title'),'title','100%');
		
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($this->getParentObject()));
		$this->setRowTemplate("tpl.trac_reset_progress_selection_row.html", "Services/Tracking");
		$this->setEnableTitle(true);
		$this->setEnableNumInfo(true);
		$this->setSelectAllCheckbox('items');
		$this->setLimit(999);
		
		$this->setFormName('cmd');
		
		$this->addCommandButton('resetMembersProgress', $this->lng->txt('trac_btn_reset_progress'));
		#$this->addCommandButton($a_parent_cmd, $this->lng->txt('cancel'));
	}
	
	/**
	 * Set selected users and store in hidden field
	 * @param type $a_users
	 */
	public function storeSelectedUsers($a_users)
	{
		$this->addHiddenInput('todelete', ilUtil::prepareFormOutput(serialize((array) $a_users)));
	}


	/**
	 * Fill row
	 * @param type $s
	 */
	public function fillRow($s)
	{
		$this->tpl->setVariable('REF_ID',$s['ref_id']);
		
		for($i = 0; $i < $s['depth']; $i++)
		{
			$this->tpl->touchBlock('padding');
			$this->tpl->touchBlock('end_padding');
		}
		$this->tpl->setVariable('TREE_IMG',ilUtil::getImagePath('icon_'.$s['type'].'.svg'));
		$this->tpl->setVariable('TREE_ALT_IMG',$this->lng->txt('obj_'.$s['type']));
		$this->tpl->setVariable('TREE_TITLE',$s['title']);
	}
	
	/**
	 * parse tree
	 * @param object $a_source
	 * @return 
	 */
	public function parseContainer($a_source)
	{
		global $tree, $ilAccess;
		
		$first = true;
		foreach($tree->getSubTree($root = $tree->getNodeData($a_source)) as $node)
		{
			if($first)
			{
				$min_depth = $node['depth'];
				$first = false;
			}
			
			
			if($node['type'] == 'rolf')
			{
				continue;
			}
			
			if(!$this->isSelectable($node['type']))
			{
				continue;
			}

			$r = array();
			$r['depth'] = $node['depth'] - $min_depth;
			$r['ref_id'] = $node['child'];
			$r['type'] = $node['type'];
			$r['title'] = $node['title'];
			
			
			$rows[] = $r;
		}
		$this->setData((array) $rows);
	}	
	
	
	/**
	 * Check if item is selectable
	 */
	protected function isSelectable($a_type)
	{
		if($GLOBALS['objDefinition']->isContainer($a_type))
		{
			return true;
		}
		return in_array($a_type, $this->valid_types);
	}
	
}
?>