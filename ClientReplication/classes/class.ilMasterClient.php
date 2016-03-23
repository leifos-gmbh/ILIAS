<?php

class ilMasterClient extends ilClientBase
{
	public function __construct($clientIniFile)
	{
		$this->readClientIniFile($clientIniFile);
		
		$this->readClientNicRegistration();
	}
	
	private function readClientIniFile($clientIniFile)
	{
		$ini = new ilIniFile($clientIniFile);
		$ini->read();
		
		$this->setDbUser( $ini->readVariable('db', 'user') );
		$this->setDbPass( $ini->readVariable('db', 'pass') );
		$this->setDbHost( $ini->readVariable('db', 'host') );
		$this->setDbName( $ini->readVariable('db', 'name') );
	}
	
	private function readClientNicRegistration()
	{
		$db = $this->getDatabaseConnection($this);
		
		$query = "
			SELECT	keyword, value
			FROM	settings
			WHERE	keyword IN('nic_enabled', 'inst_id')
		";
		
		$res = $db->query($query);
		
		$nicEnabled = $nicId = null;
		
		while( $row = $db->fetchAssoc($res) )
		{
			switch( $row['keyword'] )
			{
				case 'nic_enabled':		$nicEnabled = (int)$row['value'];
										break;
				case 'inst_id':			$nicId = (int)$row['value'];
										break;
			}
		}
		
		if( !$nicEnabled || !$nicId )
		{
			throw new Exception(
				"master clients nic registration is disabled or registration id ".
				"is invalid ( nicEnabled=$nicEnabled / nicId=$nicId )"
			);
		}
		
		$this->setNic($nicId);
		
		return $this;
	}
}
