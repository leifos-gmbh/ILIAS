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
	 * @var ilContainerFilterService
	 */
	protected $service;

	/**
	 * Constructor
	 */
	public function __construct(ilContainerFilterService $service, ilContainerFilterAdvMDAdapter $adv_adapter, ilLanguage $lng)
	{
		$this->adv_adapter = $adv_adapter;
		$this->lng = $lng;
		$this->service = $service;
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

	/**
	 * Get filter element for reference id
	 *
	 * @param int $ref_id
	 * @return \ILIAS\UI\Component\Input\Container\Filter\Standard
	 */
	public function getFilterForRefId($ref_id, $action): \ILIAS\UI\Component\Input\Container\Filter\Standard
	{
		global $DIC;
		$ui = $DIC->ui()->factory();
		$service = $this->service;
		$lng = $this->lng;

		$set = $service->data()->getFilterSetForRefId($ref_id);

		$fields = $fields_act = [];
		foreach ($set->getFields() as $field)
		{
			// standard fields
			if ($field->getRecordSetId() == 0)
			{
				switch ($field->getFieldId())
				{
					case ilContainerFilterField::STD_FIELD_TITLE:
						$fields["title"] = $ui->input()->field()->text($lng->txt("title"));
						$fields_act[] = true;
						break;
					case ilContainerFilterField::STD_FIELD_DESCRIPTION:
						$fields["description"] = $ui->input()->field()->text($lng->txt("description"));
						$fields_act[] = true;
						break;
					case ilContainerFilterField::STD_FIELD_TITLE_DESCRIPTION:
						$fields["title_desc"] = $ui->input()->field()->text($lng->txt("title")."/".$lng->txt("description"));
						$fields_act[] = true;
						break;
					case ilContainerFilterField::STD_FIELD_KEYWORD:
						$fields["keyword"] = $ui->input()->field()->text($lng->txt("keyword"));
						$fields_act[] = true;
						break;
					case ilContainerFilterField::STD_FIELD_AUTHOR:
						$fields["author"] = $ui->input()->field()->text($lng->txt("author"));
						$fields_act[] = true;
						break;
					case ilContainerFilterField::STD_FIELD_COPYRIGHT:
						$options = [];
						$fields["copyright"] = $select = $ui->input()->field()->select($lng->txt("copyright"), $options);
						$fields_act[] = true;
						break;
					case ilContainerFilterField::STD_FIELD_TUTORIAL_SUPPORT:
						$fields["tutorial_support"] = $ui->input()->field()->text($lng->txt("tutorial_support"));
						$fields_act[] = true;
						break;
					case ilContainerFilterField::STD_FIELD_OBJECT_TYPE:
						$options = [];
						$fields["type"] = $ui->input()->field()->select($lng->txt("type"), $options);
						$fields_act[] = true;
						break;
				}
			}
			else
			{
				// advanced metadata fields
				$title = $service->advancedMetadata()->getTitle($field->getRecordSetId(), $field->getFieldId());
				switch ($service->advancedMetadata()->getAdvType($field->getFieldId()))
				{
					case ilAdvancedMDFieldDefinition::TYPE_SELECT:
					case ilAdvancedMDFieldDefinition::TYPE_SELECT_MULTI:
						$options = [];
						$fields["adv_".$field->getFieldId()] =
							$ui->input()->field()->select($title, $options);
						$fields_act[] = true;
						break;

					case ilAdvancedMDFieldDefinition::TYPE_TEXT:
					case ilAdvancedMDFieldDefinition::TYPE_INTEGER:
						$fields["adv_".$field->getFieldId()] =
							$ui->input()->field()->text($title);
						$fields_act[] = true;
						break;
				}
			}
		}

		$filter = $DIC->uiService()->filter()->standard("filter_ID", $action,
			$fields, $fields_act, false, false);
		return $filter;
	}

}