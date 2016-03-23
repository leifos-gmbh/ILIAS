<?php

require_once(PLUGIN_PATH.'/classes/class.ecttTableGUI.php');
require_once(PLUGIN_PATH.'/classes/class.ecttViewComTrack.php');

class ecttObjTrackingTableGUI extends ecttTableGUI
{
	protected $ROW_TEMPLATE = 'tpl.ectt_obj_tracking_table_row.html';

	public function  __construct($link_target, $parent_cmd)
	{
		$this->link_target = $link_target;
		$this->parent_cmd = $parent_cmd;

		global $ilCtrl, $lng;
		parent::__construct($link_target, $parent_cmd);

		$this->addColumn($lng->txt("ectt_client_id"),"client_id");
		//$this->addColumn($lng->txt("ectt_ref_id"),"ref_id");
		$this->addColumn($lng->txt("ectt_obj_title"),"obj_title");
		//$this->addColumn($lng->txt("ectt_usr_id"), "usr_id");
		$this->addColumn($lng->txt("ectt_usr_ip"),"usr_ip");
		$this->addColumn($lng->txt("ectt_time"),"time");
		$this->setEnableHeader(true);
		$this->setRowTemplate($this->ROW_TEMPLATE, PLUGIN_PATH);
		$this->setFormAction($this->link_target.'&cmd=post');
		$this->setPrefix('object_tracking');
		$this->setDefaultOrderField('client_id');
		$this->setTitle($lng->txt('ectt_object_tracking_list'));
	}

	protected function fillRow($a_set)
	{
		$time = ilDatePresentation::formatDate(new ilDateTime($a_set['time'], IL_CAL_UNIX));
		
		$this->tpl->setVariable("CLIENT_ID", $a_set['client_id']);
		$this->tpl->setVariable("OBJ_TITLE", $a_set['obj_title']);
		$this->tpl->setVariable("USR_IP",	 $a_set['usr_ip']);
		$this->tpl->setVariable("TIME",		 $time);
	}


	protected function formatField($field, $content)
	{

	}
	protected function getFieldClass($field)
	{

	}
	protected function initColumns($cols)
	{

	}
}

?>
