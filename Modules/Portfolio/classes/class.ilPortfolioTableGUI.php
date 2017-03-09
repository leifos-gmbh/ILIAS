<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Portfolio table
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ModulesPortfolio
 */
class ilPortfolioTableGUI extends ilTable2GUI
{
	protected $user_id;

	/**
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_user_id)
	{
		global $ilCtrl, $lng;

		$this->user_id = (int)$a_user_id;
	
		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setTitle($lng->txt("prtf_portfolios"));

		$this->addColumn($this->lng->txt(""), "", "1");	
		// uzk-patch: begin
		$this->addColumn($this->lng->txt("title"), "title");
		// uzk-patch: end
		$this->addColumn($this->lng->txt("online"), "is_online");
		// patch uzk start
		// $this->addColumn($this->lng->txt("prtf_default_portfolio"), "is_default");		
		// patch uzk end
		$this->addColumn($this->lng->txt("actions"));

		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.portfolio_row.html", "Modules/Portfolio");

		$this->addMultiCommand("confirmPortfolioDeletion", $lng->txt("delete"));
		$this->addCommandButton("saveTitles", $lng->txt("prtf_save_status_and_titles"));		

		$this->getItems();
		
		$lng->loadLanguageModule("wsp");
		// uzk-patch: begin
		$lng->loadLanguageModule("uzk");
		// uzk-patch: end
		
		include_once('./Services/Link/classes/class.ilLink.php');
	}

	protected function getItems()
	{
		global $ilUser;
		
		include_once "Modules/Portfolio/classes/class.ilPortfolioAccessHandler.php";
		$access_handler = new ilPortfolioAccessHandler();
		
		include_once "Modules/Portfolio/classes/class.ilObjPortfolio.php";
		$data = ilObjPortfolio::getPortfoliosOfUser($this->user_id);
		
		$this->shared_objects = $access_handler->getObjectsIShare(false);		
		
		$this->setData($data);				
	}

	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl;
		
		$this->tpl->setCurrentBlock("title_form");
		$this->tpl->setVariable("VAL_ID", $a_set["id"]);
		$this->tpl->setVariable("VAL_TITLE", ilUtil::prepareFormOutput($a_set["title"]));
		$this->tpl->parseCurrentBlock();

		if(in_array($a_set["id"], $this->shared_objects))
		{
			$this->tpl->setCurrentBlock("shared");
			$but_offline = '';
			if(!$a_set['is_online'])
			{
				$but_offline = ' '.$lng->txt('prtf_but_offline');
			}
			$this->tpl->setVariable("TXT_SHARED", $lng->txt("wsp_status_shared").$but_offline);
			
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock("chck");
		$this->tpl->setVariable("VAL_ID", $a_set["id"]);
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("edit");
		$this->tpl->setVariable("VAL_ID", $a_set["id"]);
		//	uzk-patch: begin	
		$button_html = $this->getOnOffButton($a_set["id"], $a_set['is_online']);
		
		$this->tpl->setVariable("STATUS_ONLINE", $button_html
//			($a_set["is_online"]) ? " checked=\"checked\"" : ""
		);
		//	uzk-patch: end	
		
		$this->tpl->setVariable("VAL_DEFAULT",
			($a_set["is_default"]) ? $lng->txt("yes") : "");
		$this->tpl->parseCurrentBlock();
		
		$prtf_path = array(get_class($this->parent_obj), "ilobjportfoliogui");

		$ilCtrl->setParameterByClass("ilobjportfoliogui", "prt_id", $a_set["id"]);
		$this->tpl->setCurrentBlock("action");

		$this->tpl->setVariable("URL_ACTION",
			$ilCtrl->getLinkTargetByClass($prtf_path, "preview"));
		$this->tpl->setVariable("TXT_ACTION", $lng->txt("user_profile_preview"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setVariable("URL_ACTION",
			$ilCtrl->getLinkTargetByClass($prtf_path, "view"));
		$this->tpl->setVariable("TXT_ACTION", $lng->txt("prtf_edit_portfolio"));
		$this->tpl->parseCurrentBlock();
		
		//	uzk-patch: begin
		// Do not move! This is needed right here!
		$share_path = array(get_class($this->parent_obj), "ilobjportfoliogui", 'ilworkspaceaccessgui');
		$this->tpl->setVariable("URL_ACTION",
			$ilCtrl->getLinkTargetByClass($share_path, "share"));
		$this->tpl->setVariable("TXT_ACTION", $lng->txt("prtf_share"));
		$this->tpl->parseCurrentBlock();
		//	uzk-patch: end
		
		$ilCtrl->setParameterByClass("ilobjportfoliogui", "prt_id", "");		

		/* 
		// patch uzk start
		if($a_set["is_online"])
		{
			if(!$a_set["is_default"])
			{
				$ilCtrl->setParameter($this->parent_obj, "prt_id", $a_set["id"]);	
				
				$this->tpl->setVariable("URL_ACTION",
					$ilCtrl->getLinkTarget($this->parent_obj, "setDefaultConfirmation"));
				$this->tpl->setVariable("TXT_ACTION", $lng->txt("prtf_set_as_default"));
				$this->tpl->parseCurrentBlock();
				
				$ilCtrl->setParameter($this->parent_obj, "prt_id", "");	
			}
			else
			{
				$this->tpl->setVariable("URL_ACTION",
					$ilCtrl->getLinkTarget($this->parent_obj, "unsetDefault"));
				$this->tpl->setVariable("TXT_ACTION", $lng->txt("prtf_unset_as_default"));
				$this->tpl->parseCurrentBlock();
			}
		}			 
		// patch uzk end 
		*/
	}
	//	uzk-patch: begin	
	protected function getOnOffButton($pf_id, $is_online)
	{
		global $ilCtrl;
		
		include_once './Services/UIComponent/Button/classes/class.ilLinkButton.php';
		$button = ilLinkButton::getInstance();
		$txt_status =  $is_online == true ? $this->lng->txt('online') : $this->lng->txt('offline');
				
		$button->setId('pfid_'.$pf_id.'_'.$is_online);
		$ilCtrl->setParameter($this->parent_obj, "pf_id", $pf_id);
		$ilCtrl->setParameter($this->parent_obj, "is_online", $is_online);
		$button->setUrl($ilCtrl->getLinkTarget($this->parent_obj, "saveStatus", "", true, false));

		$button->setCaption($txt_status, false);
		
		return  $button->render();
	}
	//	uzk-patch: end
}?>
