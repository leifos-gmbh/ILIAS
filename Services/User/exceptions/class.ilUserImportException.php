<?php

include_once './Services/User/exceptions/class.ilUserException.php';

/**
 * Class ilUsersGalleryUsers
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilUserImportException extends ilUserException
{
	const ERR_MULTIPLE_IMPORT_IDS = 100;
}
?>