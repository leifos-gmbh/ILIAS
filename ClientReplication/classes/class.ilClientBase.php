<?php

class ilClientBase
{
	private $databaseConnection = null;
	
	private $nic = null;
	
	private $id = null;
	private $name = null;
	private $description = null;
	
	private $dbHost = null;
	private $dbName = null;
	private $dbUser = null;
	private $dbPass = null;
	
	public function setId($clientId)
	{
		$this->clientId = $clientId;
		return $this;
	}
	
	public function getId()
	{
		return $this->clientId;
	}

	public function setName($clientName)
	{
		$this->clientName = $clientName;
		return $this;
	}

	public function getName()
	{
		return $this->clientName;
	}
	
	public function setDescription($clientDescription)
	{
		$this->clientDescription = $clientDescription;
		return $this;
	}
	
	public function getDescription()
	{
		return $this->clientDescription;
	}
	
	public function setNic($clientNic)
	{
		$this->clientNic = $clientNic;
		return $this;
	}
	
	public function getNic()
	{
		return $this->clientNic;
	}
	
	public function setDbHost($dbHost)
	{
		$this->dbHost = $dbHost;
		return $this;
	}
	
	public function getDbHost()
	{
		return $this->dbHost;
	}
	
	public function setDbName($dbName)
	{
		$this->dbName = $dbName;
		return $this;
	}
	
	public function getDbName()
	{
		return $this->dbName;
	}
	
	public function setDbUser($dbUser)
	{
		$this->dbUser = $dbUser;
		return $this;
	}
	
	public function getDbUser()
	{
		return $this->dbUser;
	}
	
	public function setDbPass($dbPass)
	{
		$this->dbPass = $dbPass;
		return $this;
	}
	
	public function getDbPass()
	{
		return $this->dbPass;
	}
	
	/**
	 * @return ilDB $ilDB
	 */
	public function getDatabaseConnection()
	{
		if( $this->db === null )
		{
			require_once("./Services/Database/classes/class.ilDBWrapperFactory.php");
			
			$this->db = ilDBWrapperFactory::getWrapper('mysql');
			
			$this->db->setDBUser($this->getDbUser());
			$this->db->setDBHost($this->getDbHost());
			$this->db->setDBPort(3306);
			$this->db->setDBPassword($this->getDbPass());
			$this->db->setDBName($this->getDbName());
			
			$this->db->connect();
		}
		
		return $this->db;
	}
}
