<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * CD helper
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class cdUtil
{
	/**
	 * isDAF
	 *
	 * @return bool
	 */
	static function isDAF()
	{
		global $ilClientIniFile;

		return ($ilClientIniFile->readVariable("system","DAF") == "1");
	}

}

?>