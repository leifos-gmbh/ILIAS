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
			$p = $tree->getPathFull((int) $_GET["ref_id"]);
			foreach ($p as $n)
			{
				if ($n["type"] == "crs")
				{
					include_once("./Services/ROL/Course/classes/class.rolCourse.php");
					$c = new rolCourse($n["obj_id"]);
					if ($c->getMinOnlinetime() > 0)
					{
						$ot = (int) rolCourse::lookupOnlinetimeForObjectAndUser($ilUser->getId(), $n["child"]);
						$tpl->addOnLoadCode("rolCourseTime.init(".$c->getMinOnlinetime().", ".$ot.");");
					}
				}
			}
		}
	}

}

?>