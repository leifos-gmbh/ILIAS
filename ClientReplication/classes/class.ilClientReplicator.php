<?php

/* master nic = 5607 */

	
class ilClientReplicator
{
	const ILIAS_NIC_SERVER_URL = 'http://www.ilias.de/ilias-nic/index.php';
	
	const ILIAS_INTERNAL_DATA_SUBDIR = 'data';
	
	const ILIAS_CLIENT_INI_FILENAME = "client.ini.php";
	
	const DB_CREATE_QUERY = 'CREATE DATABASE %s CHARACTER SET utf8 COLLATE utf8_unicode_ci';

	private $iliasBasePath = null;
	private $iliasExternalDataPath = null;
	private $iliasMasterClientId = null;
	
	private $dbAdminUser = null;
	private $dbAdminPass = null;
	
	private $masterClient = null;
	
	private $slaveClients = array();
	
	public function __construct()
	{
		$this->includeCommonClasses();
	}
	
	private function includeCommonClasses()
	{
		require_once('include/inc.get_pear.php');
		require_once('include/inc.debug.php');

		require_once('setup/classes/class.ilClient.php');

		require_once('Services/Init/classes/class.ilErrorHandling.php');
		require_once('Services/Init/classes/class.ilIniFile.php');
		
		require_once('Services/Exceptions/classes/class.ilException.php');
		
		require_once('ClientReplication/classes/class.ilClientBase.php');
		require_once('ClientReplication/classes/class.ilSlaveClient.php');
		require_once('ClientReplication/classes/class.ilMasterClient.php');
		
		require_once('ClientReplication/exceptions/class.ilClientReplicationException.php');
		require_once('ClientReplication/exceptions/class.ilClientReplicationConfigException.php');
		require_once('ClientReplication/exceptions/class.ilClientReplicationFailedReplicationException.php');
	}
	
	private function readConfiguration($configFilePath)
	{
		if( !file_exists($configFilePath) ) throw new ilClientReplicationConfigException(
				'Config file "config.ini.php" does not exist! '.
				'An example config "config.ini.dist.php" is provided.'
		);
		
		if( !is_readable($configFilePath) ) throw new ilClientReplicationConfigException(
				'Config file "config.ini.php" is not readable by the webserver!'
		);
		
		$ini = new ilIniFile($configFilePath);
		$ini->read();
		
		$errors = array();
		
		$this->setIliasBasePath( $ini->readVariable('MAIN', 'ILIAS_BASE_PATH') );
		if( $ini->getError() ) throw new ilClientReplicationConfigException('Error in config file "config.ini.php": '.$ini->getError());

		$this->setIliasExternalDataPath( $ini->readVariable('MAIN', 'ILIAS_EXTERN_DATA') );
		if( $ini->getError() ) throw new ilClientReplicationConfigException('Error in config file "config.ini.php": '.$ini->getError());
		
		$this->setIliasMasterClientId( $ini->readVariable('MAIN', 'ILIAS_MASTER_CLIENT_ID') );
		if( $ini->getError() ) throw new ilClientReplicationConfigException('Error in config file "config.ini.php": '.$ini->getError());
		
		$this->setDbAdminUser( $ini->readVariable('MAIN', 'MYSQL_ADMIN_USER') );
		if( $ini->getError() ) throw new ilClientReplicationConfigException('Error in config file "config.ini.php": '.$ini->getError());
		
		$this->setDbAdminPass( $ini->readVariable('MAIN', 'MYSQL_ADMIN_PASS') );
		if( $ini->getError() ) throw new ilClientReplicationConfigException('Error in config file "config.ini.php": '.$ini->getError());
		
		$i = 0;

		while( $ini->groupExists($section = 'CLIENT_'.++$i) )
		{
			$clientId = $ini->readVariable($section, 'CLIENT_ID');
			if( $ini->getError() ) throw new ilClientReplicationConfigException('Error in config file "config.ini.php": '.$ini->getError());
			
			$clientIni = new ilIniFile(
				$this->getIliasInternalDataPath($clientId).'/'.self::ILIAS_CLIENT_INI_FILENAME
			);
			
			$slaveClient = new ilSlaveClient($clientId, $clientIni);
			
			if( $ini->variableExists($section, 'CLIENT_DESCRIPTION') )
			{
				$slaveClient->setDescription( $ini->readVariable($section, 'CLIENT_DESCRIPTION') );
			}
			
			$slaveClient->setDbHost( $ini->readVariable($section, 'MYSQL_SERVER') );
			if( $ini->getError() ) throw new ilClientReplicationConfigException('Error in config file "config.ini.php": '.$ini->getError());
			
			$slaveClient->setDbUser( $ini->readVariable($section, 'MYSQL_USER') );
			if( $ini->getError() ) throw new ilClientReplicationConfigException('Error in config file "config.ini.php": '.$ini->getError());
			
			$slaveClient->setDbPass( $ini->readVariable($section, 'MYSQL_PASS') );
			if( $ini->getError() ) throw new ilClientReplicationConfigException('Error in config file "config.ini.php": '.$ini->getError());
			
			if( $ini->variableExists($section, 'MYSQL_DB_NAME') )
			{
				$slaveClient->setDbName( $ini->readVariable($section, 'MYSQL_DB_NAME') );
			}
			else
			{
				$slaveClient->setDbName( $slaveClient->getId() );
			}
			
			$this->addSlaveClient($slaveClient);
		}
		
		if( $i == 1 ) throw new ilClientReplicationConfigException(
				'No slave clients were configured in the congig file "config.ini.php"'
		);
		
		return $this;
	}

	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function run($configFilePath)
	{
		try
		{
			$this->readConfiguration($configFilePath);

			$internalMasterClientDataPath = $this->getIliasInternalDataPath($this->getIliasMasterClientId());

			$this->setMasterClient(
					new ilMasterClient($internalMasterClientDataPath.'/'.self::ILIAS_CLIENT_INI_FILENAME)
			);

			$failedReplicationExceptions = array();
			
			foreach( $this->getSlaveClients() as $slaveClient )
			{
				try
				{
					echo "replicate client <{$this->getIliasMasterClientId()}> to client <{$slaveClient->getId()}> ... ";

					// Check if client is already created
					if($this->isAlreadyReplicated($slaveClient))
					{
						echo "\n".$slaveClient->getId().' is already replicated'."\n";
						continue;
					}

					$this->replicateClientDataDirectories($slaveClient);

					$slaveClient->modifyClientIniFile();

					$this->replicateClientDatabase($this->getMasterClient(), $slaveClient);

					$slaveClient->brandDatabaseObjects($this->getMasterClient());

					$slaveClient->ensureSoapAdministrationIsEnabled();

					$slaveClient->updateNicKey();

					$slaveClient->registerAtNic(self::ILIAS_NIC_SERVER_URL);

					echo "[ OK ]\n";
				}
				catch(ilClientReplicationFailedReplicationException $e)
				{
					echo "Error while replicating client with ID {$slaveClient->getId()}: {$e->getMessage()}\n";
					
					continue;
				}
			}
		}
		catch(ilClientReplicationException $e)
		{
			echo "{$e->getMessage()}\n";
		}
	}

	private function isAlreadyReplicated(ilSlaveClient $slaveClient)
	{
		$internalSlaveClientDataPath = $this->getIliasInternalDataPath($slaveClient->getId());

		if(is_dir($internalSlaveClientDataPath))
		{
			return true;
		}
		return false;
	}
	
	private function replicateClientDataDirectories(ilSlaveClient $slaveClient)
	{
		$internalMasterClientDataPath = $this->getIliasInternalDataPath($this->getIliasMasterClientId());
		$externalMasterClientDataPath = $this->getIliasExternalDataPath($this->getIliasMasterClientId());

		$internalSlaveClientDataPath = $this->getIliasInternalDataPath($slaveClient->getId());
		$externalSlaveClientDataPath = $this->getIliasExternalDataPath($slaveClient->getId());
		
		self::copyFilesystemDirectory($internalMasterClientDataPath, $internalSlaveClientDataPath);

		self::copyFilesystemDirectory($externalMasterClientDataPath, $externalSlaveClientDataPath);
	}
	
	private function replicateClientDatabase(ilMasterClient $masterClient, ilSlaveClient $slaveClient)
	{
		self::createNewDatabase(
				$this->getDbAdminUser(),
				$this->getDbAdminPass(),
				$slaveClient->getDbHost(),
				$slaveClient->getDbName()
		);
		
		self::copyDatabaseContent(
			$masterClient->getDbUser(), $masterClient->getDbPass(), $masterClient->getDbHost(), $masterClient->getDbName(),
			$slaveClient->getDbUser(), $slaveClient->getDbPass(), $slaveClient->getDbHost(), $slaveClient->getDbName()
		);
	}
	
	public function registerReplicatedClient(ilSlaveClient $slaveClient)
	{
		
	}
	
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	private static function copyFilesystemDirectory($sourceDirectoryPath, $destinationDirectoryPath, $subDirectoryPath = '/')
	{
		mkdir($destinationDirectoryPath.$subDirectoryPath);
		
		$dirList = opendir($sourceDirectoryPath.$subDirectoryPath);
		
	  	while( false !== ($itemName = readdir($dirList)) )
		{
			$sourceItemPath = $sourceDirectoryPath.$subDirectoryPath.$itemName;

			switch( true )
			{
				case $itemName == '.' || $itemName == '..':
					
					break;
				
				case !is_readable($sourceItemPath):
					
					throw new Exception("could not read '$sourceItemPath' due to denied permission");
				
				case is_file($sourceItemPath):
					
					$destinationItemPath = $destinationDirectoryPath.$subDirectoryPath.$itemName;
					
					copy($sourceItemPath, $destinationItemPath);
					
					break;
				
				case is_dir($sourceItemPath):
					
					self::copyFilesystemDirectory(
						$sourceDirectoryPath, $destinationDirectoryPath, $subDirectoryPath.$itemName.'/'
					);
					
					break;
				
				default:
					
					throw new ilClientReplicationFailedReplicationException(
						"Failed copy '$sourceItemPath' for unknown reason!"
					);
			}
		}
		
		closedir($dirList);
	}
	
	private static function createNewDatabase($dbAdminUser, $dbAdminPass, $dbHost, $dbName)
	{
		$dbCreateQuery = self::buildDbCreateQuery($dbName);
		//TODO: need some changes here to make it compatible with windows
		$command = sprintf(
				'echo "%s;" | mysql -h %s -u%s -p%s',
				$dbCreateQuery, $dbHost, $dbAdminUser, $dbAdminPass
		);
		
		$return = null;
		
		$output = system($command, $return);
		
		if($return != 0)
		{
			throw new ilClientReplicationFailedReplicationException("Failed to perform system command '$output'!");
		}
	}
	
	private static function buildDbCreateQuery($dbName)
	{
		return sprintf(self::DB_CREATE_QUERY, $dbName);
	}
	
	private static function copyDatabaseContent($srcDbUser, $srcDbPass, $srcDbHost, $srcDbName, $destDbUser, $destDbPass, $destDbHost, $destDbName)
	{
		$command = sprintf(
				'mysqldump -h %s -u%s -p%s %s | mysql -h %s -u%s -p%s %s',
				$srcDbHost, $srcDbUser, $srcDbPass, $srcDbName,
				$destDbHost, $destDbUser, $destDbPass, $destDbName
		);
		
		$return = null;
		
		$output = system($command, $return);
		
		if($return != 0)
		{
			throw new Exception("failed to perform system command: $output");
		}
	}
	
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	private function setIliasBasePath($iliasBasePath)
	{
		$this->iliasBasePath = $iliasBasePath;
		return $this;
	}
	
	private function getIliasBasePath()
	{
		return $this->iliasBasePath;
	}
	
	private function getIliasInternalDataPath($clientId = null)
	{
		$iliasInternalDataPath = $this->getIliasBasePath().'/'.self::ILIAS_INTERNAL_DATA_SUBDIR;
		
		if( $clientId === null )
		{
			return $iliasInternalDataPath;
		}
		
		return $iliasInternalDataPath.'/'.$clientId;
	}
	
	private function setIliasExternalDataPath($iliasExternalDataPath)
	{
		$this->iliasExternalDataPath = $iliasExternalDataPath;
		return $this;
	}
	
	private function getIliasExternalDataPath($clientId = null)
	{
		if( $clientId === null )
		{
			return $this->iliasExternalDataPath;
		}
		
		return $this->iliasExternalDataPath.'/'.$clientId;
	}
	
	private function setIliasMasterClientId($iliasMasterClientId)
	{
		$this->iliasMasterClientId = $iliasMasterClientId;
		return $this;
	}
	
	private function getIliasMasterClientId()
	{
		return $this->iliasMasterClientId;
	}
	
	private function setDbAdminUser($dbAdminUser)
	{
		$this->dbAdminUser = $dbAdminUser;
		return $this;
	}
	
	private function getDbAdminUser()
	{
		return $this->dbAdminUser;
	}
	
	private function setDbAdminPass($dbAdminPass)
	{
		$this->dbAdminPass = $dbAdminPass;
		return $this;
	}
	
	private function getDbAdminPass()
	{
		return $this->dbAdminPass;
	}
	
	private function setMasterClient(ilMasterClient $masterClient)
	{
		$this->masterClient = $masterClient;
		return $this;
	}
	
	private function getMasterClient()
	{
		return $this->masterClient;
	}
	
	private function addSlaveClient(ilSlaveClient $slaveClient)
	{
		$this->slaveClients[ $slaveClient->getId() ] = $slaveClient;
		return $this;
	}
	
	private function getSlaveClients()
	{
		return $this->slaveClients;
	}
}
