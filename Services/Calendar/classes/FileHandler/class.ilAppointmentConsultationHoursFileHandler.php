<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Calendar/interfaces/interface.ilAppointmentFileHandler.php");
include_once("./Services/Calendar/classes/FileHandler/class.ilAppointmentBaseFileHandler.php");

/**
 * Consultation Hours appointment file handler
 *
 * @author Alex Killing <killing@leifos.de>
 * @ingroup ServicesCalendar
 */
class ilAppointmentConsultationHoursFileHandler extends ilAppointmentBaseFileHandler implements ilAppointmentFileHandler
{
	/**
	 * Get files (for appointment)
	 *
	 * @param
	 * @return
	 */
	function getFiles()
	{
		$context_id = $this->appointment['event']->getContextId();

		//objects
		include_once 'Services/Booking/classes/class.ilBookingEntry.php';
		$booking = new ilBookingEntry($context_id);

		foreach($booking->getTargetObjIds() as $obj_id)
		{
			include_once("./Services/Calendar/classes/FileHandler/class.ilAppointmentFileObject.php");

			//todo the filehandler method getFiles should accept an object id to avoid get the object id twice.
			//only courses and groups available for Consultation hours.
			//only courses have files.
			if(ilObject::_lookupType($obj_id) == "crs");
			{
				include_once "./Modules/Course/classes/class.ilCourseFile.php";
				$course_files = ilCourseFile::_readFilesByCourse($obj_id);
				foreach ($course_files as $course_file)
				{
					$files[] = new ilAppointmentFileObject(
						$course_file->getFileId(),
						$course_file->getInfoDirectory(),
						$course_file->getFileId(),
						$course_file->getFileName()
					);
				}
			}
		}

	}

}

?>