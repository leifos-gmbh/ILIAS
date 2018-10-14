<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Adapter for advanced metadata service
 *
 * @author killing@leifos.de
 * @ingroup ServicesContainer
 */
class ilContainerFilterAdvMDAdapter
{
	protected $types = ["crs", "cat", "grp", "sess"];

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		global $DIC;
		$this->lng = $DIC->language();
	}

	/**
	 * Get active record sets
	 *
	 * @return ilAdvancedMDRecord[]
	 */
	public function getAvailableRecordSets(): array
	{
		$records = [];
		foreach ($this->types as $type)
		{
			foreach (ilAdvancedMDRecord::_getActivatedRecordsByObjectType($type) as $record_obj)
			{
				if ($record_obj->isActive() && $record_obj->getParentObject() == 0)
				{
					$records[] = $record_obj;
				}
			}
		}
		return $records;
	}

	/**
	 * Get fields
	 *
	 * @param int $a_record_id
	 * @return ilAdvancedMDFieldDefinition[]
	 */
	public function getFields($a_record_id): array
	{
		return ilAdvancedMDFieldDefinition::getInstancesByRecordId($a_record_id);
	}

	/**
	 * Get name for filter
	 * @param $record_id
	 * @param $filter_id
	 * @return string
	 * @throws ilException
	 */
	function getTitle($record_id, $filter_id)
	{
		$lng = $this->lng;

		if ($record_id == 0)
		{
			return $lng->txt("cont_std_filter_title_".$filter_id);
		}

		$field = ilAdvancedMDFieldDefinition::getInstance($filter_id);
		return $field->getTitle();
	}

}