<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\API\Course;
use ILIAS\API\Course\Int as I;
use ILIAS\API as API;

/**
 *
 *
 * @author killing@leifos.de
 */
class CommandFactory extends API\Int\AbstractCommandFactory implements I\CommandFactory
{
	protected $course_ref_id = null;

	/**
	 * Constructor
	 */
	public function __construct(API\Int\FactoryCollection $factory_collection, int $course_ref_id = null)
	{
		parent::__construct($factory_collection);
		$this->course_ref_id = $course_ref_id;
	}

	/**
	 * @inheritdoc
	 */
	function membership(): \ILIAS\API\Membership\CommandFactory {
		return new \ILIAS\API\Membership\CommandFactory($this->factory_collection);
	}

	/**
	 * @inheritdoc
	 */
	function create(string $title, string $description, int $parent_ref_id): CreateCommand {
		return new CreateCommand($this->factory_collection, $title, $description, $parent_ref_id);
	}
}