<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\API\Course\Int;

/**
 * Course command factory. Returns commands or other command factories
 *
 * @author killing@leifos.de
 */
interface CommandFactory extends \ILIAS\API\Int\CommandFactory
{
	/**
	 * @return \ILIAS\API\Membership\CommandFactory
	 */
	public function membership(): \ILIAS\API\Membership\Int\CommandFactory;

	/**
	 * @param string $title
	 * @param string $description
	 * @param int $parent_ref_id
	 * @return CreateCommand
	 */
	public function create(string $title, string $description, int $parent_ref_id): CreateCommand;
}