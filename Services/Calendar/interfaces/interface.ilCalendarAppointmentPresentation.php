<?php
/**
 *
 * @author Jesús López Reyes <lopez@leifos.de>
 * @version $Id$
 *
 * @ingroup ServicesCalendar
 */
interface ilCalendarAppointmentPresentation
{
	/**
	 * @param ilToolbarGUI $toolbar
	 * @return mixed
	 */
	public function addToolbar(ilToolbarGUI $toolbar);

	public function addInfoScreen(ilInfoScreenGUI $infoscreen, $appointment);

}
