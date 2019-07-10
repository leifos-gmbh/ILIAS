<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Service (e.g. being used in a course) UI wrapper for booking objects
 *
 * @author killing@leifos.de
 * @ingroup ModulesBookingManager
 */
class ilBookingObjectServiceGUI extends ilBookingObjectGUI
{
	/**
	 * ilBookingObjectServiceGUI constructor.
	 * @param int $host_obj_ref_id Host object ref id (e.g. course)
	 */
	public function __construct(int $host_obj_ref_id)
	{
		parent::__construct(null);
	}

}