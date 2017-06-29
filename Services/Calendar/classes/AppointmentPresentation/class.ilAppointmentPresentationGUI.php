<?php
include_once './Services/Calendar/interfaces/interface.ilCalendarAppointmentPresentation.php';

/**
 * @author Jesús López Reyes <lopez@leifos.com>
 * @version $Id$
 *
 *
 * @ingroup ServicesCalendar
 */

class ilAppointmentPresentationGUI implements ilCalendarAppointmentPresentation
{
	protected static $instance; // [ilCalendarAppointmentPresentationFactory]
	protected $toolbar;
	//protected $infoscreen;

	/**
	 * Get singleton
	 *
	 * @return self
	 */
	public static function getInstance()
	{
		if(self::$instance === null)
		{
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * @param ilToolbarGUI $a_toolbar
	 */
	public function addToolbar(ilToolbarGUI $a_toolbar)
	{
		$this->toolbar = $a_toolbar;
		//$clone = clone $this;
		//$clone->toolbar = $toolbar;
		//return $clone;
	}

	/**
	 * @return ilToolbarGUI
	 */
	public function getToolbar()
	{
		return $this->toolbar;
	}

	/**
	 * @param ilInfoScreenGUI $a_infoscreen
	 */
	public function addInfoScreen(ilInfoScreenGUI $a_infoscreen, $a_app)
	{
		$this->infoscreen = $a_infoscreen;
	}

	/**
	 * @return ilInfoScreenGUI
	 */
	public function getInfoScreen()
	{
		return $this->infoscreen;
	}

	public function getCatId($a_entry_id)
	{
		return ilCalendarCategoryAssignments::_lookupCategory($a_entry_id);
	}

	public function getCatInfo($a_cat_id)
	{
		$cat_info = ilCalendarCategories::_getInstance()->getCategoryInfo($a_cat_id);
		$entry_obj_id = isset($cat_info['subitem_obj_ids'][$cat_id]) ?
			$cat_info['subitem_obj_ids'][$cat_id] :
			$cat_info['obj_id'];
		return $cat_info;
	}

	//TODO : SOME ELEMENTS CAN GET CUSTOM METADATA
}