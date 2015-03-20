<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/FileUpload/classes/class.ilFileUploadUtil.php");
require_once("./Services/FileUpload/classes/class.ilFileUploadSettings.php");

/**
 * User interface class for drag and drop file upload.
 *
 * @author Stefan Born <stefan.born@phzh.ch>
 * @author Fabio Heer
 * @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version $Id$
 * 
 * @package ServicesFileUpload
 */
class ilFileUploadGUI
{
	const FILE_OBJ_GUI_CLASS = "ilObjFileGUI";
	private static $shared_code_loaded = false;
	
	private $drop_zone_id = null;
	private $ref_id = null;
	private $current_obj = false;
	private $max_file_size = null;
	private $max_number_of_files = null;
	private $suffixes = array();
	private $archive_suffixes = array();
	private $input_field_name = "upload_files";
	private $input_field_id = null;
	private $use_form = false;
	private $drop_area_id = null;
	private $submit_button_name = "uploadFiles";
	private $cancel_button_name = "cancel";
	private $file_list_id = null;
	private $file_select_button_id = null;
	private $submit_entire_form = ilFileUploadSettings::SUBMIT_ENTIRE_FORM_NEVER;
	private $get_file_list = false;
	private $delete_uploaded_files_allowed = false;
	
	/**
	 * Creates a new file upload GUI.
	 */
	public function __construct($a_drop_zone_id, $a_ref_id = null, $current_obj = false) 
	{
		$this->drop_zone_id = $a_drop_zone_id;
		$this->ref_id = $a_ref_id;
		$this->current_obj = $current_obj;
	}

	/**
	 * Initializes the file upload and loads the needed javascripts and styles.
	 */
	public static function initFileUpload()
	{
		global $tpl;
		
		// needed scripts
		$tpl->addJavaScript("./Services/FileUpload/js/tmpl.js");
		$tpl->addJavaScript("./Services/FileUpload/js/jquery.ui.widget.js");
		$tpl->addJavaScript("./Services/FileUpload/js/jquery.iframe-transport.js");
		$tpl->addJavaScript("./Services/FileUpload/js/jquery.fileupload.js");
		$tpl->addJavaScript("./Services/FileUpload/js/jquery.ba-dotimeout.min.js");
		$tpl->addJavaScript("./Services/FileUpload/js/ilFileUpload.js");
		
		// needed styles
		$tpl->addCss(ilUtil::getStyleSheetLocation("filesystem", "fileupload.css", "Services/FileUpload"));
	}
	
	/**
	 * Gets the HTML code to enable the file upload.
	 * 
	 * @return string The created HTML.
	 */
	public function getHTML()
	{
		global $tpl;
		
		// get values
		$id = $this->ref_id;
		
		// build options
		$options = new stdClass();
		$options->dropZone = $this->makeJqueryId($this->drop_zone_id);
		
		// set url
		$url = $this->getUploadUrl();
		if ($url === false)
			return "";			
			
		if ($url != null)
			$options->url = $url;
		
		// get title and replace quotes with HTML entities
		if ($this->ref_id != null && !$this->use_form)
		{
			$title = ilObject::_lookupTitle(ilObject::_lookupObjId($this->ref_id));
			$title = str_replace("\"", "&quot;", $title);
			$title = str_replace("'", "&#039;", $title);
			$options->listTitle = $title;
		}
		
		// input field (use id if specified)
		if ($this->input_field_id != null)
			$options->fileInput = $this->makeJqueryId($this->input_field_id);
		else if ($this->input_field_name != null)
			$options->fileInput = $this->input_field_name;
		
		// buttons
		$options->submitButton = $this->submit_button_name;
		$options->cancelButton = $this->cancel_button_name;
		
		// drop area
		if ($this->drop_area_id != null)
			$options->dropArea = $this->makeJqueryId($this->drop_area_id);
		
		// file list
		if ($this->file_list_id != null)
			$options->fileList = $this->makeJqueryId($this->file_list_id);
		
		// file list
		if ($this->file_select_button_id != null)
			$options->fileSelectButton = $this->makeJqueryId($this->file_select_button_id);
		
		$options->getFileList = $this->hasGetFileList();
		$options->allowDeletingFiles = $this->hasDeleteUploadedFilesAllowed();
		
		// max size
		$max_size = $this->getMaxFileSize();
		if ($max_size != null)
			$options->maxFileSize = $max_size;

		// max number of files
		$max_number_of_files = $this->getMaxNumberOfFiles();
		if ($max_number_of_files != null)
			$options->maxNumberOfFiles = $max_number_of_files;
		
		$options->submitEntireForm = $this->submit_entire_form;
		
		// allowed extensions
		$options->allowedExtensions = $this->getSuffixes(); 
		
		// supported archive extensions
		$options->supportedArchives = $this->getArchiveSuffixes();
		
		// inject load script
		include_once("./Services/JSON/classes/class.ilJsonUtil.php");
		
		if ($this->use_form)
			$onLoadCode = "var fileUpload$id = new ilFileUpload($id, " . ilJsonUtil::encode($options) . ");";
		else
			$onLoadCode = "il.FileUpload.add(\"$id\", " . ilJsonUtil::encode($options) . ", " . ($this->current_obj ? "true" : "false") . ");";
		
		$tpl->addOnLoadCode($onLoadCode);
		
		// return shared code
		return $this->getSharedHtml();
	}
	
	/**
	 * Gets the code that is shared by all upload instances.
	 * 
	 * @return string The shared code by all upload instances.
	 */
	protected function getSharedHtml()
	{
		global $lng;
		
		// already loaded?
		if (self::$shared_code_loaded)
			return "";

		// make sure required scripts are loaded
		self::initFileUpload();
		
		// load script template
		$tpl_shared = new ilTemplate("tpl.fileupload_shared.html", true, true, "Services/FileUpload");
		
		// initialize localized texts
		$lng->loadLanguageModule("form");
		$tpl_shared->setCurrentBlock("fileupload_texts");
		$tpl_shared->setVariable("ERROR_MSG_FILE_TOO_LARGE", $lng->txt("form_msg_file_size_exceeds"));
		$tpl_shared->setVariable("ERROR_MSG_WRONG_FILE_TYPE", $lng->txt("form_msg_file_wrong_file_type"));
		$tpl_shared->setVariable("ERROR_MSG_EMPTY_FILE_OR_FOLDER", $lng->txt("error_empty_file_or_folder"));
		$tpl_shared->setVariable("ERROR_MSG_UPLOAD_ZERO_BYTES", $lng->txt("error_upload_was_zero_bytes"));
		$tpl_shared->setVariable("ERROR_MSG_TOO_MANY_FILES", $lng->txt("form_msg_file_too_many_files"));
		$tpl_shared->setVariable("QUESTION_CANCEL_ALL", $lng->txt("cancel_file_upload"));
		$tpl_shared->setVariable("ERROR_MSG_EXTRACT_FAILED", $lng->txt("error_extraction_failed"));
		$tpl_shared->setVariable("PROGRESS_UPLOADING", $lng->txt("uploading"));
		$tpl_shared->setVariable("PROGRESS_EXTRACTING", $lng->txt("extracting"));
		$tpl_shared->setVariable("DROP_FILES_HERE", $lng->txt("drop_files_on_repo_obj_info"));
		$tpl_shared->parseCurrentBlock();
			
		// initialize default values
		$tpl_shared->setCurrentBlock("fileupload_defaults");
		$tpl_shared->setVariable("CONCURRENT_UPLOADS", ilFileUploadSettings::getConcurrentUploads());
		$tpl_shared->setVariable("MAX_FILE_SIZE", ilFileUploadUtil::getMaxFileSize());
		$tpl_shared->setVariable("ALLOWED_SUFFIXES", "");
		$tpl_shared->setVariable("SUPPORTED_ARCHIVES", "\"zip\"");
		$tpl_shared->parseCurrentBlock();
			
		// load panel template
		$tpl_panel = new ilTemplate("tpl.fileupload_panel_template.html", true, true, "Services/FileUpload");
		$tpl_panel->setVariable("TXT_HEADER", $lng->txt("upload_files_title"));
		$tpl_panel->setVariable("TXT_SHOW_ALL_DETAILS", $lng->txt('show_all_details')); 
		$tpl_panel->setVariable("TXT_HIDE_ALL_DETAILS", $lng->txt('hide_all_details'));
		$tpl_panel->setVariable("SUBMIT_BUTTON", $this->submit_button_name);
		$tpl_panel->setVariable("CANCEL_BUTTON", $this->cancel_button_name);
		$tpl_panel->setVariable("TXT_SUBMIT_BUTTON", $lng->txt("upload_files"));
		$tpl_panel->setVariable("TXT_CANCEL_BUTTON", $lng->txt("cancel"));

		$tpl_shared->setCurrentBlock("fileupload_panel_tmpl");
		$tpl_shared->setVariable("PANEL_TEMPLATE_HTML", $tpl_panel->get());
		$tpl_shared->parseCurrentBlock();
			
		// load row template
		$tpl_row = new ilTemplate("tpl.fileupload_row_template.html", true, true, "Services/FileUpload");
		$tpl_row->setVariable("IMG_ALERT", ilUtil::getImagePath("icon_alert_s.gif"));
		$tpl_row->setVariable("ALT_ALERT", $lng->txt("alert"));
		$tpl_row->setVariable("TXT_CANCEL", $lng->txt("cancel"));
		$tpl_row->setVariable("TXT_REMOVE", $lng->txt("remove"));
		$tpl_row->setVariable("TXT_TITLE", $lng->txt("title"));
		$tpl_row->setVariable("TXT_DESCRIPTION", $lng->txt("description"));
		$tpl_row->setVariable("TXT_EXTRACT", $lng->txt("unzip"));
		$tpl_row->setVariable("TXT_KEEP_STRUCTURE", $lng->txt("take_over_structure"));
		$tpl_row->setVariable("TXT_KEEP_STRUCTURE_INFO", $lng->txt("take_over_structure_info"));
		$tpl_row->setVariable("TXT_PENDING", $lng->txt("upload_pending"));
			
		$tpl_shared->setCurrentBlock("fileupload_row_tmpl");
		$tpl_shared->setVariable("ROW_TEMPLATE_HTML", $tpl_row->get());
		$tpl_shared->parseCurrentBlock();
			
		// shared code now loaded
		self::$shared_code_loaded = true;
		
		// create HTML
		return $tpl_shared->get();
	}
	
	/**
	 * Enable the form submit mode
	 *
	 * @param string $input_field_id
	 * @param string $a_submit_name
	 * @param string $a_cancel_name
	 */
	public function enableFormSubmit($input_field_id, $a_submit_name, $a_cancel_name)
	{
		$this->use_form = true;
		$this->input_field_id = $input_field_id;
		$this->submit_button_name = $a_submit_name;
		$this->cancel_button_name = $a_cancel_name;
	}
	
	/**
	 * Sets the maximum file size in bytes.
	 *
	 * @param	int	$max_size	The maximum file size in bytes.
	 */
	public function setMaxFileSize($max_size)
	{
		$this->max_file_size = $max_size;
	}
	
	/**
	 * @return int the maximum file size in bytes.
	 */
	public function getMaxFileSize()
	{
		return $this->max_file_size;
	}	
	
	/**
	 * Sets the maximum number of files.
	 *
	 * @param	int	$a_max_number_of_files.
	 */
	public function setMaxNumberOfFiles($a_max_number_of_files)
	{
		$this->max_number_of_files = $a_max_number_of_files;
	}

	/**
	 * Gets the maximum number of files.
	 */
	public function getMaxNumberOfFiles()
	{
		return $this->max_number_of_files;
	}

	/**
	 * Set accepted archive suffixes.
	 *
	 * @param	array	$a_suffixes	Accepted archive suffixes.
	 */
	public function setArchiveSuffixes($a_suffixes)
	{
		$this->archive_suffixes = $a_suffixes;
	}

	/**
	 * Get accepted archive suffixes.
	 *
	 * @return	array	Accepted archive suffixes.
	 */
	public function getArchiveSuffixes()
	{
		return $this->archive_suffixes;
	}
	
	/**
	 * Set accepted suffixes.
	 *
	 * @param	array	$a_suffixes	Accepted suffixes.
	 */
	public function setSuffixes($a_suffixes)
	{
		$this->suffixes = $a_suffixes;
	}

	/**
	 * Get accepted suffixes.
	 *
	 * @return	array	Accepted suffixes.
	 */
	public function getSuffixes()
	{
		return $this->suffixes;
	}
	
	/**
	 * Sets the name of the input field the files are submitted with.
	 *
	 * @param	string	$a_name	The name of the input field.
	 */
	public function setInputFieldName($a_name)
	{
		$this->input_field_name = $a_name;
	}

	/**
	 * Gets the name of the input field the files are submitted with.
	 *
	 * @return	string	The name of the input field.
	 */
	public function getInputFieldName()
	{
		return $this->input_field_name;
	}

	public function setDropAreaId($a_id)
	{
		$this->drop_area_id = $a_id;
	}

	public function getDropAreaId()
	{
		return $this->drop_area_id;
	}

	public function setFileListId($a_id)
	{
		$this->file_list_id = $a_id;
	}

	public function getFileListId()
	{
		return $this->file_list_id;
	}
	
	/**
	 * @param bool $get_file_list
	 */
	public function setGetFileList($get_file_list)
	{
		$this->get_file_list = (bool)$get_file_list;
	}

	/**
	 * @return bool
	 */
	public function hasGetFileList()
	{
		return $this->get_file_list;
	}

	/**
	 * @param bool $delete_uploaded_files_allowed
	 */
	public function setDeleteUploadedFilesAllowed($delete_uploaded_files_allowed)
	{
		$this->delete_uploaded_files_allowed = (bool)$delete_uploaded_files_allowed;
	}

	/**
	 * @return bool
	 */
	public function hasDeleteUploadedFilesAllowed()
	{
		return $this->delete_uploaded_files_allowed;
	}

	public function setFileSelectButtonId($a_id)
	{
		$this->file_select_button_id = $a_id;
	}

	public function getFileSelectButtonId()
	{
		return $this->file_select_button_id;
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
	public function setSubmitEntireForm($form_submit_mode)
	{
		switch ($form_submit_mode)
		{
			case ilFileUploadSettings::SUBMIT_ENTIRE_FORM_NEVER:
			case ilFileUploadSettings::SUBMIT_ENTIRE_FORM_ALWAYS:
			case ilFileUploadSettings::SUBMIT_ENTIRE_FORM_AFTER_FILE_UPLOADS:
				$this->submit_entire_form = $form_submit_mode;
				break;
		}
	}

	/**
	 * @return string
	 */
	public function getSubmitEntireForm() {
		return $this->submit_entire_form;
	}

	private function getUploadUrl()
	{
		/** @var ilCtrl $ilCtrl */
		global $ilCtrl;
		
		// return null when the form is used
		if ($this->use_form)
			return null;
		
		// check if supported
		if (!ilFileUploadUtil::isUploadSupported())
			return false;
			
		// build upload URL
		include_once("Modules/File/classes/class.ilObjFileGUI.php");
		$ilCtrl->setParameterByClass(self::FILE_OBJ_GUI_CLASS, "ref_id", $this->ref_id);
		$ilCtrl->setParameterByClass(self::FILE_OBJ_GUI_CLASS, "new_type", "file");
	
		return $ilCtrl->getFormActionByClass(self::FILE_OBJ_GUI_CLASS, "uploadFiles", "", true, false);
	}

	/**
	 * @param string $a_id
	 *
	 * @return string
	 */
	private function makeJqueryId($a_id)
	{
		if ($a_id != null && count($a_id) > 0)
		{
			if ($a_id[0] != "#")
				return "#" . $a_id;
		}
		
		return $a_id;
	}
}
?>