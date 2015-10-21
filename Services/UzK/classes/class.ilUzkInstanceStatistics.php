<?php
// uzk-patch: begin
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilUzkInstanceStatistics
 */
class ilUzkInstanceStatistics
{
	/**
	 * @var self
	 */
	protected static $instance;

	/**
	 * @var ilDB
	 */
	protected $db;

	/**
	 * @var array
	 */
	protected $settings = array();

	/**
	 * 
	 */
	protected function __construct()
	{
		/**
		 * @var $ilDB ilDB
		 */
		global $ilDB;

		$this->db = $ilDB;

		$this->read();
	}

	/**
	 * @return self
	 */
	public static function getInstance()
	{
		if(!(self::$instance instanceof self))
		{
			self::$instance = new self();
		}
		
		return self::$instance;
	}

	/**
	 * 
	 */
	protected function read()
	{
		$query = "SELECT * FROM uzk_instance_stats";
		$res   = $this->db->query($query);
		while($row = $this->db->fetchAssoc($res))
		{
			$this->settings[$row['keyword']] = $row['value'];
		}
	}

	/**
	 * @param string $a_key
	 * @param string $a_val
	 */
	public function set($a_key, $a_val)
	{
		$this->db->replace(
			'uzk_instance_stats',
			array(
				'keyword' => array('text', $a_key)
			),
			array(
				'value' => array('text', $a_val)
			)
		);
		$this->settings[$a_key] = $a_val;
	}

	/**
	 * @param string $a_keyword
	 * @param mixed $a_default_value
	 * @return mixed
	 */
	public function get($a_keyword, $a_default_value = '')
	{
		if(isset($this->settings[$a_keyword]))
		{
			return $this->settings[$a_keyword];
		}
		else
		{
			return $a_default_value;
		}
	}
}
// uzk-patch: end