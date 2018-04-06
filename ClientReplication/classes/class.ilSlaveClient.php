<?php

class ilSlaveClient extends ilClientBase
{
	
	public function __construct($clientId, ilIniFile $clientIni)
	{
		$this->setId($clientId);
		$this->setIni($clientIni);
	}
	
	public function setIni(ilIniFile $clientIni)
	{
		$this->ini = $clientIni;
		return $this;
	}
	
	public function getIni()
	{
		return $this->ini;
	}
	
	public function modifyClientIniFile()
	{
		$this->getIni()->read();

		$this->getIni()->setVariable('client', 'name', $this->getName());
		$this->getIni()->setVariable('client', 'description', $this->getDescription());

		$this->getIni()->setVariable('db', 'user', $this->getDbUser());
		$this->getIni()->setVariable('db', 'pass', $this->getDbPass());
		$this->getIni()->setVariable('db', 'host', $this->getDbHost());
		$this->getIni()->setVariable('db', 'name', $this->getDbName());

		$this->getIni()->write();
	}
	
	public function brandDatabaseObjects(ilMasterClient $masterClient)
	{
		$masterNic = $masterClient->getNic();
		
		$db = $this->getDatabaseConnection();
		
		$query = "
			UPDATE		object_data
			SET			import_id = CONCAT(%s, obj_id)
		";
		
		$db->manipulateF(
			$query, array('text'), array($masterNic.'::')
		);
	}
	
	public function ensureSoapAdministrationIsEnabled()
	{
		$db = $this->getDatabaseConnection();
		
		$query = "
			SELECT		value
			FROM		settings
			WHERE		module = 'common'
			AND			keyword = 'soap_user_administration'
		";
		
		$res = $db->query($query);
		
		$soapUserAdministrationEnabled = null;
		
		while( $row = $db->fetchAssoc($res) )
		{
			$soapUserAdministrationEnabled = (int)$row['value'];
		}
		
		if( $soapUserAdministrationEnabled === null )
		{
			$query = "
				INSERT INTO		settings (
					module, keyword, value
				) VALUES (
					'common', 'soap_user_administration', '1'
				)
			";
			
			$db->manipulate($query);
		}
		elseif( $soapUserAdministrationEnabled === 0 )
		{
			$query = "
				UPDATE		settings
				SET			value = '1'
				WHERE		module = 'common'
				AND			keyword = 'soap_user_administration'
			";
			
			$db->manipulate($query);
		}
	}
	
	public function updateNicKey()
	{
		$db = $this->getDatabaseConnection();
		
		$query = "
			UPDATE		settings
			SET			value = %s
			WHERE		module = 'common'
			AND			keyword = 'nic_key'
		";
		
		$nicKey = self::buildNicKey();
		
		$db->manipulateF(
			$query, array('text'), array($nicKey)
		);
	}
	
	private static function buildNicKey()
	{
		mt_srand((double)microtime()*1000000);
		$nicKey = md5(mt_rand(100000,999999));
		return $nicKey;
	}
	
	public function registerAtNic($nicUrl)
	{
		$db = $this->getDatabaseConnection();
		
		include_once './ClientReplication/classes/class.ilClientReplicationDependencies.php';
		$dic = new ilClientReplicationDependencies();
		$dic->setDB($db);
		$GLOBALS['DIC'] = $dic;
		$GLOBALS['ilDB'] = $dic->database();
		
		
		$query = "
			DELETE FROM		settings
			WHERE			module = 'common'
			AND				keyword = 'inst_id'
		";
		
		$db->manipulate($query);
		
		$client = new ilClient($this->getId(), $db);
		$client->setExternalClientIni($this->getIni());
		$client->updateNIC($nicUrl);
		$instId = (int)$client->nic_status[2];
		
		$query = "
			INSERT INTO settings (
				module, keyword, value
			) VALUES (
				'common', 'inst_id', '$instId'
			)
		";
		
		$db->manipulate($query);

	}
}
