<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Badge/classes/class.ilBadge.php";

/**
 * Class ilBadgeRenderer
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 *
 * @package ServicesBadge
 */
class ilBadgeRenderer
{
	/**
	 * @var ilTemplate
	 */
	protected $tpl;

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var \ILIAS\UI\Factory
	 */
	protected $factory;

	/**
	 * @var \ILIAS\UI\Renderer
	 */
	protected $renderer;

	protected $assignment; // [ilBadgeAssignment]
	protected $badge; // [ilBadge]
	
	protected static $init; // [bool]
	
	public function __construct(ilBadgeAssignment $a_assignment = null, ilBadge $a_badge = null)
	{
		global $DIC;

		$this->tpl = $DIC["tpl"];
		$this->ctrl = $DIC->ctrl();
		$this->lng = $DIC->language();
		$this->factory = $DIC->ui()->factory();
		$this->renderer = $DIC->ui()->renderer();
		if($a_assignment)
		{
			$this->assignment = $a_assignment;					
			$this->badge = new ilBadge($this->assignment->getBadgeId());
		}
		else
		{
			$this->badge = $a_badge;
		}
	}
	
	public static function initFromId($a_id)
	{
		$id = explode("_", $_GET["id"]);
		if(sizeof($id) == 3)
		{
			$user_id = $id[0];
			$badge_id = $id[1];
			$hash = $id[2];
			
			if($user_id)
			{		
				include_once "Services/Badge/classes/class.ilBadgeAssignment.php";
				$assignment = new ilBadgeAssignment($badge_id, $user_id);
				if($assignment->getTimestamp())
				{
					$obj = new self($assignment);							
				}
			}
			else
			{
				include_once "Services/Badge/classes/class.ilBadge.php";
				$badge = new ilBadge($badge_id);
				$obj = new self(null, $badge);
			}
			if($hash == $obj->getBadgeHash())
			{
				return $obj;
			}		
		}
	}
	
	public function getHTML()
	{				
		$components = array();

		$modal = $this->factory->modal()->roundtrip(
			$this->badge->getTitle(), $this->factory->legacy($this->renderModalContent())
		)->withCancelButtonLabel("ok");
		$components[] = $modal;

		$image = $this->factory->image()->responsive($this->badge->getImagePath(), $this->badge->getTitle())
			->withAction($modal->getShowSignal());
		$components[] = $image;

		return $this->renderer->render($components);
	}
	
	public function getHref()
	{
		$ilCtrl = $this->ctrl;
		$tpl = $this->tpl;
		
		if(!self::$init)
		{
			self::$init = true;
			
			$url = $ilCtrl->getLinkTargetByClass("ilBadgeHandlerGUI", 
				"render", "", true, false);
			
			$tpl->addJavaScript("Services/Badge/js/ilBadgeRenderer.js");
			$tpl->addOnLoadCode('il.BadgeRenderer.init("'.$url.'");');
		}
				
		$hash = $this->getBadgeHash();
		
		return "#\" data-id=\"badge_".
			($this->assignment 
				? $this->assignment->getUserId()
				: "")."_".
			$this->badge->getId()."_".
			$hash;	
	}
	
	protected function getBadgeHash()
	{
		return md5("bdg-".
			($this->assignment 
				? $this->assignment->getUserId()
				: "")."-".
			$this->badge->getId());
	}
	
	public function renderModalContent()
	{
		$lng = $this->lng;
		$lng->loadLanguageModule("badge");

		$components = array();

		$image = $this->factory->image()->responsive($this->badge->getImagePath(), $this->badge->getImage());
		$components[] = $image;

		$badge_information = [
			$lng->txt("description")=>$this->badge->getDescription(),
			$lng->txt("badge_criteria")=>$this->badge->getCriteria(),
		];

		if($this->assignment)
		{
			$badge_information[$lng->txt("badge_issued_on")] = ilDatePresentation::formatDate(
				new ilDateTime($this->assignment->getTimestamp(), IL_CAL_UNIX)
			);
		}

		if($this->badge->getParentId())
		{
			$parent = $this->badge->getParentMeta();	
			if($parent["type"] != "bdga")
			{
				$parent_icon = $this->factory->icon()->custom(
					ilObject::_getIcon($parent["id"], "big", $parent["type"]), $lng->txt("obj_".$parent["type"])
				)->withSize("medium");

				$parent_icon_with_text = $this->factory->legacy($this->renderer->render($parent_icon) . $parent["title"]);
				$badge_information[$lng->txt("object")] = $parent_icon_with_text;
			}				
		}
		
		if($this->badge->getValid())
		{
			$badge_information[$lng->txt("badge_valid")] = $this->badge->getValid();
		}

		$list = $this->factory->listing()->descriptive($badge_information);
		$components[] = $list;

		return $this->renderer->render($components);
	}
}