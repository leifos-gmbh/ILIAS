<?php

define('PLUGIN_PATH', basename(dirname(dirname(dirname(__FILE__))))
		.'/'.basename(dirname(dirname(__FILE__))) );

require_once(PLUGIN_PATH.'/classes/class.ecttContentGUI.php');

class ElisCustomTrackingToolGUI
{
	private $cmd_class = null;
	private $default_cmd_class = 'ecttViewComTrackGUI';

	public function __construct()
	{
		$this->loadStandardTemplates();
		
		if( isset($_GET['cmdClass']) && strlen($_GET['cmdClass']) )
		{
			$this->cmd_class = $_GET['cmdClass'];
		}
		else
		{
			$this->cmd_class = $this->default_cmd_class;
		}

		$this->link_target = $this->buildLinkTarget($this->cmd_class);
	}

	public function run()
	{
		global $tpl, $lng, $ilMainMenu, $rbacreview;

		$this->tpl = $tpl;
		$this->lng = $lng;

		switch($this->cmd_class)
		{
			case 'ecttViewComTrackGUI':

				$ilMainMenu->setActive('ectt_view_com_track');

				$this->setContentHeader(
					$this->lng->txt('ectt_menu_item')
				);

				include_once(PLUGIN_PATH.'/classes/class.ecttViewComTrackGUI.php');

				$gui = new ecttViewComTrackGUI($this->link_target);
				$gui->executeCommand();

				break;
		}

		$tpl->show();

		global $ilBench;
		$ilBench->save();
	}

	private function setContentHeader($header)
	{
		$this->tpl->setVariable('HEADER', $header);
		return $this;
	}

	public function loadStandardTemplates()
	{
		global $tpl;

		$tpl->addBlockFile('CONTENT', 'content', 'tpl.adm_content.html');
		$tpl->addBlockFile('STATUSLINE', 'statusline', 'tpl.statusline.html');
		$tpl->addBlockFile('LOCATOR', 'locator', 'tpl.locator.html');

		ilUtil::infoPanel();
	}

	private function buildLinkTarget($cmd_class)
	{
		$protocol = ( isset($_SERVER['HTTPS']) ? 'https' : 'http' );

		$server = $_SERVER['SERVER_NAME'];
		$script = $_SERVER['PHP_SELF'];
		$cmdClass = 'cmdClass='.$cmd_class;

		return $protocol.'://'.$server.$script.'?'.$cmdClass;
	}

}


?>