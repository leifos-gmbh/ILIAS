<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\API\Course;
use ILIAS\API\Course\Int as I;
use ILIAS\API as API;

/**
 * Course create command
 */
class CreateCommand extends \ILIAS\API\Int\AbstractCommand implements I\CreateCommand {

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var string
	 */
	protected $description;

	/**
	 * @var int
	 */
	protected $parent_ref_id;

	/**
	 * CreateCommand constructor.
	 * @param API\Int\FactoryCollection $factory_collection
	 * @param string $title
	 * @param string $description
	 * @param int $parent_ref_id
	 */
	public function __construct(API\Int\FactoryCollection $factory_collection, string $title, string $description, int $parent_ref_id) {
		parent::__construct($factory_collection);
		$this->title = $title;
		$this->description = $description;
		$this->parent_ref_id = $parent_ref_id;
	}

	/**
	 * @inheritdoc
	 */
	public function getTitel() : string {
		return $this->title;
	}

	/**
	 * @inheritdoc
	 */
	public function getDescription() : string {
		return $this->description;
	}

	/**
	 * @inheritdoc
	 */
	public function getParentRefId() : int {
		return $this->parent_ref_id;
	}


	// possible mutators

}

