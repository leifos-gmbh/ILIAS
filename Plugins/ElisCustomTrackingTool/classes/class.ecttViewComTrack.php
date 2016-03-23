<?php

class ecttViewComTrack
{
	private $involved_objects		= array();
	private $involved_clients		= array();
	private $involved_com_types		= array();
	private $filtered_object		= null;
	private $filtered_com_type		= null;
	private $filtered_period_active = null;
	private $filtered_period_start	= null;
	private $filtered_period_end	= null;
	private $filtered_client		= null;
	private $ContentSearchtext		= null;
	private $UsernameSearchtext		= null;

	private $objects = array(
		'crs', 'exc', 'lm', 'tst', 'sahs', 'htlm'
	);

	private $com_types = array(
		'forum', 'mail', 'chat'
	);

	const OBJECT_FILTER_OPTION_ALL		= '0';
	const COM_TYPE_FILTER_OPTION_ALL	= '0';
	const CLIENT_FILTER_OPTION_ALL		= '0';


	public function getCommunicationTrackingData($orderfield = null, $orderdir = null, $offset = null)
	{
		global $ilDB;

		$data = $this->filterCommunicationTrackingData($orderfield, $orderdir, $offset);

		$result = $ilDB->queryF(
				$data['query'],
				$data['types'],
				$data['values']
		);

		$this->tracked_communications = array();

		while( $row = $ilDB->fetchAssoc($result) )
		{
			$this->tracked_communications[$row['track_id']] = array(
				'usr_login'		=> $row['usr_login'],
				'client_id'		=> $row['client_id'],
				'com_content'	=> $row['com_content'],
				'com_type'		=> $row['com_type'],
				'time'			=> $row['time']
			);
		}

		return $this->tracked_communications;
	}
	
	public function getNumberOfRows($cmd, $orderfield, $orderdir, $offset)
	{
		global $ilDB;
		
		$data = $this->$cmd($orderfield, $orderdir, $offset, true);

		$result = $ilDB->queryF(
				$data['query'],
				$data['types'],
				$data['values']
		);

		while( $row = $ilDB->fetchAssoc($result) )
		{
			$number = $row['anzahl'];
		}
		
		return $number;
	}
	
	public function filterCommunicationTrackingData($orderfield, $orderdir, $offset, $count = false)
	{
		global $ilDB, $ilUser;

		$types = $values = $selects = $wheres = array();

		$selects[] = 'track_id';

		if($count == false)
		{
			$selects[] = 'usr_login';
			$selects[] = 'client_id';
			$selects[] = 'com_content';
			$selects[] = 'com_type';
			$selects[] = 'time';
		}

		if($this->filtered_com_type != self::COM_TYPE_FILTER_OPTION_ALL)
		{
			$wheres[] = 'com_type = %s';
			$values[] = $this->filtered_com_type;
			$types[]  = 'text';
		}

		if($this->filtered_client != self::CLIENT_FILTER_OPTION_ALL)
		{
			$wheres[] = 'client_id = %s';
			$values[] = $this->filtered_client;
			$types[]  = 'text';
		}

		if($this->filtered_period_active)
		{
			$wheres[] = 'time BETWEEN %s AND %s';
			$values[] = $this->getFilteredPeriodStartTs(); $types[] = 'integer';
			$values[] = $this->getFilteredPeriodEndTs() + (60*60*24); $types[] = 'integer';
		}

		if( strlen(trim($this->ContentSearchtext)) != 0)
		{
			$wheres[] = 'com_content LIKE %s';
				$values[] = '%'.$this->ContentSearchtext.'%'; $types[] = 'text';
		}

		if( strlen(trim($this->UsernameSearchtext)) != 0)
		{
			$wheres[] = 'usr_login LIKE %s';
				$values[] = '%'.$this->UsernameSearchtext.'%'; $types[] = 'text';
		}

		$selectfields = implode(', ', $selects);

		if($count == false)
		{
			$query = 'SELECT '.$selectfields.' FROM track_com_data';
		}
		else
		{
			$query = 'SELECT COUNT('.$selectfields.') anzahl FROM track_com_data';
		}

		if( count($wheres) )
		{
			$query .= ' WHERE '.implode(' AND ', $wheres);
		}
		
		if( count($wheres) && count($orwheres) )
		{
			$query .= ' AND ('.implode(' OR ', $orwheres).')';
		}

		if($orderfield !== NULL && $orderdir !== NULL)
		{
			$query .= ' ORDER BY '.$orderfield.' '.$orderdir;
				
		}

		if($count == false && $offset !== NULL)
		{
			$query .= ' LIMIT '.$ilUser->getPref("hits_per_page").' OFFSET '.$offset;
		}

		return array('query' => $query, 'types' => $types, 'values' => $values);
	}


	public function filterObjectTrackingData($orderfield, $orderdir, $offset, $count = false)
	{
		global $ilDB, $ilUser;
		
		$types = $values = $selects = $wheres = array();

		$selects[] = 'track_id';

		if($count == false)
		{
			$selects[] = 'obj_title';
			$selects[] = 'usr_ip';
			$selects[] = 'client_id';
			$selects[] = 'time';
		}

		if($this->filtered_object != self::OBJECT_FILTER_OPTION_ALL)
		{
			$wheres[] = 'obj_type = %s';
			$values[] = $this->filtered_object;
			$types[]  = 'text';
		}

		if($this->filtered_period_active)
		{
			$wheres[] = 'time BETWEEN %s AND %s';
			$values[] = $this->getFilteredPeriodStartTs(); $types[] = 'integer';
			$values[] = $this->getFilteredPeriodEndTs() + (60*60*24); $types[] = 'integer';
		}

		$selectfields = implode(', ', $selects);

		if($count == false)
		{
			$query = 'SELECT '.$selectfields.' FROM track_obj_data';
		}
		else
		{
			$query = 'SELECT COUNT('.$selectfields.') anzahl FROM track_obj_data';
		}

		if( count($wheres) )
		{
			$query .= ' WHERE '.implode(' AND ', $wheres);
		}
		
		if($orderfield !== NULL && $orderdir !== NULL)
		{
			$query .= ' ORDER BY '.$orderfield.' '.$orderdir;

		}

		if($count == false && $offset !== NULL)
		{
			$query .= ' LIMIT '.$ilUser->getPref("hits_per_page").' OFFSET '.$offset;
		}
				
		return array('query' => $query, 'types' => $types, 'values' => $values);
	}

	public function getObjectTrackingData($orderfield = null, $orderdir = null, $offset = null)
	{
		global $ilDB;
				
		$data = $this->filterObjectTrackingData($orderfield, $orderdir, $offset);

		$result = $ilDB->queryF(
				$data['query'],
				$data['types'],
				$data['values']
		);
		
		$this->tracked_objects = array();

		while( $row = $ilDB->fetchAssoc($result) )
		{
			$this->tracked_objects[$row['track_id']] = array(
				'obj_title' => $row['obj_title'],
				'usr_ip'	=> $row['usr_ip'],
				'client_id'	=> $row['client_id'],
				'time'		=> $row['time']
			);
		}

		return $this->tracked_objects;
	}

	public function getInvolvedClients()
	{
		global $ilDB, $lng;
		
		$query = 'SELECT client_id FROM track_com_data';
		$result = $ilDB->query($query);
		
		$part	= array();
		
		while( $row = $ilDB->fetchAssoc($result) )
		{
			$part[] = $row['client_id'];
		}
		
		$this->involved_clients = array_unique($part);

		return $this->involved_clients;
	}

	public function getInvolvedObjects()
	{
		global $lng;

		foreach($this->objects as $obj_type)
		{
			$this->involved_objects[$obj_type] = $lng->txt('ectt_obj_type_'.$obj_type);
		}

		return $this->involved_objects;
	}

	public function getInvolvedComTypes()
	{
		global $lng;

		foreach($this->com_types as $com_type)
		{
			$this->involved_com_types[$com_type] = $lng->txt('ectt_com_type_'.$com_type);
		}

		return $this->involved_com_types;
	}

	public function getFilteredObject()
	{
		return $this->filtered_object;
	}
	
	public function setFilteredObject($object)
	{
		$this->filtered_object = $object;
		return $this;
	}

	public function setFilteredComType($com_type)
	{
		$this->filtered_com_type = $com_type;
		return $this;
	}

	public function getFilteredComType()
	{
		return $this->filtered_com_type;
	}

	public function setContentSearchtext($searchtext)
	{
		$this->ContentSearchtext = $searchtext;
		return $this;
	}

	public function getContentSearchtext()
	{
		return $this->ContentSearchtext;
	}

	public function setUsernameSearchtext($searchtext)
	{
		$this->UsernameSearchtext = $searchtext;
		return $this;
	}

	public function getUsernameSearchtext()
	{
		return $this->UsernameSearchtext;
	}

	public function setFilteredClient($client)
	{
		$this->filtered_client = $client;
		return $this;
	}

	public function getFilteredClient()
	{
		return $this->filtered_client;
	}

// period filter methods

	public function setFilteredPeriodActive($active)
	{
		$this->filtered_period_active = $active;
		return $this;
	}

	public function setFilteredPeriodStart($start)
	{
		$this->filtered_period_start = $start;
		return $this;
	}

	public function setFilteredPeriodEnd($end)
	{
		$this->filtered_period_end = $end;
		return $this;
	}

	public function getFilteredPeriodActive()
	{
		return $this->filtered_period_active;
	}

	public function getFilteredPeriodStartTs()
	{
		$m =  $this->filtered_period_start['date']['m'];
		$d = $this->filtered_period_start['date']['d'];
		$y = $this->filtered_period_start['date']['y'];
		return mktime(0,0,0,$m,$d,$y);
	}

	public function getFilteredPeriodEndTs()
	{
		$m =  $this->filtered_period_end['date']['m'];
		$d = $this->filtered_period_end['date']['d'];
		$y = $this->filtered_period_end['date']['y'];
		return mktime(0,0,0,$m,$d,$y);
	}

	public function getFilteredPeriod()
	{
		return array(
			'active' => $this->filtered_period_active,
			'from' => $this->getFilteredPeriodStartTs(),
			'to' => $this->getFilteredPeriodEndTs()
		);
	}
}

?>
