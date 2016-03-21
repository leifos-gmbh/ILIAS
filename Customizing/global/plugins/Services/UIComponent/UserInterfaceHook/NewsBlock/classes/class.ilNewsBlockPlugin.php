<?php

include_once("./Services/UIComponent/classes/class.ilUserInterfaceHookPlugin.php");
 
/**
 * News block interface plugin
 *
 * @author Andreas Gachnang <andreas.gachnang@skyguide.ch>
 * @version $Id$
 *
 */
class ilNewsBlockPlugin extends ilUserInterfaceHookPlugin
{
	function getPluginName()
	{
		return "NewsBlock";
	}
}

?>
