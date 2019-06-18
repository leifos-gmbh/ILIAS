<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\API\Membership\Int;


/**
 * Course create command
 */
interface AddCommand {

	/**
	 * @return int
	 */
	public function getUserId(): int;

	/**
	 * @return int
	 */
	public function getLocalRoleId(): int;

	// possible mutators

}