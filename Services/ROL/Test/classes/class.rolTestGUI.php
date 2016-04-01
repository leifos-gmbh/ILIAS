<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Test patch GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class rolTestGUI
{
	/**
	 * @var array
	 */
	protected $post = array();

	/**
	 * @var int
	 */
	protected $id = 0;

	/**
	 * @var rolTest
	 */
	protected $rol_test;

	/**
	 * Constructor
	 *
	 * @param int $a_id Test ID
	 */
	function __construct($a_id)
	{
		$this->post = $_POST;
		$this->id = $a_id;
		include_once("./Services/ROL/Test/classes/class.rolTest.php");
		$this->rol_test = new rolTest($a_id);
	}

	/**
	 * Modify properties form
	 *
	 * @param ilPropertyFormGUI $a_form
	 */
	function modifyPropertiesForm($a_form)
	{
		$is_cert_test = new ilCheckboxInputGUI("Zertifikattest", "is_certificate_test");
		$is_cert_test->setValue(1);
		$is_cert_test->setChecked($this->rol_test->getCertificateTest());
		$a_form->addItem($is_cert_test);
	}

	/**
	 * Update test
	 */
	function updateTest()
	{
		$this->rol_test->setCertificateTest((int) $this->post["is_certificate_test"]);
		$this->rol_test->update();
	}

}

?>