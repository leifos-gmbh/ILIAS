<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Form/classes/class.ilFileInputGUI.php");
require_once("./Services/Form/interfaces/interface.ilFileUploadInputGUI.php");
require_once("./Services/Form/classes/class.ilFileUploadHelper.php");
require_once("./Services/FileUpload/classes/class.ilFileUploadSettings.php");

/**
* This class represents a file input property where multiple files can be dopped in a property form.
*
* @author Stefan Born <stefan.born@phzh.ch> 
* @author Fabio Heer        
* @version $Id$
* @ingroup	ServicesForm
*/
class ilDragDropFileInputGUI extends ilFileInputGUI implements ilFileUploadInputGUI
{
	private $uniqueId = 0;
	private $archive_suffixes = array();
	private $max_number_of_files = null;
	private $submit_button_name = null;
	private $cancel_button_name = null;
	protected $accept_mime_types = array();
	protected $form_submit_mode = ilFileUploadSettings::SUBMIT_ENTIRE_FORM_AFTER_FILE_UPLOADS;
	protected $list_existing_files = false;
	protected $deleting_files_allowed = false;
	protected $upload_helper = null;
	protected $upload_handler = null;
	private static $uniqueInc = 1;
	
	static private function getNextUniqueId()
	{
		return self::$uniqueInc++;
	}
	
	/**
	 * Constructor
	 *
	 * @param ilFileUploadHandler $handler GUI class which receives asynchronous file upload requests
	 * @param string              $a_title   Title
	 * @param string              $a_postvar Post Variable
	 */
	function __construct(ilFileUploadHandler $handler, $a_title = "", $a_postvar = "")
	{
		parent::__construct($a_title, $a_postvar);
		$this->uniqueId = self::getNextUniqueId();
		$this->upload_helper = new ilFileUploadHelper($this, $handler);
		$this->upload_handler = $handler;
	}
	
	/**
	* Set accepted archive suffixes.
	*
	* @param	array	$a_suffixes	Accepted archive suffixes.
	*/
	function setArchiveSuffixes($a_suffixes)
	{
		// Currently, only ZIP is supported and enabled by default (in JS). Therefore this method is disabled for now.
		$this->archive_suffixes = array("zip");
	}

	/**
	* Get accepted archive suffixes.
	*
	* @return	array	Accepted archive suffixes.
	*/
	function getArchiveSuffixes()
	{
		return $this->archive_suffixes;
	}

	/**
	 * @param string $submit_name
	 * @param string $cancel_name
	 */
	function setCommandButtonNames($submit_name, $cancel_name)
	{
		$this->submit_button_name = $submit_name;
		$this->cancel_button_name = $cancel_name;
	}
	
	/**
	 * Render html
	 */
	function render($a_mode = "")
	{
		global $lng;
					
		if(self::$check_wsp_quota)
		{
			include_once "Services/DiskQuota/classes/class.ilDiskQuotaHandler.php";
			if(!ilDiskQuotaHandler::isUploadPossible())
			{
				$lng->loadLanguageModule("file");
				return $lng->txt("personal_workspace_quota_exceeded_warning");			
			}
			else
			{							
				$quota_legend = ilDiskQuotaHandler::getStatusLegend();
			}
		}

		// make sure jQuery is loaded
		iljQueryUtil::initjQuery();
		
		// add file upload scripts
		include_once("./Services/FileUpload/classes/class.ilFileUploadGUI.php");
		ilFileUploadGUI::initFileUpload();
		
		// load template
		$dnd_tpl = new ilTemplate("tpl.prop_dndfiles.html", true, true, "Services/Form");

		// general variables
		$dnd_tpl->setVariable("UPLOAD_ID", $this->uniqueId);
		
		// input
		$dnd_tpl->setVariable("FILE_SELECT_ICON", ilObject::_getIcon('', '', 'fold'));
		require_once("Services/FileUpload/classes/class.ilFileUploadUtil.php");
		$dnd_tpl->setVariable("MAX_FILE_SIZE", ilFileUploadUtil::getMaxFileSize());
		$dnd_tpl->setVariable("FILE_UPLOAD_POSTVAR", $this->getPostVar());
		$dnd_tpl->setVariable("TXT_SHOW_ALL_DETAILS", $lng->txt('show_all_details'));
		$dnd_tpl->setVariable("TXT_HIDE_ALL_DETAILS", $lng->txt('hide_all_details'));
		$dnd_tpl->setVariable("TXT_SELECTED_FILES", $lng->txt('selected_files'));
		$dnd_tpl->setVariable("TXT_DRAG_FILES_HERE", $lng->txt('drag_files_here'));
		$dnd_tpl->setVariable("TXT_NUM_OF_SELECTED_FILES", $lng->txt('num_of_selected_files'));
		$dnd_tpl->setVariable("TXT_SELECT_FILES_FROM_COMPUTER", $lng->txt('select_files_from_computer'));
		$dnd_tpl->setVariable("TXT_OR", $lng->txt('logic_or'));
		$dnd_tpl->setVariable("INPUT_ACCEPT_SUFFIXES", $this->getInputAcceptMimeTypeString());
		if($this->getMaxNumberOfFiles() > 1 OR is_null($this->getMaxNumberOfFiles()))
		{
			$dnd_tpl->touchBlock('multiple');
		}

		// info
		$dnd_tpl->setCurrentBlock("max_size");
		$dnd_tpl->setVariable("TXT_MAX_SIZE", $lng->txt("file_notice")." ".$this->getMaxFileSizeString());
		$dnd_tpl->parseCurrentBlock();
		
		if(isset($quota_legend))
		{
			$dnd_tpl->setVariable("TXT_MAX_SIZE", $quota_legend);
			$dnd_tpl->parseCurrentBlock();
		}
		if(is_numeric($this->getMaxNumberOfFiles()))
		{
			$dnd_tpl->setVariable("TXT_MAX_FILES", sprintf($lng->txt("form_max_files"), $this->getMaxNumberOfFiles()));
			$dnd_tpl->parseCurrentBlock();
		}
		
		$this->outputSuffixes($dnd_tpl);
		
		// create file upload object
		$upload = new ilFileUploadGUI("ilFileUploadDropZone_" . $this->uniqueId, $this->uniqueId, false);
		$upload->enableFormSubmit("ilFileUploadInput_" . $this->uniqueId, $this->submit_button_name, $this->cancel_button_name);
		$upload->setDropAreaId("ilFileUploadDropArea_" . $this->uniqueId);
		$upload->setFileListId("ilFileUploadList_" . $this->uniqueId);
		$upload->setFileSelectButtonId("ilFileUploadFileSelect_" . $this->uniqueId);
		$upload->setSuffixes($this->getSuffixes());
		$upload->setSubmitEntireForm($this->getFormSubmitMode());
		$upload->setGetFileList($this->hasListExistingFiles());
		$upload->setDeleteUploadedFilesAllowed($this->isDeletingFilesAllowed());
		$upload->setMaxNumberOfFiles($this->getMaxNumberOfFiles());
		
		$dnd_tpl->setVariable("FILE_UPLOAD", $upload->getHTML());
		
		return $dnd_tpl->get();
	}
	
	/**
	 * Check input, strip slashes etc. set alert, if input is not ok.
	 *
	 * @return	boolean		Input ok, true/false
	 */	
	function checkInput()
	{
		$this->upload_helper->handleFileUploadRequest();
		/*
		 * Since handleFileUploadRequest() is an exiting method, the following code is only executed when
		 * $this->getFormSubmitMode() == ilFileUploadSettings::SUBMIT_ENTIRE_FORM_AFTER_FILE_UPLOADS
		 */
		
		// Restore the file arrays to the post variable
		$_POST[$this->getPostVar()] = $this->upload_helper->getFileArray();

		if ($this->getRequired() AND count($_POST[$this->getPostVar()]) == 0)
		{
			global $lng;
			$this->setAlert($lng->txt('msg_input_is_required'));

			return FALSE;
		}

		return TRUE;
	}

	/**
	 * @return boolean
	 */
	public function checkFileInput()
	{
		$valid = parent::checkInput();

		// empty file, could be a folder
		if ($_FILES[$this->getPostVar()]['size'] < 1)
		{
			global $lng;
			$this->setAlert($lng->txt('error_upload_was_zero_bytes'));
			$valid = FALSE;
		}

		return $valid;
	}

	/**
	 * Removes the temporary file upload data from the DB.
	 */
	public function clearHandledUploadsFromCache()
	{
		$this->upload_helper->clearPersistedUploads();
	}

	/**
	 * Prefixes the suffixes with a dot and returns them as a string.
	 * @param array $suffixes
	 *
	 * @return string comma separated list of suffixes, e.g. '.jpg,.png'
	 */
	protected function getInputAcceptSuffixes($suffixes)
	{
		if (is_array($suffixes) && count($suffixes) > 0)
			return '.' . implode(',.', $suffixes);
		else
			return '';
	}

	/**
	 * Overwrite parent to return mime types instead of suffixes for the HTML input accept attribute
	 *
	 * @return string
	 */
	protected function getInputAcceptMimeTypeString()
	{
		if (is_array($this->accept_mime_types) AND count($this->accept_mime_types) > 0)
		{
			return implode(',', $this->accept_mime_types);
		}
		else
		{
			// If no accept mime types are set, use the suffixes (only supported by Chrome)
			return $this->getInputAcceptSuffixes($this->getSuffixes());
		}
	}

	/**
	 * Limit the browser's file input selection.
	 * Use setSuffixes() additionally to show the allowed file types.
	 *
	 * @param array $accept_mime_types
	 */
	public function setAcceptMimeTypes($accept_mime_types)
	{
		$this->accept_mime_types = $accept_mime_types;
	}

	/**
	 * @return array
	 */
	public function getAcceptMimeTypes()
	{
		return $this->accept_mime_types;
	}

	/**
	 * @param string $form_submit_mode use the constants in ilFileUploadSettings to set a value.
	 *                                 set "ilFileUploadSettings::SUBMIT_ENTIRE_FORM_NEVER" in order to only receive
	 *                                 (asynchronous) file upload POST requests.
	 *                                 set "ilFileUploadSettings::SUBMIT_ENTIRE_FORM_ALWAYS" to receive the file upload
	 *                                 data along its parent form data
	 *                                 set "ilFileUploadSettings::SUBMIT_ENTIRE_FORM_AFTER_FILE_UPLOADS" to receive
	 *                                 first all file upload requests and then the parent form data.
	 */
	public function setFormSubmitMode($form_submit_mode)
	{
		$this->form_submit_mode = $form_submit_mode;
	}

	/**
	 * @return string
	 */
	public function getFormSubmitMode()
	{
		return $this->form_submit_mode;
	}
	
	/**
	 * When set to true, an asynchronous request to the FileUploadHandler's 'onListUploadedFiles' method
	 * is fired after the initialisation of the ilDragDropFileInputGUI.
	 * 
	 * @param boolean $list_existing_files
	 */
	public function setListExistingFiles($list_existing_files)
	{
		$this->list_existing_files = (bool)$list_existing_files;
	}

	/**
	 * @return boolean
	 */
	public function hasListExistingFiles()
	{
		return $this->list_existing_files;
	}

	/**
	 * Requires hasListExistingFiles() to be TRUE
	 * When set to true, the listed files are presented with a delete button.
	 * An asynchronous request to the FileUploadHandler's 'onDeleteUploadedFile' method is fired when the user clicks
	 * the delete button.
	 * 
	 * @param boolean $allow_deleting_files
	 */
	public function setDeletingFilesAllowed($allow_deleting_files)
	{
		$this->deleting_files_allowed = (bool)$allow_deleting_files;
	}

	/**
	 * @return boolean
	 */
	public function isDeletingFilesAllowed()
	{
		return $this->deleting_files_allowed;
	}

	/**
	 * Helper method to create the response object for an onListUploadedFiles call.
	 * @param ilObjFile[] $a_obj_files
	 *
	 * @return \stdClass
	 */
	public function createListUploadedFilesResponse($a_obj_files)
	{
		$response = new stdClass();
		$response->files = array();

		foreach ($a_obj_files as $file)
		{
			$response->files[] = array(
				'id' => $file->getId(),
				'name' => $file->getFileName(),
				'title' => $file->getTitle(),
				'description' => $file->getDescription(),
				'size' => $file->getFileSize());
		}

		return $response;
	}

	/**
	 * @return boolean
	 */
	public function isPersistFileEnabled()
	{
		return $this->getFormSubmitMode() == ilFileUploadSettings::SUBMIT_ENTIRE_FORM_AFTER_FILE_UPLOADS;
	}

	/**
	 * @param int $max_number_of_files
	 */
	public function setMaxNumberOfFiles($max_number_of_files) {
		$this->max_number_of_files = $max_number_of_files;
	}

	/**
	 * @return int
	 */
	public function getMaxNumberOfFiles() {
		return $this->max_number_of_files;
	}
}
?>