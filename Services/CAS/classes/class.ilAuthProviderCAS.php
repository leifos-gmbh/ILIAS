<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Authentication/classes/Provider/class.ilAuthProvider.php';
include_once './Services/Authentication/interfaces/interface.ilAuthProviderInterface.php';

/**
 * Description of class class 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de> 
 *
 */
class ilAuthProviderCAS extends ilAuthProvider implements ilAuthProviderInterface
{
	private $settings = null;
	
	/**
	 * Constructtor
	 * @param \ilAuthCredentials $credentials
	 */
	public function __construct(\ilAuthCredentials $credentials)
	{
		parent::__construct($credentials);
		$this->settings = $GLOBALS['DIC']['ilSetting'];
		
	}

	/**
	 * @return \ilSetting
	 */
	protected function getSettings()
	{
		return $this->settings;
	}

	/**
	 * try cas login
	 * @global type $phpCAS
	 * @param \ilAuthStatus $status
	 */
	public function doAuthentication(\ilAuthStatus $status)
	{
		global $phpCAS;
		
		$this->getLogger()->debug('New cas authentication try');
	
		phpCAS::setDebug();
		phpCAS::setVerbose(true);
		phpCAS::client(
			CAS_VERSION_2_0,
			$this->getSettings()->get('cas_server'),
			(int) $this->getSettings()->get('cas_port'),
			$this->getSettings()->get('cas_uri')
		);
		phpCAS::setNoCasServerValidation();
		
		phpCAS::forceAuthentication();
		$this->getCredentials()->setUsername(phpCAS::getUser());
		
		if(!strlen($this->getCredentials()->getUsername()))
		{
			$this->handleAuthenticationFail($status, 'err_wrong_login');
		}
		
		$login = ilObjUser::_checkExternalAuthAccount('cas', $this->getCredentials()->getUsername());
		$usr_id = ilObjUser::_lookupId($login);
		if(!$usr_id)
		{
			$this->getLogger()->info('Cannot find user id for external account: ' . $this->getCredentials()->getUsername());
			$this->handleAuthenticationFail($status, 'err_wrong_login');
			return false;
		}
		
		$status->setStatus(ilAuthStatus::STATUS_AUTHENTICATED);
		$status->setAuthenticatedUserId($usr_id);
		return true;
	}

}
?>