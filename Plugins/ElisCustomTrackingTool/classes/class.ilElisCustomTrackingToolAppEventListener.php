<?php

require_once 'Services/EventHandling/interfaces/interface.ilAppEventListener.php';

class ilElisCustomTrackingToolAppEventListener implements ilAppEventListener
{
	static $config_initialised = false;

	private static function initConfig()
	{
		$configfile = 'custom.ini.php';

		$ini = new ilIniFile($configfile);
		$ini->read();

		if( !file_exists($configfile) )
		{
			throw new Exception('Custom Config Error: Config File does not exist!');
		}

		define('ECTT_SOAP_URL', $ini->readVariable('soap', 'url'));
		define('ECTT_SOAP_USER', $ini->readVariable('soap', 'user'));
		define('ECTT_SOAP_PASS', $ini->readVariable('soap', 'pass'));
		define('ECTT_MASTER_CLIENT', $ini->readVariable('soap', 'client'));

		if( $err = $ini->getError() )
		{
			throw new Exception('Custom Config Error: '.$err);
		}
		else
		{
			self::$config_initialised = true;
		}
	}

	/**
	 *
	 * @global ilDB $ilDB
	 * @param <type> $a_component
	 * @param <type> $a_event
	 * @param <type> $a_parameter 
	 */
	public static function handleEvent($a_component, $a_event, $a_parameter)
	{
		if(!self::$config_initialised) self::initConfig();

		switch($a_component)
		{
			case 'Services/Tracking':
			case 'Modules/Test':
			case 'Modules/Survey':
			case 'Modules/HTMLLearningModule':
			case 'Modules/LearningModule':
			case 'Modules/ScormAicc':

				switch($a_event)
				{
					case 'trackAccess':

						header(
							'X-EcttEventHandler: Object Access Tracked ('.
							$a_component.'/'.$a_event.')'
						);
						self::trackAccess($a_parameter);

						break;
				}

				break;

			case 'Modules/Chat':
			case 'Modules/Forum':
			case 'Modules/Mail':

				switch($a_event)
				{
					case 'trackCom':

						if($a_component == 'Modules/Chat')
						{
							$a_parameter = self::fixChatMessage($a_parameter);
						}

						header(
							'X-EcttEventHandler: Communication Tracked ('.
							$a_component.'/'.$a_event.')'
						);
						self::trackCom($a_parameter);

						break;
				}
		}
	}

	private static function trackAccess($a_parameter)
	{
		$a_parameter['remote_addr'] = $_SERVER['REMOTE_ADDR'];
		
		try
		{
			ini_set('soap.wsdl_cache', 0);
			ini_set('soap.wsdl_cache_enabled', 0);

			$soap = new SoapClient(ECTT_SOAP_URL, array(
				'trace' => true, 'exceptions' => true
			));

			$sid = $soap->login(ECTT_MASTER_CLIENT, ECTT_SOAP_USER, ECTT_SOAP_PASS);

			$success = $soap->trackObjectAccessEvent($sid, serialize($a_parameter));
			
			$soap->logout($sid);

			if($success !== 1)
			{
				throw new Exception(
					'Error: could not track object access event!'
				);
			}
		}
		catch(Exception $e)
		{
			echo '<pre>';
			echo $e;
			echo '</pre>';
		}

	}

	private static function trackCom($a_parameter)
	{
		$a_parameter['remote_addr'] = $_SERVER['REMOTE_ADDR'];

		try
		{
			ini_set('soap.wsdl_cache', 0);
			ini_set('soap.wsdl_cache_enabled', 0);

			$soap = new SoapClient(ECTT_SOAP_URL, array(
				'trace' => true, 'exceptions' => true
			));

			$sid = $soap->login(ECTT_MASTER_CLIENT, ECTT_SOAP_USER, ECTT_SOAP_PASS);

			$success = $soap->trackCommunicationAccessEvent($sid, serialize($a_parameter));

			$soap->logout($sid);

			if($success !== 1)
			{
				throw new Exception(
					'Error: could not track object access event!'
				);
			}
		}
		catch(Exception $e)
		{
			echo '<pre>';
			echo $e;
			echo '</pre>';
		}
	}

	private static function fixChatMessage($a_parameter)
	{
		$reg = '/^[^<>]*?<b><font[^>]*?>.*?<\/font><\/b><font[^>]*?>(.*?)<\/font>$/';

		$found = null;

		if( !preg_match($reg, $a_parameter['com_content'], $found) )
		{
			throw new Exception(
				'Error: could not parse message text from chat message!'
			);
		}

		echo '<pre>'.print_r($found,1).'</pre>';

		$a_parameter['com_content'] = $found[1];

		return $a_parameter;
	}
}
?>
