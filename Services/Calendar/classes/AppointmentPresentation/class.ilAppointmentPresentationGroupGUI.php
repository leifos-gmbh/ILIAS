<?php
include_once './Services/Calendar/interfaces/interface.ilCalendarAppointmentPresentation.php';
include_once './Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationGUI.php';
include_once './Services/Calendar/classes/class.ilCalendarCategoryAssignments.php';

/**
 *
 * @author Jesús López Reyes <lopez@leifos.com>
 * @version $Id$
 *
 * @ilCtrl_IsCalledBy ilAppointmentPresentationGroupGUI: ilCalendarAppointmentPresentationGUI
 *
 * @ingroup ServicesCalendar
 */
class ilAppointmentPresentationGroupGUI extends ilAppointmentPresentationGUI implements ilCalendarAppointmentPresentation
{
	/**
	 * Get singleton
	 *
	 * @return self
	 */
	public static function getInstance()
	{
		if (self::$instance === null || !(self::$instance instanceof ilAppointmentPresentationGroupGUI)) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function addInfoScreen(ilInfoScreenGUI $a_infoscreen, $a_app)
	{
		global $lng, $ilCtrl;

		//include_once('./Services/Calendar/classes/class.ilCalendarCategoryAssignments.php');
		include_once "./Modules/Group/classes/class.ilObjGroup.php";

		$cat_id = $this->getCatId($a_app['event']->getEntryId());
		$cat_info = $this->getCatInfo($cat_id);

		$grp = new ilObjGroup($cat_info['obj_id'], false);

		$description_text = $cat_info['title'] . ", " . ilObject::_lookupDescription($cat_info['obj_id']);
		$a_infoscreen->addSection($cat_info['title']);

		if ($a_app['event']->getDescription()) {
			$a_infoscreen->addProperty($lng->txt("description"), ilUtil::makeClickable(nl2br($a_app['event']->getDescription())));
		}
		$a_infoscreen->addProperty($lng->txt(ilObject::_lookupType($cat_info['obj_id'])), $description_text);

		$a_infoscreen->addSection($lng->txt((ilOBject::_lookupType($cat_info['obj_id']) == "usr" ? "app" : ilOBject::_lookupType($cat_info['obj_id'])) . "_info"));
		$a_infoscreen->addProperty($lng->txt("crs_important_info"), $grp->getInformation());

		return $a_infoscreen;
	}

}