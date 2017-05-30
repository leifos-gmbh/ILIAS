<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *  
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id\$
 * @ingroup 
 */
class ilLayoutPatch
{
	/**
	 * patch
	 *
	 * @param
	 * @return
	 */
	static function patch()
	{
		global $tpl;

		$tpl->setLeftContent("");

		$ctpl = new ilTemplate("tpl.layout_patch.html", true, true, "Services/_LayoutPatch");
		$ctpl->setVariable("DUMMY", "test");
		$tpl->setLeftNavContent($ctpl->get());
	}
	
}

?>