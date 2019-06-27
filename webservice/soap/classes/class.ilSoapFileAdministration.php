<?php
  /*
   +-----------------------------------------------------------------------------+
   | ILIAS open source                                                           |
   +-----------------------------------------------------------------------------+
   | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
   |                                                                             |
   | This program is free software; you can redistribute it and/or               |
   | modify it under the terms of the GNU General Public License                 |
   | as published by the Free Software Foundation; either version 2              |
   | of the License, or (at your option) any later version.                      |
   |                                                                             |
   | This program is distributed in the hope that it will be useful,             |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
   | GNU General Public License for more details.                                |
   |                                                                             |
   | You should have received a copy of the GNU General Public License           |
   | along with this program; if not, write to the Free Software                 |
   | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
   +-----------------------------------------------------------------------------+
  */


  /**
   * Soap file administration methods
   *
   * @author Roland Küstermann <roland@kuestermann.com>
   * @version $Id: class.ilSoapFileAdministration.php 12992 2007-01-25 10:04:26Z rkuester $
   *
   * @package ilias
   */
include_once './webservice/soap/classes/class.ilSoapAdministration.php';

class ilSoapFileAdministration extends ilSoapAdministration
{

    /**
     * add an File with id.
     *
     * @param string $session_id    current session
     * @param int $target_id refid of parent in repository
     * @param string $file_xml   qti xml description of test
     *
     * @return int reference id in the tree, 0 if not successful
     */
	function addFile ($sid, $target_id, $file_xml) {

		$this->initAuth($sid);
		$this->initIlias();

   	    if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->__getMessage(),$this->__getMessageCode());
		}
        global $rbacsystem, $tree, $ilLog, $ilAccess;

        if(!$target_obj =& ilObjectFactory::getInstanceByRefId($target_id,false))
		{
			return $this->__raiseError('No valid target given.', 'Client');
		}


		if(ilObject::_isInTrash($target_id))
		{
			return $this->__raiseError("Parent with ID $target_id has been deleted.", 'CLIENT_TARGET_DELETED');
		}

   		// Check access
		$allowed_types = array('cat','grp','crs','fold','root');
		if(!in_array($target_obj->getType(), $allowed_types))
		{
			return $this->__raiseError('No valid target type. Target must be reference id of "course, group, category or folder"', 'Client');
		}

	    if(!$ilAccess->checkAccess('create','',$target_id,"file"))
		{
			return $this->__raiseError('No permission to create Files in target  '.$target_id.'!', 'Client');
		}

        // create object, put it into the tree and use the parser to update the settings
		include_once './Modules/File/classes/class.ilFileXMLParser.php';
		include_once './Modules/File/classes/class.ilFileException.php';
		include_once './Modules/File/classes/class.ilObjFile.php';

		$file = new ilObjFile();
    	try
        {

    		$fileXMLParser = new ilFileXMLParser($file, $file_xml);

    		if ($fileXMLParser->start()) 
    		{
				global $ilLog;
				
				$ilLog->write(__METHOD__.': File type: '.$file->getFileType());
				
				$file->create();
        		$file->createReference();
        		$file->putInTree($target_id);
        		$file->setPermissions($target_id);

	        	// we now can save the file contents since we know the obj id now.
    	    	$fileXMLParser->setFileContents();
				#$file->update();

        		return $file->getRefId();
        	}
        	else 
        	{
        		return $this->__raiseError("Could not add file", "Server");
        	} 
        }
        catch(ilFileException $exception) {
        	return $this->__raiseError($exception->getMessage(), $exception->getCode() == ilFileException::$ID_MISMATCH ? "Client" : "Server");
     	}
	 }


    /**
     * update a File with id.
     *
     * @param string $session_id    current session
     * @param int $ref_id   refid id of File in repository
     * @param string $file_xml   qti xml description of test
     *
     * @return boolean true, if update successful, false otherwise
     */
	function updateFile ($sid, $ref_id, $file_xml) 
	{
		$this->initAuth($sid);
		$this->initIlias();

	    if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->__getMessage(),$this->__getMessageCode());
		}
        global $rbacsystem, $tree, $ilLog, $ilAccess;

		if(ilObject::_isInTrash($ref_id))
		{
			return $this->__raiseError('Cannot perform update since file has been deleted.', 'CLIENT_OBJECT_DELETED');
		}
        // get obj_id
		if(!$obj_id = ilObject::_lookupObjectId($ref_id))
		{
			return $this->__raiseError('No File found for id: '.$ref_id,
									   'Client');
		}

   		// Check access
		$permission_ok = false;
		foreach($ref_ids = ilObject::_getAllReferences($obj_id) as $ref_id)
		{
			if($ilAccess->checkAccess('write','',$ref_id))
			{
				$permission_ok = true;
				break;
			}
		}

		if(!$permission_ok)
		{
			return $this->__raiseError('No permission to edit the File with id: '.$ref_id,
									   'Server');
		}


		$file = ilObjectFactory::getInstanceByObjId($obj_id, false);

		if (!is_object($file) || $file->getType()!= "file")
		{
            return $this->__raiseError('Wrong obj id or type for File with id '.$ref_id,
									   'Server');
		}

		include_once './Modules/File/classes/class.ilFileXMLParser.php';
		include_once './Modules/File/classes/class.ilFileException.php';
        $fileXMLParser = new ilFileXMLParser($file, $file_xml, $obj_id);

        try
        {

            if ($fileXMLParser->start())
            {
                $fileXMLParser->updateFileContents();

                return  $file->update();
            }
        } 
        catch(ilFileException $exception) 
        {
           return $this->__raiseError($exception->getMessage(),
									   $exception->getCode() == ilFileException::$ID_MISMATCH ? "Client" : "Server");
        }
        return false;
    }

	// ibi-patch start

	/**
	 * @param $sid
	 * @param $mode
	 * @param $zip_path
	 * @return bool|soap_fault|SoapFault
	 * @throws ilImportException
	 */
	public function createHelp($sid, $mode, $zip_path)
	{
		$this->initAuth($sid);
		$this->initIlias();

		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->__getMessage(),$this->__getMessageCode());
		}
		global $DIC;

		$help_id = ilObjHelpSettings::createHelpModule();

		$imp = new ilImport();
		$conf = $imp->getConfig("Services/Help");
		$conf->setModuleId($help_id);
		$new_id = $imp->importObject(
			"unused",
			$zip_path,
			basename($zip_path),
			'lm',
			'Modules/LearningModule',
			true
		);

		$new_obj = new ilObjLearningModule($new_id, false);
		ilObjHelpSettings::writeHelpModuleLmId($help_id, $new_obj->getId());

		$DIC->settings()->set('help_module', $help_id);
		$DIC->settings()->set('help_mode', $mode);

		return true;
	}

	/**
	 * @param $sid
	 * @param $ref_id
	 * @param $zip_path
	 */
	public function updateItemGroup($sid, $ref_id, $zip_path, $original_id, $a_mappings)
	{
		$this->initAuth($sid);
		$this->initIlias();

		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->__getMessage(),$this->__getMessageCode());
		}
		global $DIC;
		$tree = $DIC->repositoryTree();
		$ilLog = $DIC->logger()->wsrv();
		$access = $DIC->access();

		if(ilObject::_isInTrash($ref_id))
		{
			return $this->__raiseError('Cannot perform update since ItemGroup has been deleted.', 'CLIENT_OBJECT_DELETED');
		}
		// get obj_id
		if(!$obj_id = ilObject::_lookupObjectId($ref_id))
		{
			return $this->__raiseError('No ItemGroup found for id: '.$ref_id,
				'Client');
		}

		// Check access
		$permission_ok = false;
		if(!$access->checkAccess('write','',$ref_id)) {
			return $this->__raiseError(
				'No permission to edit the ItemGroup with id: '.$ref_id,
				'Server');
		}

		$itgr = ilObjectFactory::getInstanceByRefId($ref_id, false);
		if(!$itgr instanceof ilObjItemGroup)
		{
			return $this->__raiseError('Wrong obj id or type for ItemGroup with id '.$ref_id,
				'Server');
		}

		$items = new ilItemGroupItems($ref_id);
		$items->delete();


		try {

			include_once './Services/Export/classes/class.ilImport.php';
			$imp = new ilImport((int) $ref_id);

			$obj_id = ilObject::_lookupObjId($ref_id);


			$imp->getMapping()->addMapping('Services/Container','objs',$original_id,$obj_id);
			foreach($a_mappings as  $mapping_import_id)
			{
				list($remote_obj_id, $import_id) = explode('__',$mapping_import_id);
				$imp->getMapping()->addMapping(
					'Services/Container',
					'objs',
					$remote_obj_id,
					ilObject::_getIdForImportId($import_id)
				);
			}

			$imp->importObject(
				'unused',
				$zip_path,
				basename($zip_path),
				'itgr',
				'',
				true
			);
		}
		catch(Exception $e) {
			return $this->__raiseError($e->getMessage(),'Server');
		}
	}


	/**
	 * @param $sid
	 * @param $ref_id
	 * @param $zip_path
	 */
	public function updateBlog($sid, $ref_id, $zip_path, $original_id)
	{
		$this->initAuth($sid);
		$this->initIlias();

		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->__getMessage(),$this->__getMessageCode());
		}
		global $DIC;
		$tree = $DIC->repositoryTree();
		$ilLog = $DIC->logger()->wsrv();
		$access = $DIC->access();

		if(ilObject::_isInTrash($ref_id))
		{
			return $this->__raiseError('Cannot perform update since blog has been deleted.', 'CLIENT_OBJECT_DELETED');
		}
		// get obj_id
		if(!$obj_id = ilObject::_lookupObjectId($ref_id))
		{
			return $this->__raiseError('No blog found for id: '.$ref_id,
				'Client');
		}

		// Check access
		$permission_ok = false;
		foreach($ref_ids = ilObject::_getAllReferences($obj_id) as $ref_id)
		{
			if($access->checkAccess('write','',$ref_id))
			{
				$permission_ok = true;
				break;
			}
		}
		if(!$permission_ok)
		{
			return $this->__raiseError('No permission to edit the blog with id: '.$ref_id,
				'Server');
		}
		$blog = ilObjectFactory::getInstanceByObjId($obj_id, false);
		if(!$blog instanceof ilObjBlog)
		{
			return $this->__raiseError('Wrong obj id or type for blog with id '.$ref_id,
				'Server');
		}

		try {

			// delete blog specific data
			foreach(ilBlogPosting::getAllPostings($blog->getId()) as $blog_posting_id => $blog_info)
			{
				$post = new ilBlogPosting($blog_posting_id);
				$post->delete();
			}

			include_once './Services/Export/classes/class.ilImport.php';
			$imp = new ilImport((int) $ref_id);

			$obj_id = ilObject::_lookupObjId($ref_id);
			$imp->getMapping()->addMapping('Services/Container','objs',$original_id,$obj_id);
			$imp->importObject(
				'unused',
				$zip_path,
				basename($zip_path),
				'blog',
				'',
				true
			);
		}
		catch(Exception $e) {
			return $this->__raiseError($e->getMessage(),'Server');
		}
	}


	public function updateLearningModule($sid,$ref_id, $zip_path, $a_online,$a_old_id, $a_title, $a_desc)
	{
		$this->initAuth($sid);
		$this->initIlias();

		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->__getMessage(),$this->__getMessageCode());
		}
		global $DIC;
		$tree = $DIC->repositoryTree();
		$ilLog = $DIC->logger()->wsrv();
		$access = $DIC->access();

		if(ilObject::_isInTrash($ref_id))
		{
			return $this->__raiseError('Cannot perform update since learning module has been deleted.', 'CLIENT_OBJECT_DELETED');
		}
		// get obj_id
		if(!$obj_id = ilObject::_lookupObjectId($ref_id))
		{
			return $this->__raiseError('No Learning module found for id: '.$ref_id,
				'Client');
		}

		// Check access
		$permission_ok = false;
		foreach($ref_ids = ilObject::_getAllReferences($obj_id) as $ref_id)
		{
			if($access->checkAccess('write','',$ref_id))
			{
				$permission_ok = true;
				break;
			}
		}
		if(!$permission_ok)
		{
			return $this->__raiseError('No permission to edit the learning module with id: '.$ref_id,
				'Server');
		}
		$lm = ilObjectFactory::getInstanceByObjId($obj_id, false);
		if(!$lm instanceof ilObjLearningModule)
		{
			return $this->__raiseError('Wrong obj id or type for Html learning module with id '.$ref_id,
				'Server');
		}

		try {

			// delete lm specific data
			$lm->delete(false);

			include_once './Services/Export/classes/class.ilImport.php';
			$imp = new ilImport((int) $ref_id);

			$obj_id = ilObject::_lookupObjId($ref_id);
			$imp->getMapping()->addMapping('Services/Container','objs',$a_old_id,$obj_id);
			$imp->importObject(
				'unused',
				$zip_path,
				basename($zip_path),
				'lm',
				'',
				true
			);
		}
		catch(Exception $e) {
			return $this->__raiseError($e->getMessage(),'Server');
		}

		$lm->setOnline((bool) $a_online);
		$lm->setTitle($a_title);
		$lm->setDescription($a_desc);
		$lm->update();
	}


	public function updateHtmlLearningModule($sid,$ref_id, $zip_path, $a_online,$a_old_id, $a_title, $a_desc, $a_start)
	{
		$this->initAuth($sid);
		$this->initIlias();

		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->__getMessage(),$this->__getMessageCode());
		}
		global $rbacsystem, $tree, $ilLog;

		if(ilObject::_isInTrash($ref_id))
		{
			return $this->__raiseError('Cannot perform update since file has been deleted.', 'CLIENT_OBJECT_DELETED');
		}
		// get obj_id
		if(!$obj_id = ilObject::_lookupObjectId($ref_id))
		{
			return $this->__raiseError('No HTML Learning module found for id: '.$ref_id,
									   'Client');
		}

		// Check access
		$permission_ok = false;
		foreach($ref_ids = ilObject::_getAllReferences($obj_id) as $ref_id)
		{
			if($rbacsystem->checkAccess('edit',$ref_id))
			{
				$permission_ok = true;
				break;
			}
		}

		if(!$permission_ok)
		{
			return $this->__raiseError('No permission to edit the Html learning module with id: '.$ref_id,
									   'Server');
		}


		$html = ilObjectFactory::getInstanceByObjId($obj_id, false);

		if (!is_object($html) || $html->getType()!= "htlm")
		{
			return $this->__raiseError('Wrong obj id or type for Html learning module with id '.$ref_id,
									   'Server');
		}


		try {
			include_once './Services/Export/classes/class.ilImport.php';
			$imp = new ilImport((int) $ref_id);

			$obj_id = ilObject::_lookupObjId($ref_id);
			$imp->getMapping()->addMapping('Services/Container','objs',$a_old_id,$obj_id);
			$imp->importObject(
				'unused',
				$zip_path,
				basename($zip_path),
				'htlm',
				'',
				true
			);

			//$imp->importFromZip($zip_path, 'htlm');
		}
		catch(Exception $e) {
			return $this->__raiseError($e->getMessage(),'Server');
		}

		$html->setOnline((bool) $a_online);
		$html->setTitle($a_title);
		$html->setDescription($a_desc);
		$html->setStartFile($a_start);
		$html->update();
	}
	// ibi-patch end

	/**
	 * get File xml
	 *
	 * @param string $sid
	 * @param int $ref_id
	 * @param boolean $attachFileContentsMode
	 *
	 * @return xml following ilias_file_x.dtd
	 */

	function getFileXML ($sid, $ref_id, $attachFileContentsMode) 
	{
		$this->initAuth($sid);
		$this->initIlias();

	    if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->__getMessage(),$this->__getMessageCode());
		}
		if(!strlen($ref_id))
		{
			return $this->__raiseError('No ref id given. Aborting!',
									   'Client');
		}
		global $rbacsystem, $tree, $ilLog, $ilAccess;


		// get obj_id
		if(!$obj_id = ilObject::_lookupObjectId($ref_id))
		{
			return $this->__raiseError('No File found for id: '.$ref_id,
									   'Client');
		}

		if(ilObject::_isInTrash($ref_id))
		{
			return $this->__raiseError("Object with ID $ref_id has been deleted.", 'Client');
		}

		// Check access
		$permission_ok = false;
		foreach($ref_ids = ilObject::_getAllReferences($obj_id) as $ref_id)
		{
			if($ilAccess->checkAccess('read','',$ref_id))
			{
				$permission_ok = true;
				break;
			}
		}

		if(!$permission_ok)
		{
			return $this->__raiseError('No permission to edit the object with id: '.$ref_id,
									   'Server');
		}

		$file = ilObjectFactory::getInstanceByObjId($obj_id, false);

		if (!is_object($file) || $file->getType()!= "file")
		{
            return $this->__raiseError('Wrong obj id or type for File with id '.$ref_id,
									   'Server');
		}
   	    // store into xml result set
		include_once './Modules/File/classes/class.ilFileXMLWriter.php';

		// create writer
		$xmlWriter = new ilFileXMLWriter();
		$xmlWriter->setFile($file);
		$xmlWriter->setAttachFileContents($attachFileContentsMode);
		$xmlWriter->start();

		return $xmlWriter->getXML();
	}
}
?>
