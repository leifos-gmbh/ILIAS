<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class for root export
 * 
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * $Id$
 */
class ilRootFolderExporter extends ilXmlExporter
{
    /**
	 * @inheritdoc
     */
	public function getXmlExportHeadDependencies($a_entity, $a_target_release, $a_ids)
	{
		// always trigger container because of co-page(s)
		return array(
			array(
				'component'		=> 'Services/Container',
				'entity'		=> 'struct',
				'ids'			=> $a_ids
			)
		);
	}
	
	/**
	 * Get tail dependencies
	 *
	 * @param		string		entity
	 * @param		string		target release
	 * @param		array		ids
	 * @return		array		array of array with keys "component", entity", "ids"
	 */
	function getXmlExportTailDependencies($a_entity, $a_target_release, $a_ids)
	{
		return [];
	}

	/**
	 * Get xml
	 * @param object $a_entity
	 * @param object $a_schema_version
	 * @param object $a_id
	 * @return
	 */
	public function getXmlRepresentation($a_entity, $a_schema_version, $a_id)
	{
		global $DIC;

		$root_ref_id = end(ilObject::_getAllReferences($a_id));
		$root = ilObjectFactory::getInstanceByRefId($root_ref_id,false);

		if(!$root instanceof ilObjRootFolder)
		{
			$DIC->logger()->root()->error($a_id . ' is not instance if type root');
			return '';
		}
	}

	/**
	 * Returns schema versions that the component can export to.
	 * ILIAS chooses the first one, that has min/max constraints which
	 * fit to the target release. Please put the newest on top.
	 *
	 * @return
	 */
	public function getValidSchemaVersions($a_entity)
	{
		return array (
			"5.3.0" => array(
				"namespace" => "http://www.ilias.de/Modules/RootFolder/cat/5_3",
				"xsd_file" => "ilias_root_5_3.xsd",
				"uses_dataset" => false,
				"min" => "5.3.0",
				"max" => "")
		);
	}

	/**
	 * Init method
	 */
	public function init() {
		
	}

}
?>
