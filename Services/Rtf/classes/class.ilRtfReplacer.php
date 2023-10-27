<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * RTF template placeholder replacement processor
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup 
 */
class ilRtfReplacer
{
	/**
	 * Replace placeholders with values in a template file
	 * and save everything to a target file
	 *
	 * @param array $a_phs placeholders and their replacements as key value pairs
	 * @param string $a_file filename of RTF file
	 * @return
	 */
	function processFile($a_phs, $a_template_file,
		$a_target_file)
	{
		$escapes = array ('\\' => "\\\\",
			'{'  => "\{",
			'}'  => "\}",
			'ö' => "\u246\'9a",
			'ä' => "\u228\'8a",
			'ü' => "\u252\'9f",
			'ß' => "\u223\'a7",
			'Ö' => "\u214\'85",
			'Ä' => "\u196\'80",
			'Ü' => "\u220\'86"
			);
	
		$doc = file_get_contents($a_template_file);
		if (!$doc)
		{
			return false;
		}
	
		foreach($a_phs as $key => $value)
		{
			$ph = "xx".$key."xx";
			$doc = str_replace("xx}{".$key, "xx".$key, $doc);
			$doc = str_replace($key."}{xx", $key."xx", $doc);
			
			// escape special characters in the values
			foreach ($escapes as $char => $escaped)
			{
				$value = str_replace($char, $escaped, $value);
			}
			
			$doc = str_replace($ph, $value, $doc);
		}

		$target_file = fopen($a_target_file, 'w');
		if ($target_file)
		{
			fwrite($target_file, $doc);
			fclose($target_file);
		}
	}
}
?>
