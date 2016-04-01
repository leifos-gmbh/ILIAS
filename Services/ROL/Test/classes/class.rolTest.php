<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *  Patch class for test object
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class rolTest
{
	/**
	 * object id
	 *
	 * @var int
	 */
	protected $id;

	/**
	 * @var bool
	 */
	protected $certificate_test = false;

	/**
	 * Constructor
	 *
	 * @param int $a_id Test ID
	 */
	function __construct($a_id)
	{
		global $ilDB;

		$this->id = $a_id;
		$this->db = $ilDB;
		if ($this->id > 0)
		{
			$this->read();
		}
	}

	/**
	 * Set certificate test
	 *
	 * @param bool $a_val is certificate test?	
	 */
	function setCertificateTest($a_val)
	{
		$this->certificate_test = $a_val;
	}
	
	/**
	 * Get certificate test
	 *
	 * @return bool is certificate test?
	 */
	function getCertificateTest()
	{
		return $this->certificate_test;
	}

	/**
	 * Read
	 */
	function read()
	{
		$set = $this->db->query($q = "SELECT * FROM tst_tests ".
			" WHERE obj_fi  = ".$this->db->quote($this->id, "integer")
		);
		$rec = $this->db->fetchAssoc($set);
//		echo $q;
//		var_dump($rec); exit;
		$this->setCertificateTest($rec["is_certificate_test"]);
	}


	/**
	 * Update
	 */
	function update()
	{
		$this->db->manipulate($q = "UPDATE tst_tests SET ".
			" is_certificate_test = ".$this->db->quote($this->getCertificateTest(), "integer").
			" WHERE obj_fi = ".$this->db->quote($this->id, "integer")
		);
	}

	/**
	 * Clone test
	 *
	 * @param int $a_new_test_id new test id
	 */
	function cloneTest($a_new_test_id)
	{
		$new_t = new rolTest($a_new_test_id);
		$new_t->setCertificateTest($this->getCertificateTest());
		$new_t->update();
	}


}

?>