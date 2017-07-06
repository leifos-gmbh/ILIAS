<?php
include_once './Services/Calendar/interfaces/interface.ilCalendarAppointmentPresentation.php';
include_once './Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationGUI.php';

/**
 *
 * @author Jesús López Reyes <lopez@leifos.com>
 * @version $Id$
 *
 * @ilCtrl_IsCalledBy ilAppointmentPresentationExerciseGUI: ilCalendarAppointmentPresentationGUI
 *
 * @ingroup ServicesCalendar
 */
class ilAppointmentPresentationExerciseGUI extends ilAppointmentPresentationGUI implements ilCalendarAppointmentPresentation
{
	/**
	 * Get singleton
	 *
	 * @return self
	 */
	public static function getInstance()
	{
		if (self::$instance === null || !(self::$instance instanceof ilAppointmentPresentationExerciseGUI)) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function addInfoScreen(ilInfoScreenGUI $a_infoscreen, $a_app)
	{
		ilLoggerFactory::getRootLogger()->debug("////// // / ////////     infoscreen EXERCISE    //////");

		global $lng, $ilCtrl;

		include_once "./Modules/Exercise/classes/class.ilObjExercise.php";

		$cat_id = $this->getCatId($a_app['event']->getEntryId());
		$cat_info = $this->getCatInfo($cat_id);

		ilLoggerFactory::getRootLogger()->debug("cat _info ==> ",$cat_info);
		ilLoggerFactory::getRootLogger()->debug("cat _id ==> ".$cat_id);

		$context = $a_app['event']->getContextId();

		ilLoggerFactory::getRootLogger()->debug("context _id ==> ".$context);



		$obj = new ilObjExercise($cat_info['obj_id'], false);

		$description_text = $cat_info['title'] . ", " . ilObject::_lookupDescription($cat_info['obj_id']);

		//Assignment title
		$a_infoscreen->addSection($a_app['event']->getPresentationTitle());

		//exercise title
		$a_infoscreen->addProperty($lng->txt("cal_origin"),$cat_info['title']);

		//TODO Now i need the course or the group

		//Assignment title information
		$a_infoscreen->addSection($lng->txt((ilOBject::_lookupType($cat_info['obj_id']) == "usr" ? "app" : ilOBject::_lookupType($cat_info['obj_id'])) . "_info"));

		//TODO Work instructions of the assignment...
		$a_infoscreen->addProperty($lng->txt("exc_instruction"), "OK I NEED THE ASSIGNMENT AND THEN GET THE WORK INSTRUCTIONS");

		return $a_infoscreen;
	}

}