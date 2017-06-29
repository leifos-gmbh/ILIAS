<?php
//include_once './Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationGUI.php';
//include_once './Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationUser.php';

/**
 *
 * @author Jesús López Reyes <lopez@leifos.com>
 * @version $Id$
 *
 *
 * @ingroup ServicesCalendar
 */
class ilAppointmentPresentationFactory
{
	public static function getPresentation($a_appointment)
	{
		global $lng;

		include_once('./Services/Calendar/classes/class.ilCalendarCategoryAssignments.php');

		//get object info
		$cat_id = ilCalendarCategoryAssignments::_lookupCategory($a_appointment['event']->getEntryId());
		$cat_info = ilCalendarCategories::_getInstance()->getCategoryInfo($cat_id);
		$entry_obj_id = isset($cat_info['subitem_obj_ids'][$cat_id]) ?
			$cat_info['subitem_obj_ids'][$cat_id] :
			$cat_info['obj_id'];

		ilLoggerFactory::getRootLogger()->debug("swithc type = ".$cat_info['type']);

		switch($cat_info['type'])
		{
			case ilCalendarCategory::TYPE_OBJ:
				$type = ilObject::_lookupType($cat_info['obj_id']);
				ilLoggerFactory::getRootLogger()->debug("**** Type = ".$type);
				switch($type)
				{
					case "crs":
						require_once "./Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationCourseGUI.php";
						return ilAppointmentPresentationCourseGUI::getInstance();
						break;
					case "grp":
						require_once "./Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationGroupGUI.php";
						return ilAppointmentPresentationGroupGUI::getInstance();
						break;
					case "sess":
						require_once "./Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationSessionGUI.php";
						return ilAppointmentPresentationSessionGUI::getInstance();
						break;
					case "exc":
						include_once './Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationExerciseGUI.php';
						return ilAppointmentPresentationExerciseGUI::getInstance();
						break;
					default:
						include_once './Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationGUI.php';
						return ilAppointmentPresentationGUI::getInstance(); // title, description etc... link to generic object.
				}
				break;
			case ilCalendarCategory::TYPE_USR:
				require_once "./Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationUserGUI.php";
				return ilAppointmentPresentationUserGUI::getInstance();
				break;
			//TYPE GLOBAL uses the same code/data as TYPE_USR
			case ilCalendarCategory::TYPE_GLOBAL:
				require_once "./Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationUserGUI.php";
				return ilAppointmentPresentationUserGUI::getInstance();
				break;
			case ilCalendarCategory::TYPE_CH:
				require_once "./Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationGUI.php";
				return ilAppointmentPresentationUserGUI::getInstance();
				break;
			case ilCalendarCategory::TYPE_BOOK:
				require_once "./Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationBookingPoolGUI.php";
				return ilAppointmentPresentationBookingPoolGUI::getInstance();
			default:
				include_once './Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationGUI.php';
				return ilAppointmentPresentationGUI::getInstance();

		}
	}
}