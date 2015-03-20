<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2014 ILIAS open source, University of Cologne            |
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

require_once('Services/Form/interfaces/interface.ilFileUploadHandler.php');
/**
 * Class ilFileUploadHandler
 *
 * @author  Fabio Heer
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version $Id$
 */
class ilFileUploadHelper {

	const TABLE_NAME = 'il_fileupload_cache';

	/**
	 * @var ilFileUploadInputGUI
	 */
	protected $file_upload_item;
	/**
	 * @var ilFileUploadHandler
	 */
	protected $callback;


	/**
	 * @param ilFileUploadInputGUI $item        the file upload item that shall be handled
	 * @param ilFileUploadHandler  $callback    a controller that handles the file uploads. It is called by ilFileUploadHelper  
	 */
	public function __construct(ilFileUploadInputGUI $item, ilFileUploadHandler $callback) {
		$this->file_upload_item = $item;
		$this->callback = $callback;
	}


	/**
	 * @return array ( array(name, type, tmp_name, error, size, extract, title, description, keep_structure) )
	 */
	public function getFileArray() {
		/** @var ilDB $ilDB */
		global $ilDB;

		$files = array();
		$stmt = $ilDB->prepare('SELECT ' .
			'name, type, tmp_name, error, file_size AS size, extract, title, description, keep_structure  FROM ' .
			self::TABLE_NAME . ' WHERE upload_id = ?', array('text'));
		$set = $ilDB->execute($stmt, array($this->file_upload_item->getPostVar() . '_' . $_POST['ilfilehash']));
		while ($rec = $ilDB->fetchAssoc($set)) {
			$rec['description'] = (string)$rec['description'];
			$files[] = $rec;
		}

		return $files;
	}


	/**
	 * Removes the temporary file upload data from the DB.
	 */
	public function clearPersistedUploads() {
		/** @var ilDB $ilDB */
		global $ilDB;
		$stmt = $ilDB->prepare('DELETE FROM ' . self::TABLE_NAME . ' WHERE upload_id = ?;', array('text'));
		$ilDB->execute($stmt, array($this->file_upload_item->getPostVar() . '_' . $_POST['ilfilehash']));
	}

	
	/**
	 * Call this method to handle the asynchronous file upload requests.
	 * This method uses the callback to pass on the uploaded file data.
	 */
	public function handleFileUploadRequest() {
		// Only handle async request
		if (isset($_REQUEST['is_async_file_upload'])) {
			// is this a file upload?
			if (isset($_FILES[$this->file_upload_item->getPostVar()])) {
				$this->uploadFile();
				exit;
			}

			// load the list of uploaded files?
			if (isset($_GET['getExistingFiles']) AND $_GET['getExistingFiles'] == 1) {
				$this->listUploadedFiles();
				exit;
			}

			// delete a file?
			if (isset($_POST['deleteId'])) {
				$this->deleteUploadedFile();
				exit;
			}
		}
	}


	/**
	 * Send a response to list the uploaded files.
	 */
	protected function listUploadedFiles() {
		$response = new stdClass();
		$response->files = array();

		// pass to user method
		$user_response = $this->callback->onListUploadedFiles($this->file_upload_item, $response);
		/** @var stdClass $response */
		$response = (object)array_merge((array)$response, (array)$user_response);

		$this->echoResponse($response);
	}


	/**
	 * Request deletion of a specific file
	 */
	protected function deleteUploadedFile() {
		$this->callback->onDeleteUploadedFile($this->file_upload_item, $_POST['deleteId']);
	}


	/**
	 * Handle a file upload
	 */
	protected function uploadFile() {
		// read input data
		require_once('Services/JSON/classes/class.ilJsonUtil.php');
		$file_data = ilJsonUtil::decode(stripslashes ($_POST[$this->file_upload_item->getPostVar()]));
		$title = isset($file_data->title) ? $file_data->title : '';
		$description = isset($file_data->description) ? $file_data->description : '';
		$extract = isset($file_data->extract) ? (bool)$file_data->extract : FALSE;
		$structure = isset($file_data->keep_structure) ? (bool)$file_data->keep_structure : TRUE;

		// validate file upload
		$is_valid = $this->file_upload_item->checkFileInput();

		// prepare file array
		$file = $_FILES[$this->file_upload_item->getPostVar()];
		$file['extract'] = $extract;
		$file['title'] = $title;
		$file['description'] = $description;
		$file['keep_structure'] = $structure;
		require_once('./Services/Utilities/classes/class.ilMimeTypeUtil.php');
		$file['type'] = ilMimeTypeUtil::getMimeType($file['tmp_name'], $file['name'], $file['type']);
		$tmp_name = $file['tmp_name'];

		// prepare the response
		$response = new stdClass();
		$response->error = NULL;
		$response->debug = NULL;
		if ($this->file_upload_item->getAlert()) {
			$response->error = $this->file_upload_item->getAlert();
		}
		$response->fileName = $file['name'];
		$response->fileSize = intval($file['size']);
		$response->type = $file['type'];
		$response->fileUnzipped = $file['extract'];

		// pass to user method
		$user_response = $this->callback->onFileUpload($this->file_upload_item, $response, $is_valid, $file);
		/** @var stdClass $response */
		$response = (object)array_merge((array)$response, (array)$user_response);

		// make sure the file is persisted
		if ($this->file_upload_item->isPersistFileEnabled()) {
			$this->persistFile($tmp_name, $file, $response);
		}

		// send response
		$this->echoResponse($response);
	}


	/**
	 * @param string    $tmp_name
	 * @param array     $file
	 * @param stdClass  $response
	 */
	protected function persistFile($tmp_name, $file, stdClass &$response) {
		try {
			$temp_destination_path = ilUtil::ilTempnam();
			if (is_file($tmp_name) AND ilUtil::moveUploadedFile($tmp_name, $file['name'], $temp_destination_path)) {
				$file['tmp_name'] = $temp_destination_path;
			}

			// persist data in session
			/** @var ilDB $ilDB */
			global $ilDB;
			$stmt = $ilDB->prepare('INSERT INTO ' . self::TABLE_NAME .
				' (id, upload_id, name, type, tmp_name, error, file_size, extract, title, description, keep_structure) ' .
				' VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);',
				array('integer', 'text', 'text', 'text', 'text', 'integer', 'integer', 'integer', 'text', 'text', 'integer'));

			$ilDB->execute($stmt, array(
				$ilDB->nextId(self::TABLE_NAME),
				$this->file_upload_item->getPostVar() . '_' . $_POST['ilfilehash'],
				$file['name'],
				$file['type'],
				$file['tmp_name'],
				$file['error'],
				$file['size'],
				$file['extract'],
				$file['title'],
				$file['description'],
				$file['keep_structure']

			));
		} catch (Exception $ex) {
			$response->error = $ex->getMessage() . ' ## ' . $ex->getTraceAsString();
		}
	}


	/**
	 * @param stdClass $response
	 */
	protected function echoResponse(stdClass $response = NULL) {
		// send response object (don't use 'application/json' as IE wants to download it!)
		require_once('Services/JSON/classes/class.ilJsonUtil.php');
		header('Vary: Accept');
		header('Content-type: text/plain');
		echo ilJsonUtil::encode($response);
	}
}