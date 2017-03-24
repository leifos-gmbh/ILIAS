<?php
// uzk-patch: begin
require_once 'Services/PDFGeneration/classes/class.ilPDFGeneratorUtils.php';

/**
 * Class ilPortfolioPDFExport
 */
class ilPortfolioPDFExport
{

	/**
	 * @var string
	 */
	protected $html_folder_path;

	/**
	 * ilPortfolioPDFExport constructor.
	 * @param $html_folder_path
	 */
	public function __construct($html_folder_path)
	{
		$this->html_folder_path = $html_folder_path;
	}

	/**
	 * @return string
	 */
	public function generateSingleHtmlPageFromFolder()
	{
		$html_files		= array();
		$html_content	= '';
		require_once 'Modules/Portfolio/classes/class.ilPortfolioPage.php';
		foreach (glob($this->html_folder_path . '/*.html') as $filename)
		{
			if($filename != $this->html_folder_path .'/index.html')
			{
				$output_array = array();

				if(preg_match_all("/prtf_(\\d)*_blm_\\d{4}-\\d{2}\\.html/", $filename, $output_array))
				{
					$remove_parent = $this->html_folder_path . '/prtf_' . $output_array[1][0] .'.html';
					unset($html_files[$remove_parent]);
				}
				else
				{
					$html_files[$filename] =  $filename;
				}
			}
		}

		preg_match('#\/prtf_(\d+)\/#', $filename, $matches);
		$portfolio_id = $matches[1];
		$ordering = ilPortfolioPage::getAllPages($portfolio_id);
		if($ordering !== null)
		{
			$path = dirname($filename);
			foreach($ordering as $value)
			{
				$filename = $path . '/prtf_' . $value['id'] . '.html';
				if(file_exists($filename))
				{
					$html_content .= $this->getAndCleanHtml($filename);
				}
			}
		}
		else
		{
			foreach($html_files as $filename)
			{
				$html_content .= $this->getAndCleanHtml($filename);
			}
		}


		file_put_contents($this->html_folder_path . '/pdf_export.html', $html_content . $this->appendCustomCSS() . $this->injectJs());
		$this->removeJs();
		$this->addImageFiles();
		$this->copyFonts();
		ilPdfGeneratorUtils::removeWrongPathFromStyleFiles($this->html_folder_path . '/style/');
		return $this->html_folder_path . '/pdf_export.html';
	}

	protected function copyFonts()
	{
		ilUtil::makeDirParents($this->html_folder_path . '/templates/default/fonts');
		ilUtil::rCopy( self::getTemplatePath() . 'fonts', $this->html_folder_path . '/templates/default/fonts');
		ilUtil::rCopy( './templates/default/fonts', $this->html_folder_path . '/templates/default/fonts');
	}

	protected static function getTemplatePath()
	{
		// use ilStyleDefinition instead of account to get the current skin
		include_once "Services/Style/classes/class.ilStyleDefinition.php";
		if (ilStyleDefinition::getCurrentSkin() != "default")
		{
			$fname = "./Customizing/global/skin/".	ilStyleDefinition::getCurrentSkin()."/";
		}

		if($fname == "" || !dir($fname))
		{
			$fname = "./templates/default/";
		}
		return $fname;
	}
	/**
	 * @param $filename
	 * @return string
	 */
	protected function getAndCleanHtml($filename)
	{
		$html = file_get_contents($filename);
		/*
		 * <video class="ilPageVideo" controls="controls" preload="none" width="500" height="400">
		 * 		<source type="video/mp4" src="mobs/mm_262/BigBuckBunny_320x180_cut.mp4">
		 * 		<object type="application/x-shockwave-flash" width="500" height="400" data="Services/MediaObjects/media_element_2_14_2/flashmediaelement.swf">
		 * 			<param name="movie" value="Services/MediaObjects/media_element_2_14_2/flashmediaelement.swf">
		 * 			<param name="flashvars" value="controls=true&amp;file=mobs/mm_262/BigBuckBunny_320x180_cut.mp4">
		 * 		</object>
		 * </video>
		 */
		
		preg_match('/<video.*poster=\"([^"]*)\".*>/', $html, $match);
		
		if(count($match))
		{
			$poster = $match[1];
			if(strlen($poster))
			{
				$data_dir = ilUtil::getWebspaceDir();
				
				$tmp_poster = explode($data_dir, $poster);
				$mob_path = $tmp_poster[1];
				
				$preview_pic = $this->html_folder_path.''.$mob_path;
				$fake_player = '<div class="ilPosterFakePlayer" style="width:$1px;height:$2px;background-image:url('.$preview_pic.');">
				<div class="ilFakePlayerPlay"></div>
				<div class="ilFakePlayerText">"$3"</div>
				</div>';
				$html        = preg_replace('/<video.*width=\"([^"]*)\".*height=\"([^"]*)\".*>.*<source.*src=\"([^"]*)\".*type=\"([^"]*)\".*><\/video>/', $fake_player, $html);
			}
		}
		else
		{
			$fake_player = '<div class="ilFakePlayer" style="width:$1px;height:$2px;"><div class="ilFakePlayerPlay"></div><div class="ilFakePlayerText">"$3"</div></div>';
			$html        = preg_replace('/<video.*width=\"([^"]*)\".*height=\"([^"]*)\".*>.*<source.*src=\"([^"]*)\".*type=\"([^"]*)\".*><\/video>/', $fake_player, $html);
		}
		$fake_player = '<div class="ilFakePlayer" style="width:$3px;height:$1px;"><div class="ilFakePlayerPlay"></div><div class="ilFakePlayerText">"$2"</div></div>';
		$html = preg_replace('/<audio.*height=\"([^"]*)\".*src=\"([^"]*)\".*width=\"([^"]*)\"\/>/', $fake_player, $html);

		return '<div style="page-break-after:always;">' . $html . '</div>';
	}

	/**
	 * @return string
	 */
	protected function appendCustomCss()
	{
		return '<style>' . file_get_contents('./Modules/Portfolio/templates/default/portfolio_pdf_export.css') .	'</style>';
	}

	protected function removeJs()
	{
		unlink($this->html_folder_path . '/js/Basic.js');
		unlink($this->html_folder_path . '/js/ilTooltip.js');
		unlink($this->html_folder_path . '/js/ilCOPagePres.js');
		unlink($this->html_folder_path . '/js/accordion.js');
		unlink($this->html_folder_path . '/js/ilOverlay.js');
	}
	
	protected function addImageFiles()
	{
		copy('./Modules/Portfolio/templates/default/images/play_one.svg', $this->html_folder_path .'/images/play_one.svg' );
	}
	
	protected function injectJs()
	{
		//return '<script>'	. file_get_contents('./Modules/Portfolio/templates/default/portfolio_pdf_export.js') .	'</script>';
	}
}
// uzk-patch: end