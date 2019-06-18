<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\API\Course\Int;


/**
 * Course create command
 */
interface CreateCommand {


	/**
	 * @return string
	 */
	public function getTitel(): string;

	/**
	 * @return description
	 */
	public function getDescription(): string;

	/**
	 * @return parent ref id
	 */
	public function getParentRefId(): int;


	// possible mutators

}