<?php

class ApacheCustom {

	public static function getUsername() {

		ilLoggerFactory::getLogger('auth')->debug('Original user name: ' . $_SERVER['REMOTE_USER']);
		
		$a_username = $_SERVER['REMOTE_USER'];
		if(strpos($a_username, '@') !== FALSE)
		{
			$a_username = substr($a_username,0,strpos($a_username,'@'));
		}
		ilLoggerFactory::getLogger('auth')->debug('Parsed user name: ' . $a_username);
		return $a_username;
	}
	
}