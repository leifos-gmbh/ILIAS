<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Init/classes/class.ilInitialisation.php");

/**
 *  
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id\$
 * @ingroup 
 */
class ilCDTestSaverInit extends ilInitialisation
{
	/**
	 * Init
	 */
	function init()
	{
		$this->initIliasIniFile();
		$this->determineClient();
		$this->initClientIniFile();
		$this->initDatabase();
		$this->initSettings();
		$this->buildHttpPath();
	}
	
}

?>