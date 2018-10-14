<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Utilities for container filter
 *
 * @author killing@leifos.de
 * @ingroup ServicesContainer
 */
class ilContainerFilterUtil
{
	/**
	 * @var ilContainerFilterAdvMDAdapter
	 */
	protected $adv_adapter;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * Constructor
	 */
	public function __construct(ilContainerFilterAdvMDAdapter $adv_adapter, ilLanguage $lng)
	{
		$this->adv_adapter = $adv_adapter;
		$this->lng = $lng;
	}

	/**
	 * Get title of field
	 * @param $record_id
	 * @param $field_id
	 * @return string
	 * @throws ilException
	 */
	public function getContainerFieldTitle($record_id, $field_id)
	{
		$lng = $this->lng;

		if ($record_id == 0)
		{
			return $lng->txt("cont_std_filter_title_".$field_id);
		}

		$field = ilAdvancedMDFieldDefinition::getInstance($field_id);
		return $field->getTitle();
	}

	/**
	 * Get title of record
	 * @param $record_id
	 * @return string
	 * @throws ilException
	 */
	public function getContainerRecordTitle($record_id)
	{
		$lng = $this->lng;

		if ($record_id == 0)
		{
			return $lng->txt("cont_std_record_title");
		}

		$rec = ilAdvancedMDRecord::_getInstanceByRecordId($record_id);
		return $rec->getTitle();
	}

}