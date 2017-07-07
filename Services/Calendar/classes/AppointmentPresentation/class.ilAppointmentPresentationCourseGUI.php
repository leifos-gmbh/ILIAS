<?php
include_once './Services/Calendar/interfaces/interface.ilCalendarAppointmentPresentation.php';
include_once './Services/Calendar/classes/AppointmentPresentation/class.ilAppointmentPresentationGUI.php';

/**
 *
 * @author Jesús López Reyes <lopez@leifos.com>
 * @version $Id$
 *
 * @ilCtrl_IsCalledBy ilAppointmentPresentationCourseGUI: ilCalendarAppointmentPresentationGUI
 *
 * @ingroup ServicesCalendar
 */
class ilAppointmentPresentationCourseGUI extends ilAppointmentPresentationGUI implements ilCalendarAppointmentPresentation
{

	public function getHTML()
	{
		global $lng, $ilCtrl;

		$a_infoscreen = $this->getInfoScreen();
		$a_app = $this->appointment;

		//include_once('./Services/Calendar/classes/class.ilCalendarCategoryAssignments.php');
		include_once "./Modules/Course/classes/class.ilObjCourse.php";
		include_once "./Modules/Course/classes/class.ilCourseFile.php";

		$cat_id = $this->getCatId($a_app['event']->getEntryId());
		$cat_info = $this->getCatInfo($cat_id);

		//ilLoggerFactory::getRootLogger()->debug("cat_info",$cat_info);

		$crs = new ilObjCourse($cat_info['obj_id'], false);
		$files = ilCourseFile::_readFilesByCourse($cat_info['obj_id']);

		// get course ref id (this is possible, since courses only have one ref id)
		$refs = ilObject::_getAllReferences($cat_info['obj_id']);
		include_once('./Services/Link/classes/class.ilLink.php');
		$crs_ref_id = current($refs);

		$description_text = $cat_info['title'] . ", " . ilObject::_lookupDescription($cat_info['obj_id']);
		$a_infoscreen->addSection($cat_info['title']);

		if ($a_app['event']->getDescription()) {
			$a_infoscreen->addProperty($lng->txt("description"), ilUtil::makeClickable(nl2br($a_app['event']->getDescription())));
		}
		$a_infoscreen->addProperty($lng->txt(ilObject::_lookupType($cat_info['obj_id'])), $description_text);

		$a_infoscreen->addSection($lng->txt((ilOBject::_lookupType($cat_info['obj_id']) == "usr" ? "app" : ilOBject::_lookupType($cat_info['obj_id'])) . "_info"));
		$a_infoscreen->addProperty($lng->txt("crs_important_info"), $crs->getImportantInformation());
		$a_infoscreen->addProperty($lng->txt("crs_syllabus"), $crs->getSyllabus());
		if (count($files)) {
			$tpl = new ilTemplate('tpl.event_info_file.html', true, true, 'Modules/Course');
			foreach ($files as $file) {
				$tpl->setCurrentBlock("files");
				$ilCtrl->setParameter($this, 'file_id', $file->getFileId());
				$ilCtrl->setParameterByClass('ilobjcoursegui','file_id', $file->getFileId());
				$ilCtrl->setParameterByClass('ilobjcoursegui','ref_id', $crs_ref_id);
				$tpl->setVariable("DOWN_LINK",$ilCtrl->getLinkTargetByClass(array("ilRepositoryGUI","ilobjcoursegui"),'sendfile'));
				$tpl->setVariable("DOWN_NAME", $file->getFileName());
				$tpl->setVariable("DOWN_INFO_TXT", $lng->txt('crs_file_size_info'));
				$tpl->setVariable("DOWN_SIZE", $file->getFileSize());
				$tpl->setVariable("TXT_BYTES", $lng->txt('bytes'));
				$tpl->parseCurrentBlock();
				$ilCtrl->setParameterByClass('ilobjcoursegui','ref_id', $_GET["ref_id"]);
			}
			$a_infoscreen->addProperty($lng->txt("files"), $tpl->get());
		}

		// support contacts
		$parts = ilParticipants::getInstanceByObjId($cat_info['obj_id']);
		$conts = $parts->getContacts();
		if (count($conts) > 0) {
			foreach ($conts as $c) {
				include_once("./Services/User/classes/class.ilPublicUserProfileGUI.php");
				$pgui = new ilPublicUserProfileGUI($c);
				//TODO DOWNLOAD LINK CALL
				//$pgui->setBackUrl($ilCtrl->getLinkTargetByClass("ilinfoscreengui"));
				$pgui->setEmbedded(true);
				$a_infoscreen->addProperty($lng->txt("crs_mem_contacts"), $pgui->getHTML());
			}
		}

		$a_infoscreen->addProperty($lng->txt("crs_contact"), "(Can I delete this???)Text from Contact of Setting > Course Information  (do not display if not applicable)The Contact from Course Information should be swapped for Tutorial Support.");
	}
}