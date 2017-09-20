<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './webservice/soap/classes/class.ilSoapAdministration.php';

/**
 * This class handles all DB changes necessary for fraunhofer
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 *
 */
class ilSoapLearningProgressAdministration extends ilSoapAdministration
{
	protected static $DELETE_PROGRESS_FILTER_TYPES = array('sahs', 'tst');

	const PROGRESS_FILTER_ALL = 0;
	const PROGRESS_FILTER_IN_PROGRESS = 1;
	const PROGRESS_FILTER_COMPLETED = 2;
	const PROGRESS_FILTER_FAILED = 3;
	const PROGRESS_FILTER_NOT_ATTEMPTED = 4;

	const SOAP_LP_ERROR_AUTHENTICATION = 50;
	const SOAP_LP_ERROR_INVALID_FILTER = 52;
	const SOAP_LP_ERROR_INVALID_REF_ID = 54;
	const SOAP_LP_ERROR_LP_NOT_AVAILABLE = 56;
	const SOAP_LP_ERROR_NO_PERMISSION = 58;
	const SOAP_LP_ERROR_LP_NOT_ENABLED = 60;
	
	protected static $PROGRESS_INFO_TYPES = array(
		self::PROGRESS_FILTER_ALL,
		self::PROGRESS_FILTER_IN_PROGRESS,
		self::PROGRESS_FILTER_COMPLETED,
		self::PROGRESS_FILTER_FAILED,
		self::PROGRESS_FILTER_NOT_ATTEMPTED
	);
		
	
	
	const USER_FILTER_ALL = -1;
	
	/**
	 * Delete progress of users and objects
	 * Implemented for 
	 */
	public function deleteProgress($sid, $ref_ids, $usr_ids, $type_filter, $progress_filter)
	{
		$this->initAuth($sid);
		$this->initIlias();

		if(!is_array($usr_ids))
		{
			$usr_ids = (array)$usr_ids;
		}
		if(!is_array($type_filter))
		{
			$type_filter = (array)$type_filter;
		}

		// Check session
		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->__getMessage(),$this->__getMessageCode());
		}

		// Check filter
		if(array_diff((array) $type_filter, self::$DELETE_PROGRESS_FILTER_TYPES))
		{
			return $this->__raiseError('Invalid filter type given', 'Client');
		}
		
		include_once 'Services/User/classes/class.ilObjUser.php';
		if(!in_array(self::USER_FILTER_ALL, $usr_ids) and !ilObjUser::userExists($usr_ids))
		{
			return $this->__raiseError('Invalid user ids given', 'Client');
		}
		
		$valid_refs = array();
		foreach((array) $ref_ids as $ref_id)
		{
			$obj_id = ilObject::_lookupObjId($ref_id);
			$type = ilObject::_lookupType($obj_id);
			
			// All containers
			if($GLOBALS['objDefinition']->isContainer($type))
			{
				$all_sub_objs = array();
				foreach(($type_filter) as $type_filter_item)
				{
					$sub_objs = $GLOBALS['tree']->getSubTree(
						$GLOBALS['tree']->getNodeData($ref_id),
						false,
						$type_filter_item
					);
					$all_sub_objs = array_merge($all_sub_objs, $sub_objs);
				}
				
				foreach($all_sub_objs as $child_ref)
				{
					$child_type = ilObject::_lookupType(ilObject::_lookupObjId($child_ref));
					if(!$GLOBALS['ilAccess']->checkAccess('write', '', $child_ref))
					{
						return $this->__raiseError('Permission denied for : '. $ref_id.' -> type '.$type, 'Client');
					}
					$valid_refs[] = $child_ref;
				}
				
			}
			elseif(in_array($type, $type_filter))
			{
				if(!$GLOBALS['ilAccess']->checkAccess('write','',$ref_id))
				{
					return $this->__raiseError('Permission denied for : '. $ref_id.' -> type '.$type, 'Client');
				}
				$valid_refs[] = $ref_id;
			}
			else
			{
				return $this->__raiseError('Invalid object type given for : '. $ref_id.' -> type '.$type, 'Client');
			}
		}
		
		// Delete tracking data
		foreach($valid_refs as $ref_id)
		{
			include_once './Services/Object/classes/class.ilObjectFactory.php';
			$obj = ilObjectFactory::getInstanceByRefId($ref_id, false);
			
			if(!$obj instanceof ilObject)
			{
				return $this->__raiseError('Invalid reference id given : '. $ref_id.' -> type '.$type, 'Client');
			}
			
			// filter users
			$valid_users = $this->applyProgressFilter($obj->getId(), (array) $usr_ids, (array) $progress_filter);
			
			switch($obj->getType())
			{
				case 'sahs':
					include_once './Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php';
					$subtype = ilObjSAHSLearningModule::_lookupSubType($obj->getId());
					
					switch($subtype)
					{
						case 'scorm':
							$this->deleteScormTracking($obj->getId(),(array) $valid_users);
							break;
							
						case 'scorm2004':
							$this->deleteScorm2004Tracking($obj->getId(), (array) $valid_users);
							break;
					}
					break;
					
				case 'tst':
					foreach((array) $valid_users as $usr_id)
					{
						$obj->removeTestResultsForUser($usr_id);
					}
					break;
			}
			
			// Refresh status
			include_once './Services/Tracking/classes/class.ilLPStatusWrapper.php';
			ilLPStatusWrapper::_resetInfoCaches($obj->getId());
			ilLPStatusWrapper::_refreshStatus($obj->getId(), $valid_users);
			
		}
		return true;
	}
	
	public function getProgressInfo($sid, $a_ref_id, $a_progress_filter)
	{
		global $ilAccess;
		$this->initAuth($sid);
		$this->initIlias();

		// Check session
		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError('Error '.self::SOAP_LP_ERROR_AUTHENTICATION.':'.$this->__getMessage(),
				self::SOAP_LP_ERROR_AUTHENTICATION);
		}
		
		// Check filter
		if(array_diff((array) $a_progress_filter, self::$PROGRESS_INFO_TYPES))
		{
			return $this->__raiseError('Error '.self::SOAP_LP_ERROR_INVALID_FILTER.': Invalid filter type given',
				self::SOAP_LP_ERROR_INVALID_FILTER);
		}
		// Check LP enabled
		include_once("Services/Tracking/classes/class.ilObjUserTracking.php");
		if(!ilObjUserTracking::_enabledLearningProgress())
		{
			return $this->__raiseError('Error '. self::SOAP_LP_ERROR_LP_NOT_ENABLED .': Learning progress not enabled in ILIAS',
				self::SOAP_LP_ERROR_LP_NOT_ENABLED);
		}

		include_once './Services/Object/classes/class.ilObjectFactory.php';
		$obj = ilObjectFactory::getInstanceByRefId($a_ref_id, false);
		if(!$obj instanceof ilObject)
		{
			return $this->__raiseError('Error '.self::SOAP_LP_ERROR_INVALID_REF_ID.': Invalid reference id '. $a_ref_id.' given',
				self::SOAP_LP_ERROR_INVALID_REF_ID);
		}
		
		// check lp available
		include_once './Services/Tracking/classes/class.ilLPObjSettings.php';
		$mode = ilLPObjSettings::_lookupDBMode($obj->getId());
		if($mode == ilLPObjSettings::LP_MODE_UNDEFINED)
		{
			return $this->__raiseError('Error '.self::SOAP_LP_ERROR_LP_NOT_AVAILABLE.': Learning progress not available for objects of type '.
				$obj->getType(),
				self::SOAP_LP_ERROR_LP_NOT_AVAILABLE);
		}

		// check rbac
		if(!$ilAccess->checkAccess('edit_learning_progress','',$a_ref_id))
		{
			return $this->__raiseError('Error '. self::SOAP_LP_ERROR_NO_PERMISSION .': No Permission to access learning progress in this object',
				self::SOAP_LP_ERROR_NO_PERMISSION);
		}
		
		include_once './Services/Xml/classes/class.ilXmlWriter.php';
		$writer = new ilXmlWriter();
		$writer->xmlStartTag(
				'LearningProgressInfo',
				array(
					'ref_id' => $obj->getRefId(),
					'type' => $obj->getType()
				)
		);
		
		$writer->xmlStartTag('LearningProgressSummary');
		
		include_once './Services/Tracking/classes/class.ilLPStatusWrapper.php';
		if(in_array(self::PROGRESS_FILTER_ALL, $a_progress_filter) or in_array(self::PROGRESS_FILTER_COMPLETED, $a_progress_filter))
		{
			$completed = ilLPStatusWrapper::_getCountCompleted($obj->getId());
			$writer->xmlElement(
					'Status',
					array(
						'type'  => self::PROGRESS_FILTER_COMPLETED,
						'num'	=> (int) $completed
					)
			);
		}
		if(in_array(self::PROGRESS_FILTER_ALL, $a_progress_filter) or in_array(self::PROGRESS_FILTER_IN_PROGRESS, $a_progress_filter))
		{
			$completed = ilLPStatusWrapper::_getCountInProgress($obj->getId());
			$writer->xmlElement(
					'Status',
					array(
						'type'  => self::PROGRESS_FILTER_IN_PROGRESS,
						'num'	=> (int) $completed
					)
			);
		}
		if(in_array(self::PROGRESS_FILTER_ALL, $a_progress_filter) or in_array(self::PROGRESS_FILTER_FAILED, $a_progress_filter))
		{
			$completed = ilLPStatusWrapper::_getCountFailed($obj->getId());
			$writer->xmlElement(
					'Status',
					array(
						'type'  => self::PROGRESS_FILTER_FAILED,
						'num'	=> (int) $completed
					)
			);
		}
		if(in_array(self::PROGRESS_FILTER_ALL, $a_progress_filter) or in_array(self::PROGRESS_FILTER_NOT_ATTEMPTED, $a_progress_filter))
		{
			$completed = ilLPStatusWrapper::_getCountNotAttempted($obj->getId());
			$writer->xmlElement(
					'Status',
					array(
						'type'  => self::PROGRESS_FILTER_NOT_ATTEMPTED,
						'num'	=> (int) $completed
					)
			);
		}
		$writer->xmlEndTag('LearningProgressSummary');
		

		$writer->xmlStartTag('UserProgress');
		if(in_array(self::PROGRESS_FILTER_ALL, $a_progress_filter) or in_array(self::PROGRESS_FILTER_COMPLETED, $a_progress_filter))
		{
			$completed = ilLPStatusWrapper::_getCompleted($obj->getId());
			$this->addUserProgress($writer, $completed, self::PROGRESS_FILTER_COMPLETED);
		}
		if(in_array(self::PROGRESS_FILTER_ALL, $a_progress_filter) or in_array(self::PROGRESS_FILTER_IN_PROGRESS, $a_progress_filter))
		{
			$completed = ilLPStatusWrapper::_getInProgress($obj->getId());
			$this->addUserProgress($writer, $completed, self::PROGRESS_FILTER_IN_PROGRESS);
		}
		if(in_array(self::PROGRESS_FILTER_ALL, $a_progress_filter) or in_array(self::PROGRESS_FILTER_FAILED, $a_progress_filter))
		{
			$completed = ilLPStatusWrapper::_getFailed($obj->getId());
			$this->addUserProgress($writer, $completed, self::PROGRESS_FILTER_FAILED);
		}
		if(in_array(self::PROGRESS_FILTER_ALL, $a_progress_filter) or in_array(self::PROGRESS_FILTER_NOT_ATTEMPTED, $a_progress_filter))
		{
			$completed = ilLPStatusWrapper::_getNotAttempted($obj->getId());
			$this->addUserProgress($writer, $completed, self::PROGRESS_FILTER_NOT_ATTEMPTED);
		}
		$writer->xmlEndTag('UserProgress');
		$writer->xmlEndTag('LearningProgressInfo');

		return $writer->xmlDumpMem();
	}
	
	protected function addUserProgress(ilXmlWriter $writer, $users, $a_type)
	{
		foreach($users  as $user_id)
		{
			$writer->xmlStartTag(
					'User',
					array(
						'id' => $user_id,
						'status' => $a_type
					)
			);
			
			$info = ilObjUser::_lookupName($user_id);
			$writer->xmlElement('Login',array(),(string) $info['login']);
			$writer->xmlElement('Firstname',array(),(string) $info['firstname']);
			$writer->xmlElement('Lastname',array(),(string) $info['lastname']);
			$writer->xmlEndTag('User');
			
		}
	}
	
	
	/**
	 * Apply progress filter
	 * @param int $obj_id
	 * @param array $usr_ids
	 * @param array $filter
	 * 
	 * @return array $filtered_users
	 */
	protected function applyProgressFilter($obj_id, Array $usr_ids, Array $filter)
	{
		include_once './Services/Tracking/classes/class.ilLPStatusWrapper.php';
		

		$all_users = array();
		if(in_array(self::USER_FILTER_ALL, $usr_ids))
		{
			$all_users = array_unique(
					array_merge(
						ilLPStatusWrapper::_getInProgress($obj_id),
						ilLPStatusWrapper::_getCompleted($obj_id),
						ilLPStatusWrapper::_getFailed($obj_id)
					)
				);
		}
		else
		{
			$all_users = $usr_ids;
		}

		if(!$filter or in_array(self::PROGRESS_FILTER_ALL, $filter))
		{
			$GLOBALS['log']->write(__METHOD__.': Deleting all progress data');
			return $all_users;
		}
		
		$filter_users = array();
		if(in_array(self::PROGRESS_FILTER_IN_PROGRESS, $filter))
		{
			$GLOBALS['log']->write(__METHOD__.': Filtering  in progress.');
			$filter_users = array_merge($filter, ilLPStatusWrapper::_getInProgress($obj_id));
		}
		if(in_array(self::PROGRESS_FILTER_COMPLETED, $filter))
		{
			$GLOBALS['log']->write(__METHOD__.': Filtering  completed.');
			$filter_users = array_merge($filter, ilLPStatusWrapper::_getCompleted($obj_id));
		}
		if(in_array(self::PROGRESS_FILTER_FAILED, $filter))
		{
			$GLOBALS['log']->write(__METHOD__.': Filtering  failed.');
			$filter_users = array_merge($filter, ilLPStatusWrapper::_getFailed($obj_id));
		}
		
		// Build intersection
		return array_intersect($all_users, $filter_users);
	}
	
	/**
	 * Delete SCORM Tracking
	 * @global type $ilDB
	 * @param type $a_obj_id
	 * @param type $a_usr_ids
	 * @return boolean
	 */
	protected function deleteScormTracking($a_obj_id, $a_usr_ids)
	{
		global $ilDB;
		
		$query = 'DELETE FROM scorm_tracking '.
		 	'WHERE '.$ilDB->in('user_id',$a_usr_ids,false,'integer').' '.
		 	'AND obj_id = '. $ilDB->quote($a_obj_id,'integer').' ';
		$res = $ilDB->manipulate($query);
		return true;
	}
	
	/**
	 * Delete scorm 2004 tracking
	 * @param type $a_obj_id
	 * @param type $a_usr_ids 
	 */
	protected function deleteScorm2004Tracking($a_obj_id, $a_usr_ids)
	{
		global $ilDB;
		
		$query = 'SELECT cp_node_id FROM cp_node '.
			'WHERE nodename = '. $ilDB->quote('item','text').' '.
			'AND cp_node.slm_id = '.$ilDB->quote($a_obj_id,'integer');
		$res = $ilDB->query($query);
		
		$scos = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$scos[] = $row->cp_node_id;
		}
		
		$query = 'DELETE FROM cmi_node '.
				'WHERE '.$ilDB->in('user_id',(array) $a_usr_ids,false,'integer').' '.
				'AND '.$ilDB->in('cp_node_id',$scos,false,'integer');
		$ilDB->manipulate($query);
	
	}
	
	/**
	 * Get learning progress changes
	 */
	public function getLearningProgressChanges($sid, $timestamp, $include_ref_ids, $type_filter)
	{
		$this->initAuth($sid);
		$this->initIlias();

		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->__getMessage(),$this->__getMessageCode());
		}
		global $rbacsystem, $tree, $ilLog;

		// check administrator
		$types = "";
		if (is_array($type_filter))
		{
			$types = implode($type_filter, ",");
		}
		
		// output lp changes as xml
		try
		{
			include_once './Services/Tracking/classes/class.ilLPXmlWriter.php';
			$writer = new ilLPXmlWriter(true);
			$writer->setTimestamp($timestamp);
			$writer->setIncludeRefIds($include_ref_ids);
			$writer->setTypeFilter($type_filter);
			$writer->write();
		
			return $writer->xmlDumpMem(true);
		}
		catch(UnexpectedValueException $e)
		{
			return $this->__raiseError($e->getMessage(), 'Client');
		}
	}

	/**
	 * eturns learning module progress information
	 *
	 * @param int $sid
	 * @param int $a_ref_id reference id
	 * @param array $a_usr_names login string array
	 * @param array $a_progress_status progress status filter
	 * @return soap_fault|SoapFault|string
	 */
	public function getProgress($sid, $a_ref_id, $a_usr_names, $a_progress_status = array())
	{
		$this->initAuth($sid);
		$this->initIlias();

		if(!is_array($a_usr_names))
		{
			$a_usr_names = (array)$a_usr_names;
		}

		//check authentication
		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->__getMessage(),$this->__getMessageCode());
		}

		//check permission on tracking administration

		if(!$this->checkTracPermission())
		{
			return $this->__raiseError('You have no permission to use this function ', 1);
		}

		//check ref_id
		include_once './Modules/LearningModule/classes/class.ilObjLearningModule.php';
		include_once './Modules/LearningModule/classes/class.ilLMObject.php';
		$obj = new ilObjLearningModule($a_ref_id, true);

		if(!$obj instanceof ilObjLearningModule)
		{
			return $this->__raiseError('Invalid reference id '. $a_ref_id.' given', 1);
		}
		//check usernames
		$usernames = array();
		foreach($a_usr_names as $user)
		{
			$login = ilObjUser::_checkExternalAuthAccount("ldap", $user);

			if($login === false && ilObjUser::_loginExists($user))
			{
				$login = $user;
			}

			$usernames[$user] = $login;

			if($login === false)
			{
				return $this->__raiseError('Invalid user login '. $user.' given', 1);
			}
		}

		include_once "Services/Tracking/classes/class.ilLPStatus.php";
		$progress_status = array();
		$filter_all = false;

		foreach((array) $a_progress_status as $filt)
		{
			//check progress filter
			if(!in_array($filt,
				array(self::PROGRESS_FILTER_ALL,
					self::PROGRESS_FILTER_COMPLETED,
					self::PROGRESS_FILTER_FAILED,
					self::PROGRESS_FILTER_IN_PROGRESS,
					self::PROGRESS_FILTER_NOT_ATTEMPTED)
			)){
				return $this->__raiseError('Invalid progress status '. $filt.' given', 1);
			}

			$stat = -1;

			switch($filt)
			{
				case self::PROGRESS_FILTER_ALL:
					$filter_all = true;
					break;
				case self::PROGRESS_FILTER_COMPLETED:
					$stat = ilLPStatus::LP_STATUS_COMPLETED_NUM;
					break;
				case self::PROGRESS_FILTER_FAILED:
					$stat = ilLPStatus::LP_STATUS_FAILED_NUM;
					break;
				case self::PROGRESS_FILTER_IN_PROGRESS:
					$stat = ilLPStatus::LP_STATUS_IN_PROGRESS_NUM;
					break;
				case self::PROGRESS_FILTER_NOT_ATTEMPTED:
					$stat = ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
					break;
			}

			$progress_status[$filt] = $stat;
		}

		include_once './Services/Object/classes/class.ilObjectLP.php';
		$olp = ilObjectLP::getInstance($obj->getId());
		$mode = $olp->getCurrentMode();
		//check mode for deactivate
		if(!$mode || $mode == ilLPObjSettings::LP_MODE_DEACTIVATED)
		{
			return $this->__raiseError('Learning Progress is deactivated in this Object', 1);
		}
		//init xml writer
		include_once './Services/Xml/classes/class.ilXmlWriter.php';
		$this->writer = new ilXmlWriter();
		$this->writer->xmlStartTag(
			'UserProgress',
			array(
				'ref_id' => $obj->getRefId(),
				'mode' => $mode
			)
		);

		include_once "Services/Tracking/classes/class.ilLPStatusFactory.php";
		include_once "Services/Tracking/classes/class.ilLearningProgress.php";
		$class = ilLPStatusFactory::_getClassById($obj->getId(), $mode);
		$info = $class::_getStatusInfo($obj->getId(), true);

		$collection = $olp->getCollectionInstance();

		foreach($usernames as $ext => $login)
		{
			$id = ilObjUser::_lookupId($login);
			$lp_status = ilLPStatusFactory::_getInstance($obj->getId(), $mode);
			$lp_status = $lp_status->_lookupStatus($obj->getId(), $id);

			if(!in_array($lp_status, $progress_status))
			{
				if(!$filter_all)
				{
					continue;
				}
			}
			//write user infos and status
			$this->writer->xmlStartTag('User', array("usr_id" => (string)$id));
			$user_info = ilObjUser::_lookupName($id);
			$this->writer->xmlElement('Login',array(),(string) $ext);
			$this->writer->xmlElement('Firstname',array(),(string) $user_info['firstname']);
			$this->writer->xmlElement('Lastname',array(),(string) $user_info['lastname']);

			$this->writer->xmlElement('Status',array(),(string) $lp_status);

			if($collection)
			{
				$coll_items = $collection->getItems();
				$possible_items = $collection->getPossibleItems($a_ref_id); // for titles

				foreach($coll_items as $item_id)
				{
					if(!ilLMObject::_exists($item_id))
					{
						continue;
					}
					//write chapter infos
					$this->writer->xmlStartTag('Chapter', array("obj_id" => $item_id));
					$this->writer->xmlElement('Title',array(),(string) $possible_items[$item_id]["title"]);

					//write lp status
					$status = ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
					if(isset($info["completed"][$item_id]) &&
						in_array($id, $info["completed"][$item_id]))
					{
						$status = ilLPStatus::LP_STATUS_COMPLETED_NUM;
					}
					else if(isset($info["in_progress"][$item_id]) &&
						in_array($id, $info["in_progress"][$item_id]))
					{
						$status = ilLPStatus::LP_STATUS_IN_PROGRESS_NUM;
					}
					$this->writer->xmlElement('Status',array(),(string) $status);

					//write typical learning time infos per chapter
					if($mode == ilLPObjSettings::LP_MODE_COLLECTION_TLT)
					{
						// stats
						$spent = 0;
						if(isset($info["tlt_users"][$item_id][$id]))
						{
							$spent = $info["tlt_users"][$item_id][$id];
						}
						$needed = $info["tlt"][$item_id];

						$this->writer->xmlElement('SpentSeconds',array(),(string) $spent);
						$this->writer->xmlElement('TimeNeeded',array(),(string) $needed);
					}
					$this->writer->xmlEndTag('Chapter');
				}
			}
			$progress = ilLearningProgress::_getProgress($id, $obj->getId());
			//write visits infos
			if($mode == ilLPObjSettings::LP_MODE_VISITS)
			{
				$this->writer->xmlElement('Visits',array(),(string) $progress['visits']);
			}
			//write typical learning time infos
			if($mode ==ilLPObjSettings::LP_MODE_TLT)
			{
				$this->writer->xmlElement('SpentSeconds',array(),(string) $progress['spent_seconds']);
				include_once 'Services/MetaData/classes/class.ilMDEducational.php';
				$this->writer->xmlElement('TimeNeeded', array(), (string) ilMDEducational::_getTypicalLearningTimeSeconds($obj->getId()));
			}

			$this->writer->xmlEndTag('User');
		}

		$this->writer->xmlEndTag('UserProgress');

		return $this->writer->xmlDumpMem();
	}

	/**
	 * updates learning module learning progress
	 *
	 * @param string $sid
	 * @param int $a_ref_id reference id
	 * @param array $a_usr_names login string array
	 * @param string $progress_xml progress xml from getProgress
	 * @return bool|soap_fault|SoapFault
	 */
	public function updateProgress($sid, $a_ref_id, $a_usr_names, $progress_xml)
	{
		$this->initAuth($sid);
		$this->initIlias();

		if(!is_array($a_usr_names))
		{
			$a_usr_names = (array)$a_usr_names;
		}

		//check authentication
		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->__getMessage(),$this->__getMessageCode());
		}

		//check permission on tracking administration

		if(!$this->checkTracPermission())
		{
			return $this->__raiseError('You have no permission to use this function ', 1);
		}

		//check ref_id
		include_once './Modules/LearningModule/classes/class.ilObjLearningModule.php';
		include_once './Modules/LearningModule/classes/class.ilLMObject.php';
		$obj = new ilObjLearningModule($a_ref_id, true);

		if(!$obj instanceof ilObjLearningModule)
		{
			return $this->__raiseError('Invalid reference id '. $a_ref_id.' given', 55);
		}

		//check usernames
		foreach($a_usr_names as $user)
		{
			$login = ilObjUser::_checkExternalAuthAccount("ldap", $user);

			if($login === false && ilObjUser::_loginExists($user))
			{
				$login = $user;
			}

			if($login === false)
			{
				return $this->__raiseError('Invalid user login '. $user.' given', 1);
			}
		}

		$xml = simplexml_load_string($progress_xml);

		//check xml
		if($a_ref_id != $xml["ref_id"])
		{
			return $this->__raiseError('Invalid xml given', 59);
		}

		$mode =(int) $xml["mode"];
		include_once "Services/Tracking/classes/class.ilLPObjSettings.php";

		//check mode for deactivate
		if($mode == ilLPObjSettings::LP_MODE_DEACTIVATED)
		{
			return $this->__raiseError('Learning Progress deactivated for object with ref_id '. $a_ref_id, 61);
		}

		include_once "Services/Tracking/classes/class.ilLPStatus.php";

		include_once "Services/Tracking/classes/class.ilLPStatusFactory.php";
		include_once "Services/Tracking/classes/class.ilLearningProgress.php";
		$class = ilLPStatusFactory::_getClassById($obj->getId(), $mode);


		if($mode == ilLPObjSettings::LP_MODE_COLLECTION_TLT)
		{
			$c_tlt_info = $class::_getStatusInfo($obj->getId(), true);
		}


		foreach($xml->User as $user)
		{

			if(in_array(trim((string)$user->Login), $a_usr_names))
			{
				//read xml user and progress infos
				$id =(string) $user["usr_id"];

				$spent_seconds = isset($user->SpentSeconds)?(int) $user->SpentSeconds : 0;
				$visits = isset($user->Visits)?(int) $user->Visits : 0;
				$status = isset($user->Status)?(int) $user->Status : 0;
				$chapters = array();
				$completed = array();

				foreach($user->Chapter as $chapter)
				{
					//read xml progress infos per chapter
					$ch_id = (int)$chapter["obj_id"];
					$chapters[$ch_id]["spent_seconds"] = isset($chapter->SpentSeconds) ?(int) $chapter->SpentSeconds : 0;
					$chapters[$ch_id]["time_needed"] = isset($chapter->TimeNeeded) ?(int) $chapter->TimeNeeded : 0;
					$chapters[$ch_id]["status"] = isset($chapter->Status) ? (int) $chapter->Status : 0;

					if($chapters[$ch_id]["status"] == ilLPStatus::LP_STATUS_COMPLETED_NUM)
					{
						$completed[] = $ch_id;
					}
				}
				include_once "Services/Tracking/classes/class.ilChangeEvent.php";

				switch($mode)
				{
					case ilLPObjSettings::LP_MODE_COLLECTION_MANUAL:

						if($status == ilLPStatus::LP_STATUS_COMPLETED_NUM)
						{
							$completed = array_keys($chapters);
						}
						//set status manual per chapter
						$class::_setObjectStatus($obj->getId(), $id, $completed);
						break;
					case ilLPObjSettings::LP_MODE_VISITS:
						include_once './Services/Tracking/classes/class.ilLearningProgress.php';
						$progress = ilLearningProgress::_getProgress($id, $obj->getId());

						$lp_settings = new ilLPObjSettings($obj->getId());
						$visits_needed = $lp_settings->getVisits();
						if($visits_needed > $progress['visits'] && $status == ilLPStatus::LP_STATUS_COMPLETED_NUM)
						{
							$visits = $visits_needed;
						}
						//record read event und set visits to needed value
						ilChangeEvent::_recordReadEvent($obj->getType(),$a_ref_id,$obj->getId(),$id,true,$visits, $progress['spent_seconds']);
						break;
					case ilLPObjSettings::LP_MODE_TLT:
						include_once "Services/MetaData/classes/class.ilMDEducational.php";
						$time_needed = ilMDEducational::_getTypicalLearningTimeSeconds($obj->getId());

						if($time_needed > $spent_seconds && $status == ilLPStatus::LP_STATUS_COMPLETED_NUM)
						{
							$spent_seconds = $time_needed;
						}
						//record read event und set spent seconds to needed value
						ilChangeEvent::_recordReadEvent($obj->getType(),$a_ref_id,$obj->getId(),$id,true,false,$spent_seconds);
						break;
					case ilLPObjSettings::LP_MODE_COLLECTION_TLT:

						foreach($chapters as $ch_id => $value)
						{
							if(($value["status"] == ilLPStatus::LP_STATUS_COMPLETED_NUM ||
							$status == ilLPStatus::LP_STATUS_COMPLETED_NUM) &&
								$value["spent_seconds"] < $value["time_needed"]
							)
							{
								$value["spent_seconds"] = $value["time_needed"];
							}

							if(ilLMObject::_exists($ch_id) && $c_tlt_info["tlt_users"][$ch_id][$id] != $value["spent_seconds"])
							{
								//record read event und set spent seconds to needed value per chapter
								$this->trackChapterAccess($ch_id, $obj, $id, $value["spent_seconds"] - $c_tlt_info["tlt_users"][$ch_id][$id]);
							}
						}

						break;
				}

				//set completed if status completed
				include_once 'Services/Tracking/classes/class.ilLPMarks.php';
				$lp_marks = new ilLPMarks($obj->getId(),$id);
				$lp_marks->setCompleted($status == ilLPStatus::LP_STATUS_COMPLETED_NUM ? 1 : 0);
				$lp_marks->update();

				//update lm status
				require_once 'Services/Tracking/classes/class.ilLPStatusWrapper.php';
				ilLPStatusWrapper::_updateStatus($obj->getId(), $id);

				ilChangeEvent::_recordReadEvent($obj->getType(),$a_ref_id,$obj->getId(),$id);
			}

		}

		return true;
	}

	/**
	 * Trac Chapter Access in Learningmodule
	 *
	 * @param int $a_obj_id
	 * @param ilObjLearningModule $a_object
	 * @param int $a_usr_id
	 * @param int $a_time_spent_diff
	 */
	protected function trackChapterAccess($a_obj_id, $a_object, $a_usr_id, $a_time_spent_diff)
	{
		global $ilDB, $ilUser;

		$pg_id = $a_obj_id;
		if(!$a_object->getLMTree()->isInTree($pg_id))
		{
			return;
		}

		// find parent chapter(s) for that page
		$parent_st_ids = array();
		foreach($a_object->getLMTree()->getPathFull($pg_id) as $item)
		{
			if($item["type"] == "st")
			{
				$parent_st_ids[] = $item["obj_id"];
			}
		}

		if($parent_st_ids && $a_time_spent_diff)
		{
			// get existing chapter entries
			$ex_st = array();
			$set = $ilDB->query("SELECT obj_id FROM lm_read_event".
				" WHERE ".$ilDB->in("obj_id", $parent_st_ids, "", "integer").
				" AND usr_id = ".$ilDB->quote($a_usr_id, "integer"));
			while($row = $ilDB->fetchAssoc($set))
			{
				$ex_st[] = $row["obj_id"];
			}

			// add missing chapter entries
			$missing_st = array_diff($parent_st_ids, $ex_st);
			if(sizeof($missing_st))
			{
				foreach($missing_st as $st_id)
				{
					$fields = array(
						"obj_id" => array("integer", $st_id),
						"usr_id" => array("integer", $a_usr_id)
					);
					$ilDB->insert("lm_read_event", $fields);
				}
			}

			// update all parent chapters
			$ilDB->manipulate($q = "UPDATE lm_read_event SET".
				" spent_seconds = spent_seconds + ".$ilDB->quote($a_time_spent_diff, "integer").
				" WHERE ".$ilDB->in("obj_id", $parent_st_ids, "", "integer").
				" AND usr_id = ".$ilDB->quote($a_usr_id, "integer"));
		}
	}

	/**
	 * check permission on trac administration object
	 *
	 * @param string $a_opt
	 * @return bool
	 */
	protected function checkTracPermission($a_opt = "write")
	{
		$set = $GLOBALS['ilDB']->query("SELECT ref.ref_id FROM object_data od RIGHT JOIN object_reference ref ON od.obj_id = ref.obj_id".
			" WHERE od.type = " . $GLOBALS['ilDB']->quote('trac', 'text'));

		$trac_ref = $GLOBALS['ilDB']->fetchObject($set)->ref_id;

		return $GLOBALS['rbacsystem']->checkAccess($a_opt,$trac_ref);
	}

}
?>
