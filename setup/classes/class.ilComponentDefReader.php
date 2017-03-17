<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Component definition xml reader class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$ 
 */
class ilComponentDefReader
{
	/**
	 * Clear definition tables
	 */
	function clearTables()
	{
		global $ilDB;
		
		$ilDB->manipulate("DELETE FROM comp_impl_int");
	}

	/**
	 * Start tag handler
	 *
	 * @param object $a_xml_parser internal xml_parser_handler
	 * @param string $a_name element tag name
	 * @param string[] $a_attribs element attributes
	 * @param string $a_comp component
	 */
	function handlerBeginTag($a_xml_parser,$a_name,$a_attribs, $a_comp)
	{
		global $ilDB;
		
		switch ($a_name)
		{
			case "implements":
				$ilDB->manipulate("INSERT INTO comp_impl_int ".
					"(provider_component, provider_interface, consumer_component, consumer_dir, consumer_classbase) VALUES (".
					$ilDB->quote($a_attribs["component"], "text").",".
					$ilDB->quote($a_attribs["interface"], "text").",".
					$ilDB->quote($a_comp, "text").",".
					$ilDB->quote($a_attribs["dir"], "text").",".
					$ilDB->quote($a_attribs["classbase"], "text").
					")");
				break;
		}
	}
	
	/**
	 * End tag handler
	 *
	 * @param object $a_xml_parser internal xml_parser_handler
	 * @param string $a_name element tag name
	 */
	function handlerEndTag($a_xml_parser,$a_name)
	{
	}
}

?>
