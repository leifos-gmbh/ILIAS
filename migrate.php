<?php

include_once './include/inc.header.php';

function migrateAccounts()
{
	global $DIC;

	$db = $DIC->database();

	$query = 'select usr_id,login from usr_data  where auth_mode = ' . $db->quote('ldap_1', 'text');
	$res = $db->query($query);
	while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
		$new_hash = '';
		while ($new_hash = substr(md5(microtime(true)), 0, 5)) {
			if (!ilObjUser::_loginExists($new_hash)) {
				break;
			}
		}

		$update_query = 'update usr_data set login = ' . $db->quote($new_hash, 'text') . ' where usr_id = ' . $db->quote($row->usr_id, 'integer');
		$db->manipulate($update_query);

		echo $update_query.'<br />';
	}
}

#migrateAccounts();

echo '<h1>Done</h1>';

?>


