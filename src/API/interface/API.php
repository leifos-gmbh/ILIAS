<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\API\Int;


/**
 * Command factory. Returns commands or other command factories
 *
 * @author killing@leifos.de
 */
interface API
{
	/**
	 * Returns the course API
	 *
	 * @param int|null $ref_id
	 * @return \ILIAS\API\Course\Int\CommandFactory
	 */
	public function course(int $ref_id = null): \ILIAS\API\Course\Int\CommandFactory;

	// more entry points for domain level apis
	// ...


	/**
	 * @param Command $command
	 * @return CommandResponse
	 */
	public function dispatch(Command $command): CommandResponse;
}