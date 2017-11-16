<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */
/**
 * Download appointment files can be from courses, sessions, assignments ... and all return
 * different kind of file objects or resources from different locations. Or, in case of Consultation H.
 * multiple objects can be involved. This class wants the filehandlers returning always same object type.
 * 
 * @author Jesús López Reyes <lopez@leifos.com>
 * @ingroup ServicesCalendar
 */
class ilAppointmentFileObject
{
	protected $id; // object file ID.
	protected $fs_name; // file name stored in the file system.
	protected $name; // public and safe name to show in ILIAS.
	protected $directory; // directory of the file.
	protected $ref_id; // file reference id (session materials for example)

	public function __construct($a_id = "", $a_directory, $a_fs_name, $a_name = "", $a_ref_id = "")
	{
		$this->id = $a_id;
		$this->fs_name = $a_fs_name;
		$this->directory = $a_directory;
		$this->ref_id = $a_ref_id;

		if($a_name == "") {
			$this->name = $a_fs_name;
		} else {
			$this->name = $a_name;
		}
	}

	public function getId()
	{
		return $this->id;
	}

	public function getFSName()
	{
		return $this->fs_name;
	}

	public function getDirectory()
	{
		return $this->directory;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getRefId()
	{
		return $this->ref_id;
	}
}