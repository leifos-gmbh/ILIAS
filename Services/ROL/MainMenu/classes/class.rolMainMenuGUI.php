<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *  
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class rolMainMenuGUI
{
	/**
	 * Modify main menu
	 */
	static function modify()
	{
		global $tpl, $tree, $ilUser;

		$tpl->addJavaScript("./Services/ROL/MainMenu/js/rolCourseTimer.js");

		if ($_GET["ref_id"] > 0)
		{
			$parent_ref = $tree->checkForParentType((int) $_GET["ref_id"], "crs");

			if ($parent_ref > 0)
			{
				include_once("./Services/ROL/Course/classes/class.rolCourse.php");
				$c = new rolCourse(ilObject::_lookupObjId($parent_ref));
				if ($c->getMinOnlinetime() > 0)
				{
					$ot = (int) rolCourse::lookupOnlinetimeForObjectAndUser($ilUser->getId(), $parent_ref);
					$tpl->addOnLoadCode("rolCourseTime.init(".$c->getMinOnlinetime().", ".$ot.");");
				}
			}
		}
	}

}

?>