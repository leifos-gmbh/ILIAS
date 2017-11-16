<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Calendar/interfaces/interface.ilAppointmentFileHandler.php");
include_once("./Services/Calendar/classes/FileHandler/class.ilAppointmentBaseFileHandler.php");

/**
 * Course appointment file handler
 *
 * @author Alex Killing <killing@leifos.de>
 * @ingroup ServicesCalendar
 */
class ilAppointmentCourseFileHandler extends ilAppointmentBaseFileHandler implements ilAppointmentFileHandler
{
	/**
	 * Get files (for appointment)
	 *
	 * @param
	 * @return array ilAppointmentFileObject
	 */
	function getFiles()
	{
		$cat_info = $this->getCatInfo();

		//checking permissions of the parent object.
		// get course ref id (this is possible, since courses only have one ref id)
		$refs = ilObject::_getAllReferences($cat_info['obj_id']);
		$crs_ref_id = current($refs);

		$files = array();
		if ($this->access->checkAccessOfUser($this->user->getId(), "read", "", $crs_ref_id))
		{
			include_once "./Modules/Course/classes/class.ilCourseFile.php";
			$course_files = ilCourseFile::_readFilesByCourse($cat_info['obj_id']);

			foreach ($course_files as $course_file)
			{
				/*$files[] = array(
					'source_directory' => $course_file->getInfoDirectory(),
					'source_file_name' => $course_file->getFileId(),
					'source_file_id' => $course_file->getFileId(),
					'target_file_name' => $course_file->getFileName()
				);*/

				include_once("./Services/Calendar/classes/FileHandler/class.ilAppointmentFileObject.php");
				$files[] = new ilAppointmentFileObject(
								$course_file->getFileId(),
								$course_file->getInfoDirectory(),
								$course_file->getFileId(),
								$course_file->getFileName()
							);
			}
		}

		return $files;
	}

}