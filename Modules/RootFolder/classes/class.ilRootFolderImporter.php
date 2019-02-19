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
     * @var \ilLogger
     */
	private $logger = null;

    /**
     * @var ilObjRootFolder
     */
	private $root = null;


    /**
     * @inheritdoc
     */
	public function init()
	{
		global $DIC;

		$this->logger = $DIC->logger()->root();
	}
	
	/**
	 * @inheritdoc
	 */
	function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
	{
		$this->root = ilObjectFactory::getInstanceByRefId(ROOT_FOLDER_ID, false);

		$root_folder = simplexml_load_string($a_xml);
		if(!$root_folder instanceof SimpleXMLElement) {
			$this->logger->error('Cannot parse root folder xml: ' . $a_xml);
		}

		foreach($root_folder->Sort as $sort_element)
		{
			$attributes = [];
			foreach($sort_element->attributes() as $name => $value)
			{
				$attributes[$name] = $value;
			}
			$this->logger->dump($attributes);
            ilContainerSortingSettings::_importContainerSortingSettings($attributes, $this->root->getId());
		}

		foreach($root_folder->ContainerSettings as $settings_element)
		{
            $this->logger->debug('Found ContainerSettings');
			foreach($settings_element->ContainerSetting as $setting_element)
			{
                $this->logger->debug('Found ContainerSetting');
				foreach($setting_element->attributes() as $name => $keyword)
				{
					$this->logger->debug('Attribute: ' . $keyword .' => ' . (string) $settings_element);
					ilContainer::_writeContainerSetting(
						ROOT_FOLDER_ID,
						$keyword,
						(string) $setting_element
					);
				}
			}
		}

	}
	
	/**
	 * @inheritdoc
	 */
	function finalProcessing($a_mapping)
	{	
	}
	
}
?>
