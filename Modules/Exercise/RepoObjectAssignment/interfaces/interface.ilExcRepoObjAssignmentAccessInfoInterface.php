<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface for assignment types
 *
 * @author Alex Killing <killing@leifos.de>
 */
interface ilExcRepoObjAssignmentAccessInfoInterface
{
	/**
	 * Is access granted due to exercise assignment conditions?
	 *
	 * @return int assignment id  //TODO why id and not null?
	 */
	function isGranted();

	/**
	 * Get reasons why access is not granted.
	 *
	 * @return string[]  TODO -> return type array
	 */
	function getNotGrantedReasons();
}