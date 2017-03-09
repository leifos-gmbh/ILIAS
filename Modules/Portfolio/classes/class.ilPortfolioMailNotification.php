
<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Mail/classes/class.ilMailNotification.php';

/**
 * uzk-patch: begin
 * 
 * Class ilPortfolioMailNotification
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilPortfolioMailNotification extends ilMailNotification
{
	const TYPE_SHARE_CRS 		= 10;
	const TYPE_SHARE_GRP 		= 20;
	const TYPE_SHARE_USERS 		= 30;
	
	/**
	 * @var $wsp_access_handler null | ilWorkspaceAccessHandler
	 */
	protected $wsp_access_handler = null;
	
	public $wsp_node_id = 0;
	
	public function __construct($a_is_personal_workspace)
	{
		parent::__construct($a_is_personal_workspace);
		$this->language->loadLanguageModule('uzk');
		$this->language->loadLanguageModule('wsp');
	}
	
	public function send()
	{
		global $ilUser;
		
		// personal workspace
		if(method_exists($this->getWspAccessHandler(), "getTree"))
		{
			$tree = $this->getWspAccessHandler()->getTree();
			$obj_id = $tree->lookupObjectId($this->wsp_node_id);
			
			$this->createPermanentLink();
			$link = $this->getWspAccessHandler()->getGotoLink($this->wsp_node_id, $obj_id);
		}
		// portfolio
		else
		{
			$obj_id = $this->wsp_node_id;

			include_once "Services/Link/classes/class.ilLink.php";
			$link = ilLink::_getStaticLink($obj_id, "prtf");
		}
		$obj_type = ilObject::_lookupType($obj_id);
		$obj_title = ilObject::_lookupTitle($obj_id);
		
		switch($this->getType())
		{
			case self::TYPE_SHARE_CRS:
			case self::TYPE_SHARE_GRP:
			case self::TYPE_SHARE_USERS:
				foreach($this->getRecipients() as $rcp)
				{
					$this->initMail();
					$this->getUserLanguage($rcp);
					$this->setSubject(sprintf($this->getLanguageText('wsp_share_notification_subject'), $obj_title));
					
					$this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
					$this->appendBody("\n\n");
					$this->appendBody(sprintf($this->getLanguageText('prtf_shared_usr_body'), ilObjUser::_lookupFullname($ilUser->getId())).":");
				
					$this->appendBody("\n");
					$this->appendBody($this->getLanguageText('obj_'.$obj_type).': '.$obj_title);
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('wsp_share_notification_link').": ".$link);
					
					// direct download
					if($obj_type == "file")
					{
						$dl_link = $this->getWspAccessHandler()->getGotoLink($this->wsp_node_id, $obj_id, "_download");
						$this->appendBody($this->getLanguageText('wsp_share_notification_download_link').": ".$dl_link);
					}
					$this->appendBody("\n\n");
					
					$this->appendBody(ilMail::_getInstallationSignature());
					$this->sendMail(array($rcp), array('system'));
				}
				break;
		}
	}
	
	/**
	 * @return null
	 */
	public function getWspAccessHandler()
	{
		return $this->wsp_access_handler;
	}
	
	/**
	 * @param null $wsp_access_handler 
	 */
	public function setWspAccessHandler($wsp_access_handler)
	{
		$this->wsp_access_handler = $wsp_access_handler;
	}
	
	/**
	 * @return int
	 */
	public function getWspNodeId()
	{
		return $this->wsp_node_id;
	}
	
	/**
	 * @param int $wsp_node_id
	 */
	public function setWspNodeId($wsp_node_id)
	{
		$this->wsp_node_id = $wsp_node_id;
	}
}