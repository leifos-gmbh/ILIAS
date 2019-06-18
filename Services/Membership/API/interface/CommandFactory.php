<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\API\Membership\Int;

/**
 * Course command factory. Returns commands or other command factories
 *
 * @author killing@leifos.de
 */
interface CommandFactory extends \ILIAS\API\Int\CommandFactory
{

	/**
	 * @param int $user_id
	 * @param int $local_role_id
	 * @return AddCommand
	 */
	public function add(int $user_id, int $local_role_id): AddCommand;

}