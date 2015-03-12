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

/**
 * Interface ilFileUploadHandler
 * 
 * @author  Fabio Heer
 * @version $Id$
 * @ingroup ServicesForm
 */
interface ilFileUploadHandler {

	/**
	 * @param ilFileUploadInputGUI $item
	 * @param stdClass             $response
	 * @param bool                 $is_valid
	 * @param array                $file
	 *
	 * @return stdClass $response
	 */
	public function onFileUpload(ilFileUploadInputGUI $item, $response, $is_valid, $file);


	/**
	 * @param ilFileUploadInputGUI $item
	 * @param stdClass             $response already contains an array "files" append all files by setting the following
	 *                                       properties: "id", "name", "title", "description" and "size".
	 *
	 * @return stdClass $response
	 */
	public function onListUploadedFiles(ilFileUploadInputGUI $item, $response);


	/**
	 * @param ilFileUploadInputGUI $item
	 * @param string               $file_id this ID matches an ID set in onListUploadedFiles
	 *
	 * @return stdClass $response
	 */
	public function onDeleteUploadedFile(ilFileUploadInputGUI $item, $file_id);
}