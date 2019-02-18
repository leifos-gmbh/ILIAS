<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Root folder importer
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ingroup ModulesRootFolder
 */
class ilRootFolderImporter extends ilXmlImporter
{
    /**
     * @var ilObjRootFolder
     */
	private $root = null;


    /**
     * @inheritdoc
     */
	public function init()
	{
	}
	
	/**
	 * @inheritdoc
	 */
	function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
	{
		$this->root = ilObjectFactory::getInstanceByRefId(ROOT_FOLDER_ID, false);
		// nothing to do

	}
	
	/**
	 * @inheritdoc
	 */
	function finalProcessing($a_mapping)
	{	
	}
	
}
?>
