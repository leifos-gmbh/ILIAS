<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Container user filer. This holds the current filter data being used for
 * filtering the objects being presented.
 *
 * Currently a plain assoc array as retrieved by $filter->getData
 *
 * @author killing@leifos.de
 * @ingroup ServicesContainer
 */
class ilContainerUserFilter
{
	/**
	 * @var array
	 */
	protected $data;

	/**
	 * Constructor
	 */
	public function __construct($data)
	{
		$this->data = $data;
	}

	/**
	 * Get data
	 *
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}


}