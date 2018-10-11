<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Filter field set
 *
 * @author killing@leifos.de
 * @ingroup ServicesContainer
 */
class ilContainerFilterSet
{
	protected $filters;

	/**
	 * Constructor
	 * @param ilContainerFilterField[] $filters
	 */
	public function __construct(array $filters)
	{
		$this->filters = $filters;
	}
	
	/**
	 * Get filters
	 *
	 * @return ilContainerFilterField[]
	 */
	protected function getFilters(): array
	{
		return $this->filters;
	}
	

}