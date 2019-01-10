<?php

/* Copyright (c) 2019 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Component as C;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Validation\Factory as ValidationFactory;
use ILIAS\Transformation\Factory as TransformationFactory;

/**
 * This implements the select.
 */
class TreeSelect extends Input implements C\Input\Field\TreeSelect {

	protected $label;
	protected $tree;
	protected $is_multiple;
	protected $value;

	/**
	 * Select constructor.
	 *
	 * @param DataFactory           $data_factory
	 * @param ValidationFactory     $validation_factory
	 * @param TransformationFactory $transformation_factory
	 * @param string                $label
	 * @param mixed					$tree
	 * @param bool					$is_multiple
	 * @param string                $byline
	 */
	public function __construct(
		DataFactory $data_factory,
		ValidationFactory $validation_factory,
		TransformationFactory $transformation_factory,
		$label,
		$tree,
		$is_multiple,
		$byline
	) {
		parent::__construct($data_factory, $validation_factory, $transformation_factory, $label, $byline);
		$this->tree = $tree;
		$this->is_multiple = $is_multiple;
	}

	/**
	 * @inheritdoc
	 */
	protected function isClientSideValueOk($value) {

	}

	/**
	 * @inheritdoc
	 */
	protected function getConstraintForRequirement() {

	}
}