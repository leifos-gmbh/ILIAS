<?php

/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * GUI patch class for course
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class rolCourseGUI
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
	 * Constructor
	 *
	 * @param int $a_id Course ID
	 */
	function __construct($a_id)
	{
		$this->post = $_POST;
		$this->id = $a_id;
		include_once("./Services/ROL/Course/classes/class.rolCourse.php");
		$this->rol_course = new rolCourse($a_id);
	}

	/**
	 * Modify info form
	 *
	 * @param ilPropertyFormGUI $a_form
	 */
	function modifyInfoForm($a_form)
	{
		// min online time
		$text = new ilNumberInputGUI('Onlinezeit','min_onlinetime');
		if ($this->rol_course->getMinOnlinetime() > 0)
		{
			$text->setValue($this->rol_course->getMinOnlinetime());
		}
		$text->setInfo('Bitte geben Sie die Onlinezeit, welche als Voraussetzung zur Zulassung zum Zertifikatstest dient, in Minuten an!');
		$text->setSize(6);
		$text->setMaxLength(6);
		$a_form->addItem($text);

		// asix key
		$ti = new ilTextInputGUI("AsiX Kurzschlüssel", "rol_asix_key");
		$ti->setValue($this->rol_course->getAsixKey());
		$ti->setMaxLength(200);
		$a_form->addItem($ti);

	}


	/**
	 * Update info
	 */
	function updateInfo()
	{
		$this->rol_course->setMinOnlinetime((int) $this->post["min_onlinetime"]);
		$this->rol_course->setAsixKey($this->post["rol_asix_key"]);
		$this->rol_course->update();
	}


}

?>