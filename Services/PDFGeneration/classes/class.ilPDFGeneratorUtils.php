<?php


class ilPdfGeneratorUtils
{
	public static function getTestPdfDir()
	{
		$iliasPDFTestPath      = 'data/' . CLIENT_ID . '/pdf_service/';
		if(!file_exists($iliasPDFTestPath))
		{
			mkdir($iliasPDFTestPath);
		}
		return $iliasPDFTestPath;
	}

	public static function removePrintMediaDefinitionsFromStyleFile($path)
	{
		foreach (glob($path . '*.css') as $filename)
		{
			$content = file_get_contents($filename);
			$content = preg_replace('/@media[\s]* print/', '@media nothing', $content);
			file_put_contents($filename, $content);
		}
	}

	public static function removeWrongPathFromStyleFiles($path)
	{
		foreach (glob($path . '*.css') as $filename)
		{
			$content = file_get_contents($filename);
			$content = preg_replace('/src:\surl\([\',\"](..\/)*(\S)/', "src: url(./$2", $content);
			file_put_contents($filename, $content);
		}
	}

}