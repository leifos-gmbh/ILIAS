<?php

/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Factory for user action contexts
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesUser
 */
class ilUserActionContextFactory
{
	/**
	 * Get all action contexts
	 *
	 * @return array[ilUserActionContext] all providers
	 */
	static function getAllActionContexts()
	{
		include_once("./Services/Component/classes/class.ilComponent.php");
		$contexts = array();

		foreach (ilComponent::getInterfaceImplementers("Services/User", "ActionContext") as $p)
		{
			$dir = (isset($p["consumer_component"]))
				? $p["consumer_component"]
				: "classes";
			$class = "il".$p["consumer_classbase"]."UserActionContext";
			include_once("./".$p["consumer_component"]."/".$dir."/class.".$class.".php");
			$contexts[] = new $class();
		}

		return $contexts;
	}

}

?>