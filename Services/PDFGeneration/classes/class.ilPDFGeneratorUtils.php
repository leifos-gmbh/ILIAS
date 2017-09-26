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
	
	public static function generatePathToComplexTestHTML()
	{
		$mathJaxSetting = new ilSetting("MathJax");
		$path_to_mathjax = $mathJaxSetting->get("path_to_mathjax");
		$data_dir = ilUtil::getDataDir() . '/pdf_generation';
		$file_path = $data_dir . '/test_complex.html';
		if(!is_dir($data_dir))
		{
			ilUtil::createDirectory($data_dir);
		}
		if(file_exists($file_path))
		{
			unlink($file_path);
		}
		if(!is_dir($data_dir . '/images'))
		{
			ilUtil::createDirectory($data_dir . '/images');
			copy('Services/PDFGeneration/templates/images/5.png', $data_dir . '/images/5.png');
		}
		$string = preg_replace('/{REPLACE_MATHJAX_URL}/',$path_to_mathjax, file_get_contents('Services/PDFGeneration/templates/default/test_complex.html'));
		file_put_contents($file_path, $string);
		return $file_path;
	}

}