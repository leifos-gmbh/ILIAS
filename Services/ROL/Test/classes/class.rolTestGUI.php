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
		global $tree, $ilUser;

		$this->user = $ilUser;
		$this->tree = $tree;
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

	/**
	 * Modify start buttons
	 *
	 * @param
	 * @return
	 */
	function modifyStartButtons(&$a_buttons)
	{
		$allow_start = true;
		if ($this->rol_test->getCertificateTest())
		{

			$parent_ref = $this->tree->checkForParentType((int) $_GET["ref_id"], "crs");
			if ($parent_ref > 0)
			{

				include_once("./Services/ROL/Course/classes/class.rolCourse.php");
				$c = new rolCourse(ilObject::_lookupObjId($parent_ref));
				if ($c->getMinOnlinetime() > 0)
				{
					$ot = (int) rolCourse::lookupOnlinetimeForObjectAndUser($this->user->getId(), $parent_ref);
					if ($ot < $c->getMinOnlinetime() * 60)
					{
						$allow_start = false;
					}
				}
			}
		}
		if (!$allow_start)
		{
			ilUtil::sendInfo("<font color=\"red\"><b>ACHTUNG:<br/>Die nötige \"Onlinezeit\" ist noch nicht erreicht, sobald Sie die nötigen Stunden erreicht haben, wird der Zertifikattest automatisch zugänglich.</b></font>");
			$a_buttons = array();
		}
	}


}

?>