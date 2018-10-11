<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Container field data
 *
 * @author killing@leifos.de
 * @ingroup ServicesContainer
 */
class ilContainerFilterFieldData
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		global $DIC;

		$this->db = $DIC->database();
	}

	/**
	 * Get filter for ref id
	 *
	 * @param
	 * @return
	 */
	protected function getFilterSetForRefId($ref_id)
	{
		$db = $this->db;

		$filter = [];
		$set = $db->queryF("SELECT * FROM cont_filter_field ".
			" WHERE ref_id = %s ",
			array("integer"),
			array($ref_id)
			);
		while ($rec = $db->fetchAssoc($set))
		{
			$filter[] =  new ilContainerFilterField($rec["record_set_id"], $rec["field_id"]);
		}
		return new ilContainerFilterSet($filter);
	}


}